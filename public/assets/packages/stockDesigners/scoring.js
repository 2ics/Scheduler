
scoringController = function(options) {
  this.init(options);
};

scoringController.prototype = {

  init: function(options) {
    this.el = {
      widthCol: $("#scoring_width_"+options.id),
      heightCol: $("#scoring_height_"+options.id),
      btn_direction: $("#scoring_preset_buttons_"+options.id+" input:radio[name='score_direction']"),
      btn_fold: $("#scoring_preset_buttons_"+options.id+" input:checkbox[name='fold_scores']"),
      addWidthBtn: $("#scoring_width_"+options.id+"_button"),
      addHeightBtn: $("#scoring_height_"+options.id+"_button"),
      manual_buttons: $("#scoring_manual_buttons_"+options.id),
      preset_buttons: $("#scoring_preset_buttons_"+options.id),
      canvas: $("#scoring_canvas_"+options.id)[0]
    }

    this.width = parseFloat(options.width);
    this.height = parseFloat(options.height);
    this.flat_width = parseFloat(options.flat_width).toFixed(2);
    this.flat_height = parseFloat(options.flat_height).toFixed(2);
    this.scaleX = Math.max(this.width-20, this.flat_width)/Math.min(this.width-20, this.flat_width);
    this.scaleY = Math.max(this.height-21, this.flat_height)/Math.min(this.height-21, this.flat_height);
    this.id = options.id;
    this.projectId = options.projectId;
    this.directions = {width: {}, height: {}};

    this.width_index = 0;
    this.height_index = 0;
    this.num_widths = 0;
    this.num_heights = 0;

    this.score_type = null;
    this.score_direction = null;

    $("#scoring_preset_buttons_"+this.id+" .score-select").click($.proxy(function(event){
      $("#scoring_preset_buttons_"+this.id+" .score-select.active").removeClass("active");
      if ($(event.currentTarget).data('foldtype') == this.score_type){
        this.width_index = 0;
        this.height_index = 0;
        this.num_widths = 0;
        this.num_heights = 0;
        this.directions.width = {};
        this.directions.height = {};
        this.score_type = "";
        this.score_direction = "";
        this.el.btn_direction.each(function(index, element){
          $(element).parent('label').removeClass('active');
        });
        this.el.btn_direction.filter(':checked').prop('checked', false);
        this.draw();
      }else{
        $(event.currentTarget).addClass("active");
        this.score_type = $(event.currentTarget).data('foldtype');
      }
      
      if (this.el.btn_direction.filter(':checked').val() == undefined && this.score_type != ""){
        this.score_direction = "width";
        this.el.btn_direction.filter('[value="width"]').prop('checked', true);
        this.el.btn_direction.filter('[value="width"]').parent('label').addClass('active');
      }

      this.setPreset();
      getScoringRunningCost();
    },this));

    $("#scoring_preset_buttons_"+this.id+" .score-select").hover(
      function(){
        $(this).addClass("score-hover");
      },
      function(){
        $(this).removeClass("score-hover");
      }
    );

    this.el.btn_direction.change($.proxy(function(event){
      this.score_direction = $(event.target).val();
      this.setPreset();
    }, this));

    this.el.addWidthBtn.click($.proxy(function(){
      this.addWidth(this.flat_width/2);
    }, this));

    this.el.addHeightBtn.click($.proxy(function(){
      this.addHeight(this.flat_height/2);
    }, this));

      this.el.btn_fold.click($.proxy(function(){
          console.log("TEST");
          if (this.el.btn_fold.is(':checked')){
              if (stepController.is_step_before("projectFolding", "projectScoring")){
                  stepController.remove_step("projectFolding");
                  stepController.add_step_after("Folding", "projectFolding", false, null, null, true, false, {'projectInformation': "true", 'projectScoring': "true", "projectSettings": "true"}, "projectScoring");
                  stepController.sync_steps();
              }else{
                  stepController.add_prerequisite("projectFolding", "projectScoring");
                  stepController.sync_steps();
              }

              if (stepController.find_step("projectFolding") == false){
                  stepController.add_step_after("Folding", "projectFolding", false, null, null, true, false, {'projectInformation': "true", 'projectScoring': "true", "projectSettings": "true"}, "projectScoring");
                  stepController.sync_steps();
              }
          }else{
              stepController.remove_prerequisite("projectFolding", "projectScoring");
          }
      }, this));

    this.run();
  },

  //---------------------------------------------------------------------------

  run: function() {
    this.restoreData();
  },

  restoreData: function() {
    $.ajax({
      url: root+'/api/project/scoringData/'+this.projectId+'/'+this.id,
      type: 'GET',
      dataType: 'json',
      success: $.proxy(function(data) {
        this.score_type = data.folding_type;
        $.each(data, $.proxy(function(index, element){
          if (index == "score_type"){
            if (element == null){
              return;
            }
            this.score_type = element.field_value;
            $("#scoring_preset_buttons_"+this.id+" .score-select[data-foldtype='"+this.score_type+"']").addClass("active");
            return;
          }
          if (index == "score_direction"){
            if (element == null){
              return;
            }
            this.score_direction = element.field_value;
            this.el.btn_direction.filter('[value='+this.score_direction+']').prop('checked', 'checked');
            $("#scoring_preset_buttons_"+this.id+" .btn-score-direction.active").removeClass("active");
            this.el.btn_direction.filter('[value='+this.score_direction+']').parent('label').addClass('active');
            return;
          }
          if (index == "fold_scores"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.btn_fold.prop('checked', true);
            }
            return;
          }
        }, this));
        $.each(data, $.proxy(function(index, element){
          if (index == "score_type"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.manual_buttons.hide();
            }
            return;
          }
          if (index == "score_direction"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.manual_buttons.hide();
            }
            return;
          }
          if (index == "fold_scores"){
            return;
          }
          $.each(JSON.parse(element), $.proxy(function(scoreIndex, data){
            if (index == "width"){
              if (this.score_type == "false"){
                this.addWidth(data.field_value);
              }else{
                this.addWidth_noRemove(data.field_value);
              }
            }
            if (index == "height"){
              if (this.score_type == "false"){
                this.addHeight(data.field_value);
              }else{
                this.addHeight_noRemove(data.field_value);
              }
            }
          }, this));
        }, this));
        getScoringRunningCost();
        this.draw();
      }, this)
    });

    if (project_sub_type != "none") {
      if (presets[(project_type).split("-")[1]][project_sub_type]['steps'] != undefined && presets[(project_type).split("-")[1]][project_sub_type]['steps']['Scoring'] != undefined) {
        this.el.preset_buttons.parent('div').prepend("<div class='col-md-12 text-info text-center'>These scores are inherited from the preset selected</div>");
        this.el.manual_buttons.hide();
        this.el.preset_buttons.hide();
        this.el.btn_fold.hide();
      }
    }
  },

  setPreset: function() {
    if (this.score_type == ""){
      this.el.manual_buttons.show();
      return;
    }
    this.width_index = 0;
    this.height_index = 0;
    this.num_widths = 0;
    this.num_heights = 0;
    this.directions.width = {};
    this.directions.height = {};
    $(".score_direction_remove").each(function(){
      $(this).trigger("click");
    });
    this.el.manual_buttons.hide();
    if (this.score_direction == "height"){
      if (this.score_type == "2 Panel"){
        this.addHeight_noRemove(this.flat_height/2);
      }else if (this.score_type == "3 Panel"){
        this.addHeight_noRemove(this.flat_height/3);
        this.addHeight_noRemove(this.flat_height/3*2);
      }else if (this.score_type == "4 Panel"){
        this.addHeight_noRemove(this.flat_height/4);
        this.addHeight_noRemove(this.flat_height/4*2);
        this.addHeight_noRemove(this.flat_height/4*3);
      }
    }else{
      if (this.score_type == "2 Panel"){
        this.addWidth_noRemove(this.flat_width/2);
      }else if (this.score_type == "3 Panel"){
        this.addWidth_noRemove(this.flat_width/3);
        this.addWidth_noRemove(this.flat_width/3*2);
      }else if (this.score_type == "4 Panel"){
        this.addWidth_noRemove(this.flat_width/4);
        this.addWidth_noRemove(this.flat_width/4*2);
        this.addWidth_noRemove(this.flat_width/4*3);
      }
    }
    this.draw();
  },

  draw: function() {
    paper.setup(this.el.canvas);
    paper.view.viewSize = new paper.Size(this.width, this.height);
    var path = new paper.Path.Rectangle({
        point: [0, 20],
        size: [this.width-20, this.height-20],
        strokeColor: 'black',
        strokeWidth: 1,
        fillColor: "#FFFFFF"
    }); 
    var text = new paper.PointText(new paper.Point(this.width-15, (this.height/2) + 15));
    text.justification = 'center';
    text.fillColor = 'black';
    text.rotation = 90;
    text.content = this.flat_height;
    var text = new paper.PointText(new paper.Point(this.width / 2, 15));
    text.justification = 'center';
    text.fillColor = 'black';
    text.content = this.flat_width;
    $.each(this.directions.width, $.proxy(function(index, element){
      if (element != null){
        var path = new paper.Path.Line({
          from: [(element*this.scaleX), 20],
          to: [(element*this.scaleX), this.height - 1],
          strokeColor: 'black',
          strokeWidth: 1
          // dashArray: [5, 4]
        });
      }
    }, this));
    $.each(this.directions.height, $.proxy(function(index, element){
      if (element != null){
        var path = new paper.Path.Line({
          from: [1, (element*this.scaleY) + 20],
          to: [this.width - 21, (element*this.scaleY) + 20],
          strokeColor: 'black',
          strokeWidth: 1
          // dashArray: [5, 4]
        });
      }
    }, this));
    paper.view.draw();
  },

  addWidth_noRemove: function(value) {
    this.directions.width[this.width_index] = value;
    this.width_index++;
    this.num_widths++;
    getScoringRunningCost();
    this.draw();
  },

  addHeight_noRemove: function(value) {
    this.directions.height[this.height_index] = value;
    this.height_index++;
    this.num_heights++;
    getScoringRunningCost();
    this.draw();
  },

  addWidth: function(value) {
    this.el.widthCol.append("<div class='input-group scoring_side scoring_side_width_"+this.id+"' data-widthid='"+this.width_index+"' data-stockid='"+this.id+"' style='margin-top: 10px;'><input type='text' class='form-control' value='"+value+"'> <span class='input-group-addon score_direction_remove' id='score_width_"+this.id+"_"+this.width_index+"_remove'><span class='glyphicon glyphicon-remove'></span></span> </div>");
    this.directions.width[this.width_index] = value;
    $("#score_width_"+this.id+"_"+this.width_index+"_remove").click($.proxy(function(event){
      $($($(event)[0].currentTarget).parent('div')).remove();
      this.directions.width[$($(event)[0].currentTarget).parent('div').data('widthid')] = null;
      this.num_widths--;
      getScoringRunningCost();
      this.draw();
    }, this));
    $(".scoring_side_width_"+this.id).keyup($.proxy(function(event){
      value = $($($(event)[0].currentTarget)[0]).children('input').val();
      if (parseFloat(value) < 0 || parseFloat(value) > this.flat_width || !$.isNumeric(value)){
        value = null;
        $($($(event)[0].currentTarget)[0]).addClass("has-error");
      }else{
        $($($(event)[0].currentTarget)[0]).removeClass("has-error");
      }
      this.directions.width[$($($(event)[0].currentTarget)[0]).data('widthid')] = value;
      this.draw();
    }, this));
    this.width_index++;
    this.num_widths++;
    getScoringRunningCost();
    this.draw();
  },

  addHeight: function(value) {
    this.el.heightCol.append("<div class='input-group scoring_side scoring_side_height_"+this.id+"' data-heightid='"+this.height_index+"' data-stockid='"+this.id+"' style='margin-top: 10px;'><input type='text' class='form-control' value='"+value+"'> <span class='input-group-addon score_direction_remove' id='score_height_"+this.id+"_"+this.height_index+"_remove'><span class='glyphicon glyphicon-remove'></span></span> </div>");
    this.directions.height[this.height_index] = value;
    $("#score_height_"+this.id+"_"+this.height_index+"_remove").click($.proxy(function(event){
      $($($(event)[0].currentTarget).parent('div')).remove();
      this.directions.height[$($(event)[0].currentTarget).parent('div').data('heightid')] = null;
      this.num_heights--;
      getScoringRunningCost();
      this.draw();
    }, this));
    $(".scoring_side_height_"+this.id).keyup($.proxy(function(event){
      var value = $($($(event)[0].currentTarget)[0]).children('input').val();
      if (parseFloat(value) < 0 || parseFloat(value) > this.flat_height || !$.isNumeric(value)){
        value = null;
        $($($(event)[0].currentTarget)[0]).addClass("has-error");
      }else{
        $($($(event)[0].currentTarget)[0]).removeClass("has-error");
      }
      this.directions.height[$($($(event)[0].currentTarget)[0]).data('heightid')] = value;
      this.draw();
    }, this));
    this.height_index++;
    this.num_heights++;
    getScoringRunningCost();
    this.draw();
  },

  getData: function() {
    var scores = {};
    if (this.score_type == "" || this.score_type == null){
      this.score_type = "false";
    }
    if (this.score_direction == "" || this.score_direction == null){
      this.score_direction = "false";
    }
    scores['directions'] = this.directions;
    scores['score_direction'] = this.score_direction;
    scores['score_type'] = this.score_type;
    scores['fold_scores'] = this.el.btn_fold.is(':checked');
    return scores;
  }

} 