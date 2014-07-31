/******************************************************************************

This is a very simple binary tree based bin packing algorithm that is initialized
with a fixed width and height and will fit each block into the first node where
it fits and then split that node into 2 parts (down and right) to track the
remaining whitespace.

Best results occur when the input blocks are sorted by height, or even better
when sorted by max(width,height).

Inputs:
------

  w:       width of target rectangle
  h:      height of target rectangle
  blocks: array of any objects that have .w and .h attributes

Outputs:
-------

  marks each block that fits with a .fit attribute pointing to a
  node with .x and .y coordinates

Example:
-------

  var blocks = [
    { w: 100, h: 100 },
    { w: 100, h: 100 },
    { w:  80, h:  80 },
    { w:  80, h:  80 },
    etc
    etc
  ];

  var packer = new Packer(500, 500);
  packer.fit(blocks);

  for(var n = 0 ; n < blocks.length ; n++) {
    var block = blocks[n];
    if (block.fit) {
      Draw(block.fit.x, block.fit.y, block.w, block.h);
    }
  }


******************************************************************************/

PackerDutch = function(w, h, block_width, block_height) {
  this.init(w, h, block_width, block_height);
};

PackerDutch.prototype = {

  init: function(w, h, block_width, block_height) {
    this.root = { x: 0, y: 0, w: w, h: h };
    this.width = w;
    this.height = h;
    this.block_width = block_width;
    this.block_height = block_height;
    this.rows = 0;
    this.columns = 0;
    this.dutch_rows = 0;
    this.dutch_columns = 0;
  },

  fit: function(blocks) {
    var node, block;
    block = blocks[0];
    blocks.pop();

    this.appendForward(this.root, blocks);
    this.appendReverseRight(this.root, blocks);
    this.appendReverseDown(this.root, blocks);
  },

  maxBlocks: function() {
    return 1;
  },
  
  appendForward: function(root, blocks){
    while (node = this.findNode(this.root, blocks)){
      node.used = true;
      node.right = {w: node.w -  this.block_width, h: this.block_height, x: node.x + this.block_width, y: node.y};
      node.down = {w: node.w, h: node.h - this.block_height, x: node.x, y: node.y + this.block_height};
      var new_block = {w: this.block_width, h: this.block_height, area: this.block_height*this.block_width};
      new_block.fit = node;
      blocks.push(new_block);
    }
  },

  findNode: function(root, blocks){
    if (root.used){
      if (root.right.w >= this.block_width || root.down.h >= this.block_height){
        return this.findNode(root.right, blocks) || this.findNode(root.down, blocks);
      }else if (root.right.w >= this.block_height || root.down.h >= this.block_width){
        return this.findNode(root.right, blocks) || this.findNode(root.down, blocks);
      }
    }else if((this.block_width <= root.w && this.block_height <= root.h)) {
      return root;
    }else{
      return;
    }
  },

  appendReverseRight: function(root, blocks){
    if (root.right){
      return this.appendReverseRight(root.right, blocks);
    }else {
      if (root.w >= this.block_height && this.height >= this.block_width){
        root.used = true;
        root.right = {w: root.w - this.block_height, h: this.block_width, x: root.x + this.block_height, y: root.y};
        var new_block = {w: this.block_height, h: this.block_width, area: this.block_width*this.block_height};
        new_block.fit = root;
        blocks.push(new_block);

        this.cascadeReverseDown(root, blocks);
        this.appendReverseRight(root.right, blocks);
      }
      return root;
    }
  },

  cascadeReverseDown: function(root, blocks){
    if (root.y <= this.height - this.block_width){
      root.down = {w: this.block_height, h: this.block_width, x: root.x, y: root.y + this.block_width};
      if (!root.used){
        var new_block = {w: this.block_height, h: this.block_width, area: this.block_width*this.block_height};
        new_block.fit = root;
        blocks.push(new_block);
      }
      root.used = true;
      return this.cascadeReverseDown(root.down, blocks);
    }else{
      return;
    }
  },

  appendReverseDown: function(root, blocks){
    if (root.down){
      return this.appendReverseDown(root.down, blocks);
    }else {
      if (root.h >= this.block_width && this.width >= this.block_height && (root.y + this.block_width) <= this.height){
        root.used = true;
        root.down = {w: this.width - this.block_height, h: root.h - this.block_width, x: root.x, y: root.y + this.block_width};
        var new_block = {w: this.block_height, h: this.block_width, area: this.block_width*this.block_height};
        new_block.fit = root;
        blocks.push(new_block);
        this.cascadeReverseRight(root, blocks);
        this.appendReverseDown(root.down, blocks);
      }
      return root;
    }
  },

  cascadeReverseRight: function(root, blocks){
    if (root.x <= this.width - this.block_height){
      root.right = {w: this.width - (root.x + this.block_height), h: this.block_width, x: root.x + this.block_height, y: root.y};
      if (!root.used){
        var new_block = {w: this.block_height, h: this.block_width, area: this.block_width*this.block_height};
        new_block.fit = root;
        blocks.push(new_block);
      }
      root.used = true;
      return this.cascadeReverseRight(root.right, blocks);
    }else{
      return;
    }
  }

}