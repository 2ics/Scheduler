
foldingController = function(options) {
  this.init(options);
};

foldingController.prototype = {

  init: function(options) {
    this.el = {
      widthCol: $("#folding_width_"+options.id),
      heightCol: $("#folding_height_"+options.id),
      btn_direction: $("#folding_preset_buttons_"+options.id+" input:radio[name='fold_direction']"),
      addWidthBtn: $("#folding_width_"+options.id+"_button"),
      addHeightBtn: $("#folding_height_"+options.id+"_button"),
      manual_buttons: $("#folding_manual_buttons_"+options.id),
      preset_buttons: $("#folding_preset_buttons_"+options.id),
      canvas: $("#folding_canvas_"+options.id)[0]
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

    this.fold_type = null;
    this.fold_direction = null;

    $("#folding_preset_buttons_"+this.id+" .fold-select").click($.proxy(function(event){
      $("#folding_preset_buttons_"+this.id+" .fold-select.active").removeClass("active");
      if ($(event.currentTarget).data('foldtype') == this.fold_type){
        this.width_index = 0;
        this.height_index = 0;
        this.num_widths = 0;
        this.num_heights = 0;
        this.directions.width = {};
        this.directions.height = {};
        this.fold_type = "";
        this.fold_direction = "";
        this.el.btn_direction.each(function(index, element){
          $(element).parent('label').removeClass('active');
        });
        this.el.btn_direction.filter(':checked').prop('checked', false);
        this.draw();
      }else{
        $(event.currentTarget).addClass("active");
        this.fold_type = $(event.currentTarget).data('foldtype');
      }
      
      if (this.el.btn_direction.filter(':checked').val() == undefined && this.fold_type != ""){
        this.fold_direction = "width";
        this.el.btn_direction.filter('[value="width"]').prop('checked', true);
        this.el.btn_direction.filter('[value="width"]').parent('label').addClass('active');
      }
      
      this.setPreset();
      getFoldingRunningCost();
    },this));

    $("#folding_preset_buttons_"+this.id+" .fold-select").hover(
      function(){
        $(this).addClass("fold-hover");
      },
      function(){
        $(this).removeClass("fold-hover");
      }
    );

    this.el.btn_direction.change($.proxy(function(event){
      this.fold_direction = $(event.target).val();
      this.setPreset();
    }, this));

    this.el.addWidthBtn.click($.proxy(function(){
      this.addWidth(this.flat_width/2);
    }, this));

    this.el.addHeightBtn.click($.proxy(function(){
      this.addHeight(this.flat_height/2);
    }, this));

    this.run();
  },

  //---------------------------------------------------------------------------

  run: function() {
    this.restoreData();
  },

  restoreData: function() {
    $.ajax({
      url: root+'/api/project/foldingData/'+this.projectId+'/'+this.id,
      type: 'GET',
      dataType: 'json',
      success: $.proxy(function(data) {
        this.fold_type = data.folding_type;
        $.each(data, $.proxy(function(index, element){
          if (index == "fold_type"){
            if (element == null){
              return;
            }
            this.fold_type = element.field_value;
            $("#folding_preset_buttons_"+this.id+" .fold-select[data-foldtype='"+this.fold_type+"']").addClass("active");
            return;
          }
          if (index == "fold_direction"){
            if (element == null){
              return;
            }
            this.fold_direction = element.field_value;
            this.el.btn_direction.filter('[value='+this.fold_direction+']').prop('checked', 'checked');
            $("#folding_preset_buttons_"+this.id+" .btn-fold-direction.active").removeClass("active");
            this.el.btn_direction.filter('[value='+this.fold_direction+']').parent('label').addClass('active');
            return;
          }
          if (index == "fold_scores"){
            if (element == null){
              return;
            }
            return;
          }
        }, this));
        $.each(data, $.proxy(function(index, element){
          if (index == "fold_type"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.manual_buttons.hide();
            }
            return;
          }
          if (index == "fold_direction"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.manual_buttons.hide();
            }
            return;
          }
          if (index == "fold_scores"){
            if (element == null){
              return;
            }
            if (element.field_value != "false"){
              this.el.manual_buttons.hide();
              this.el.preset_buttons.prepend("<div class='col-md-12 text-info text-center'>These folds are inherited from scoring</div>");
              $("#folding_preset_buttons_"+this.id+" .fold-select").unbind('mouseenter mouseleave click');
              this.el.btn_direction.parent('label').parent('div').hide();
            }
            return;
          }
          $.each(JSON.parse(element), $.proxy(function(foldIndex, data){
            if (index == "width"){
              if (this.fold_type == "false"){
                this.addWidth(data.field_value);
              }else{
                this.addWidth_noRemove(data.field_value);
              }
            }
            if (index == "height"){
              if (this.fold_type == "false"){
                this.addHeight(data.field_value);
              }else{
                this.addHeight_noRemove(data.field_value);
              }
            }
          }, this));
        }, this));
        getFoldingRunningCost();
        this.draw();
      }, this)
    });
    if (project_sub_type != "none") {
      if (presets[(project_type).split("-")[1]][project_sub_type]['steps'] != undefined && presets[(project_type).split("-")[1]][project_sub_type]['steps']['Folding'] != undefined) {
        this.el.preset_buttons.parent('div').prepend("<div class='col-md-12 text-info text-center'>These folds are inherited from the preset selected</div>");
        this.el.manual_buttons.hide();
        this.el.preset_buttons.hide();
      }
    }
  },

  setPreset: function() {
    if (this.fold_type == ""){
      this.el.manual_buttons.show();
      return;
    }
    this.width_index = 0;
    this.height_index = 0;
    this.num_widths = 0;
    this.num_heights = 0;
    this.directions.width = {};
    this.directions.height = {};
    $(".fold_direction_remove").each(function(){
      $(this).trigger("click");
    });
    this.el.manual_buttons.hide();
    if (this.fold_direction == "height"){
      if (this.fold_type == "2 Panel"){
        this.addHeight_noRemove(this.flat_height/2);
      }else if (this.fold_type == "3 Panel"){
        this.addHeight_noRemove(this.flat_height/3);
        this.addHeight_noRemove(this.flat_height/3*2);
      }else if (this.fold_type == "4 Panel"){
        this.addHeight_noRemove(this.flat_height/4);
        this.addHeight_noRemove(this.flat_height/4*2);
        this.addHeight_noRemove(this.flat_height/4*3);
      }
    }else{
      if (this.fold_type == "2 Panel"){
        this.addWidth_noRemove(this.flat_width/2);
      }else if (this.fold_type == "3 Panel"){
        this.addWidth_noRemove(this.flat_width/3);
        this.addWidth_noRemove(this.flat_width/3*2);
      }else if (this.fold_type == "4 Panel"){
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
    getFoldingRunningCost();
    this.draw();
  },

  addHeight_noRemove: function(value) {
    this.directions.height[this.height_index] = value;
    this.height_index++;
    this.num_heights++;
    getFoldingRunningCost();
    this.draw();
  },

  addWidth: function(value) {
    this.el.widthCol.append("<div class='input-group folding_side folding_side_width_"+this.id+"' data-widthid='"+this.width_index+"' data-stockid='"+this.id+"' style='margin-top: 10px;'><input type='text' class='form-control' value='"+value+"'> <span class='input-group-addon fold_direction_remove' id='fold_width_"+this.id+"_"+this.width_index+"_remove'><span class='glyphicon glyphicon-remove'></span></span> </div>");
    this.directions.width[this.width_index] = value;
    $("#fold_width_"+this.id+"_"+this.width_index+"_remove").click($.proxy(function(event){
      $($($(event)[0].currentTarget).parent('div')).remove();
      this.directions.width[$($(event)[0].currentTarget).parent('div').data('widthid')] = null;
      this.num_widths--;
      getFoldingRunningCost();
      this.draw();
    }, this));
    $(".folding_side_width_"+this.id).keyup($.proxy(function(event){
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
    getFoldingRunningCost();
    this.draw();
  },

  addHeight: function(value) {
    this.el.heightCol.append("<div class='input-group folding_side folding_side_height_"+this.id+"' data-heightid='"+this.height_index+"' data-stockid='"+this.id+"' style='margin-top: 10px;'><input type='text' class='form-control' value='"+value+"'> <span class='input-group-addon fold_direction_remove' id='fold_height_"+this.id+"_"+this.height_index+"_remove'><span class='glyphicon glyphicon-remove'></span></span> </div>");
    this.directions.height[this.height_index] = value;
    $("#fold_height_"+this.id+"_"+this.height_index+"_remove").click($.proxy(function(event){
      $($($(event)[0].currentTarget).parent('div')).remove();
      this.directions.height[$($(event)[0].currentTarget).parent('div').data('heightid')] = null;
      this.num_heights--;
      getFoldingRunningCost();
      this.draw();
    }, this));
    $(".folding_side_height_"+this.id).keyup($.proxy(function(event){
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
    getFoldingRunningCost();
    this.draw();
  },

  getData: function() {
    var folds = {};
    if (this.fold_type == "" || this.score_type == null){
      this.fold_type = "false";
    }
    if (this.fold_direction == "" || this.fold_direction == null){
      this.fold_direction = "false";
    }
    folds['directions'] = this.directions;
    folds['fold_direction'] = this.fold_direction;
    folds['fold_type'] = this.fold_type;
    return folds;
  }

} 