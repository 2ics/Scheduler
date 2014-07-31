/******************************************************************************

 This is a Imposer page to experiment with binary tree based
 algorithms for packing blocks into a single 2 dimensional bin.

 See individual .js files for descriptions of each algorithm:

  * packer.js         - simple algorithm for a fixed width/height bin
  * packer.growing.js - complex algorithm that grows automatically

 TODO
 ====
  * step by step animated render to watch packing in action (and help debug)
  * optimization - mark branches as "full" to avoid walking them
  * optimization - dont bother with nodes that are less than some threshold w/h (2? 5?)

*******************************************************************************/


SingleImposer = function(options) {
  this.init(options);
};

SingleImposer.prototype = {

  init: function(options) {
    this.el = {
      canvas: $("#"+options.canvas)[0]
    };

    this.block_width = parseFloat(options.block_width);
    this.block_height = parseFloat(options.block_height);
    this.width = parseFloat(options.width);
    this.height = parseFloat(options.height);
    this.rotation = options.rotation;
    this.canvasWidth = options.canvasWidth;
    this.canvasHeight = options.canvasHeight;
    this.margin = parseFloat(options.margin);
    this.gripper = options.gripper;
    this.gripper_side = options.gripper_side;
    this.widthMargin = options.width - (2*this.margin);
    this.heightMargin = options.height - (2*this.margin);
    this.gutter = parseFloat(options.gutter);
    this.bleed = options.bleed;
    this.grain_direction = options.grain_direction;
    this.min_waste = options.min_waste;
    this.num_blocks = 1;
    this.blocks = null;
    this.packerObj = null;

    if (this.bleed == "true"){
      this.bleed = 0.125;
    }else{
      this.bleed = 0;
    }

    if (this.rotation == "Portrait"){
      this.width = parseFloat(options.height);
      this.height = parseFloat(options.width);
      this.widthMargin = options.height - (2*this.margin);
      this.heightMargin = options.width - (2*this.margin);
      this.height = parseFloat(options.width);
      this.canvasWidth = options.canvasHeight;
      this.canvasHeight = options.canvasWidth;
    }

    if (this.gripper == ""){
      this.gripper = 0;
    }

    if (this.gripper_side == "left" || this.gripper_side == "right"){
      if (this.gripper_side > this.width){ this.gripper = this.width; }
      this.widthMargin = this.width - (2*this.margin) - this.gripper;
      this.heightMargin = this.height - (2*this.margin);
    }else{
      if (this.gripper_side > this.height){ this.gripper = this.height; }
      this.heightMargin = this.height - (2*this.margin) - this.gripper;
      this.widthMargin = this.width - (2*this.margin);
    }

    if (this.min_waste == "true"){
      if (this.grain_direction == "long"){
        this.sort_type = "dutch_long";
      }
      if (this.grain_direction == "short"){
        this.sort_type = "dutch_short";
      }
    }else{
      if (this.grain_direction == "long"){
        this.sort_type = "grain_long";
      }
      if (this.grain_direction == "short"){
        this.sort_type = "grain_short";
      }
    }

    this.run();
  },

  //---------------------------------------------------------------------------

  run: function() {
    this.packerObj = this.packer();

    this.num_blocks = 1;
  
    var totalBlocks = this.packerObj.maxBlocks(this.block_width, this.block_height);

    this.blocks = this.deserialize(this.block_width+"x"+this.block_height+"x"+totalBlocks);
    this.packerObj.fit(this.blocks);

    this.draw(this.width, this.height, this.blocks, this.packerObj.root, this.el.canvas);
  },

  //---------------------------------------------------------------------------

  packer: function() { 
    if (this.sort_type == "grain_short" || this.sort_type == "grain_long"){
      return new Packer(this.widthMargin, this.heightMargin);
    }else if(this.sort_type == "dutch_long"){
      return new PackerDutch(this.widthMargin, this.heightMargin, this.block_width, this.block_height);
    }else if(this.sort_type == "dutch_short"){
      return new PackerDutch(this.widthMargin, this.heightMargin, this.block_height, this.block_width);
    }
  },

  draw: function(width, height, blocks, root, canvas) {
    paper.setup(this.el.canvas);
    var scaleX = Math.max(width, this.canvasWidth)/Math.min(width, this.canvasWidth);
    var scaleY = Math.max(height, this.canvasHeight)/Math.min(height, this.canvasHeight);
    paper.view.viewSize = new paper.Size(width*scaleX + 26, height*scaleY + 26);
    var path = new paper.Path.Rectangle({
        point: [0, 24],
        size: [width*scaleX  + 1, height*scaleY + 1],
        strokeColor: 'black',
        strokeWidth: 1,
        fillColor: "#EEEEEE"
    });
    var text = new paper.PointText(new paper.Point(width*scaleX+7, height*scaleY/2 + 24));
    text.justification = 'center';
    text.fillColor = 'black';
    text.rotation = 90;
    text.content = parseFloat(this.height).toFixed(2);
    var text = new paper.PointText(new paper.Point(width*scaleX/2, 17));
    text.justification = 'center';
    text.fillColor = 'black';
    text.content = parseFloat(this.width).toFixed(2);
    var point = [this.margin*scaleX, this.margin*scaleY+24];
    if (this.gripper_side == "left"){
      point = [this.margin*scaleX + this.gripper*scaleX, this.margin*scaleY+24];
    }
    if (this.gripper_side == "top"){
      point = [this.margin*scaleX, this.margin*scaleY + this.gripper*scaleY+24];
    }
    var path = new paper.Path.Rectangle({
        point: point,
        size: [this.widthMargin*scaleX, this.heightMargin*scaleY],
        strokeColor: 'black',
        strokeWidth: 0.5,
        fillColor: "#FFFFFF"
    });
    var n, block;
    
    for (n = 0 ; n < blocks.length ; n++) {
      block = blocks[n];
      if (block.fit){
        var point = [block.fit.x*scaleX + this.margin*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + 24.5];
        if (this.gripper_side == "left"){
          point = [block.fit.x*scaleX + this.margin*scaleX + this.gripper*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + 24.5];
        }
        if (this.gripper_side == "top"){
          point = [block.fit.x*scaleX + this.margin*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gripper*scaleY + 24.5];
        }
        var path = new paper.Path.Rectangle({
            point: point,
            size: [block.w*scaleX - 0.5, block.h*scaleY - 0.5],
            fillColor: "#FFFFFF"
        });
        var point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + 24.5];
        if (this.gripper_side == "left"){
          point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + this.gripper*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + 24.5];
        }
        if (this.gripper_side == "top"){
          point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + this.gripper*scaleY + 24.5];
        }
        var path = new paper.Path.Rectangle({
            point: point,
            size: [block.w*scaleX - (2*this.gutter*scaleX) - 0.5, block.h*scaleY - (2*this.gutter*scaleY) - 0.5],
            strokeColor: 'black',
            strokeWidth: 0.5,
            dashArray: [10,4],
            fillColor: "#FF5B5A"
        });
        var point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + this.bleed*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + this.bleed*scaleY + 24.5];
        if (this.gripper_side == "left"){
          point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + this.bleed*scaleX + this.gripper*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + this.bleed*scaleY + 24.5];
        }
        if (this.gripper_side == "top"){
          point = [block.fit.x*scaleX + this.margin*scaleX + this.gutter*scaleX + this.bleed*scaleX + 0.5, block.fit.y*scaleY + this.margin*scaleY + this.gutter*scaleY + this.bleed*scaleY + this.gripper*scaleY + 24.5];
        }

        var path = new paper.Path.Rectangle({
            point: point,
            size: [block.w*scaleX-(2*this.gutter)*scaleX-(2*this.bleed)*scaleX - 0.5, block.h*scaleY-(2*this.gutter)*scaleY-(2*this.bleed)*scaleY - 0.5],
            strokeColor: 'black',
            strokeWidth: 0.5,
            fillColor: this.color(n)
        });
      }
    }
    paper.view.draw();
  },

  deserialize: function(val) {
    var i, j, block, blocks = val.split("\n"), result = [];
    for(i = 0 ; i < blocks.length ; i++) {
      block = blocks[i].split("x");
      if (block.length >= 2)
        result.push({w: parseFloat(block[0]), h: parseFloat(block[1]), num: (block.length == 2 ? 1 : parseFloat(block[2])) });
    }
    var expanded = [];
    for(i = 0 ; i < result.length ; i++) {
      for(j = 0 ; j < result[i].num ; j++)
        expanded.push({w: result[i].w, h: result[i].h, area: result[i].w * result[i].h});
    }
    return expanded;
  },

  serialize: function(blocks) {
    var i, block, str = "";
    for(i = 0; i < blocks.length ; i++) {
      block = blocks[i];
      str = str + block.w + "x" + block.h + (block.num > 1 ? "x" + block.num : "") + "\n";
    }
    return str;
  },

  //---------------------------------------------------------------------------

  colors: {
    pastel:         [ "#428bca" ]
  },

  color: function(n) {
    var cols = this.colors['pastel'];
    return cols[n % cols.length];
  }

  //---------------------------------------------------------------------------

} 