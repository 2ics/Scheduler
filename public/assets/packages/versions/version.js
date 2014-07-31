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



 Version = function(id, name, next, previous, quantities) {
  this.init(id, name, next, previous, quantities);
};

Version.prototype = {

  init: function(id, name, next, previous, quantities) {
    this.id = id;
    this.name = name;
    this.next = next;
    this.previous = previous;
    this.quantities = quantities;
  },

  next: function() {
    return this.next;
  },

  previous: function() {
    return this.previous;
  },

  quantities: function() {
    return this.quantities;
  },

  quantity: function(id) {
    return this.quantities[id];
  },

  appendQuantity: function(id, value){
    this.quantities[id] = value; 
  },

  removeQuantity: function(id){
    this.quantities.splice(id-1, 1);
  },

  changeQuantity: function(id, value){
    this.quantities[id] = value;
  },

  changeName: function(value){
    this.name = value;
  }

} 