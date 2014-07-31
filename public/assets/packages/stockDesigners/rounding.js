
roundingController = function(options) {
  this.init(options);
};

roundingController.prototype = {

  init: function(options) {
    this.el = {
      manual_buttons: $("#rounding_stocks_buttons input[type='checkbox']"),
      topleft_button: $("#rounding_stocks_buttons input[type='checkbox'][value='topleft']"),
      topright_button: $("#rounding_stocks_buttons input[type='checkbox'][value='topright']"),
      bottomleft_button: $("#rounding_stocks_buttons input[type='checkbox'][value='bottomleft']"),
      bottomright_button: $("#rounding_stocks_buttons input[type='checkbox'][value='bottomright']"),
      canvas: $("#rounding_canvas")[0]
    }

    this.canvas_width = parseFloat(options.canvas_width);
    this.canvas_height = parseFloat(options.canvas_height);
    this.width = parseFloat(options.width);
    this.height = parseFloat(options.height);
    this.id = options.id;
    this.projectId = options.projectId;
    this.corners = {topleft: false, topright: false, bottomleft: false, bottomright: false};

    this.el.manual_buttons.change($.proxy(function(event){
      this.corners[$(event.target).data('corner')] = $(event.target).is(":checked").toString();
      getRoundingCharges();
      this.draw();
    }, this));

    this.run();
  },

  //---------------------------------------------------------------------------

  run: function() {
    this.restoreData();
  },

  restoreData: function() {
    $.ajax({
      url: root+'/api/project/roundingData/'+this.projectId+'/'+this.id,
      type: 'GET',
      dataType: 'json',
      success: $.proxy(function(data) {
        this.corners.topleft = data.topleft;
        this.corners.topright = data.topright;
        this.corners.bottomleft = data.bottomleft;
        this.corners.bottomright = data.bottomright;
        if (this.corners.topleft == "true"){
          this.el.topleft_button.attr('checked', 'checked');
        }
        if (this.corners.topright == "true"){
          this.el.topright_button.attr('checked', 'checked');
        }
        if (this.corners.bottomleft == "true"){
          this.el.bottomleft_button.attr('checked', 'checked');
        }
        if (this.corners.bottomright == "true"){
          this.el.bottomright_button.attr('checked', 'checked');
        }
        this.draw();
        getRoundingCharges();
      }, this)
    });
  },

  draw: function() {
    paper.setup(this.el.canvas);
    paper.view.viewSize = new paper.Size(this.canvas_width, this.canvas_height);

    var rectangle = new paper.Rectangle(new paper.Point(0, 0), new paper.Point(this.canvas_width, this.canvas_height-2));
    var cornerSize = new paper.Size(15, 15);
    var path = new paper.Path.RoundRectangle(rectangle, cornerSize);
    path.fillColor = 'white';
    path.strokeColor = 'black';

    var path = new paper.Path.RoundRectangle(rectangle, paper.Size(20,20));

//    if (this.height > this.width){
//      var text = new paper.PointText(new paper.Point(this.canvas_width / 2, this.canvas_height/2));
//    }else{
      var text = new paper.PointText(new paper.Point(this.canvas_width / 2 - 10, this.canvas_height/2 + 10));
//    }
    text.justification = 'center';
    text.fillColor = 'black';
    text.fontSize = 50;
    if (this.height > this.width){
//      text.rotation = 270;
    }
    text.content = "A";

//    if (this.height < this.width){
      if (this.corners.topleft == "false"){
        var path = new paper.Path.Rectangle({
            point: [0, 0],
            size: [20, 20],
            fillColor: "#FFFFFF"
        });
        var path = new paper.Path.Line({
            from: [0, 0],
            to: [0, 20],
            strokeColor: 'black'
        });
        var path = new paper.Path.Line({
            from: [0, 0],
            to: [20, 0],
            strokeColor: 'black'
        });
      }

      if (this.corners.topright == "false"){
        var path = new paper.Path.Rectangle({
            point: [this.canvas_width-20, 0],
            size: [20, 20],
            fillColor: "#FFFFFF"
        });
        var path = new paper.Path.Line({
            from: [this.canvas_width-20, 0],
            to: [this.canvas_width, 0],
            strokeColor: 'black'
        });
        var path = new paper.Path.Line({
            from: [this.canvas_width, 0],
            to: [this.canvas_width, 20],
            strokeColor: 'black'
        });
      }

      if (this.corners.bottomleft == "false"){
        var path = new paper.Path.Rectangle({
            point: [0, this.canvas_height-20],
            size: [20, this.canvas_height],
            fillColor: "#FFFFFF"
        });
        var path = new paper.Path.Line({
            from: [0, this.canvas_height-2],
            to: [0, this.canvas_height-22],
            strokeColor: 'black'
        });
        var path = new paper.Path.Line({
            from: [0, this.canvas_height-2],
            to: [20, this.canvas_height-2],
            strokeColor: 'black'
        });
      }

      if (this.corners.bottomright == "false"){
        var path = new paper.Path.Rectangle({
            point: [this.canvas_width-20, this.canvas_height-20],
            size: [20, 20],
            fillColor: "#FFFFFF"
        });
        var path = new paper.Path.Line({
            from: [this.canvas_width, this.canvas_height-2],
            to: [this.canvas_width, this.canvas_height-22],
            strokeColor: 'black'
        });
        var path = new paper.Path.Line({
            from: [this.canvas_width-20, this.canvas_height-2],
            to: [this.canvas_width, this.canvas_height-2],
            strokeColor: 'black'
        });
      }
//    }else{
//      if (this.corners.topleft == "false"){
//        var path = new paper.Path.Rectangle({
//            point: [0, this.canvas_height-20],
//            size: [20, this.canvas_height],
//            fillColor: "#FFFFFF"
//        });
//        var path = new paper.Path.Line({
//            from: [0, this.canvas_height-2],
//            to: [0, this.canvas_height-22],
//            strokeColor: 'black'
//        });
//        var path = new paper.Path.Line({
//            from: [0, this.canvas_height-2],
//            to: [20, this.canvas_height-2],
//            strokeColor: 'black'
//        });
//      }
//
//      if (this.corners.topright == "false"){
//        var path = new paper.Path.Rectangle({
//            point: [0, 0],
//            size: [20, 20],
//            fillColor: "#FFFFFF"
//        });
//        var path = new paper.Path.Line({
//            from: [0, 0],
//            to: [0, 20],
//            strokeColor: 'black'
//        });
//        var path = new paper.Path.Line({
//            from: [0, 0],
//            to: [20, 0],
//            strokeColor: 'black'
//        });
//      }
//
//      if (this.corners.bottomleft == "false"){
//        var path = new paper.Path.Rectangle({
//            point: [this.canvas_width-20, this.canvas_height-20],
//            size: [20, 20],
//            fillColor: "#FFFFFF"
//        });
//        var path = new paper.Path.Line({
//            from: [this.canvas_width, this.canvas_height-2],
//            to: [this.canvas_width, this.canvas_height-22],
//            strokeColor: 'black'
//        });
//        var path = new paper.Path.Line({
//            from: [this.canvas_width, this.canvas_height-2],
//            to: [this.canvas_width-20, this.canvas_height-2],
//            strokeColor: 'black'
//        });
//      }
//
//      if (this.corners.bottomright == "false"){
//        var path = new paper.Path.Rectangle({
//            point: [this.canvas_width-20, 0],
//            size: [20, 20],
//            fillColor: "#FFFFFF"
//        });
//        var path = new paper.Path.Line({
//            from: [this.canvas_width, 0],
//            to: [this.canvas_width, 20],
//            strokeColor: 'black'
//        });
//        var path = new paper.Path.Line({
//            from: [this.canvas_width, 0],
//            to: [this.canvas_width-20, 0],
//            strokeColor: 'black'
//        });
//      }
//    }

    paper.view.draw();
  },

  getData: function() {
    return this.corners;
  }

} 