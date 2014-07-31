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


Step = function(name, action, next, previous, disabled, visited, elements, sortable, completed, prerequisites) {
	if (typeof(disabled)==='undefined' || disabled == null) {disabled = true;}
	if (typeof(visited)==='undefined' || visited == null) {visited = false;}
	if (typeof(elements)==='undefined' || elements == null) {elements = {div: null};}
	if (typeof(sortable)==='undefined' || sortable == null) {sortable = true;}
	if (typeof(completed)==='undefined' || completed == null) {completed = false;}
	if (typeof(prerequisites)==='undefined' || prerequisites == null) {prerequisites = {};}
	this.init(name, action, next, previous, disabled, visited, elements, sortable, completed, prerequisites);
};

Step.prototype = {
	init: function(name, action, next, previous, disabled, visited, elements, sortable, completed, prerequisites) {
		this.el = elements;

		this.name = name;
		this.action = action;
		this.next = next;
		this.previous = previous;
		this.disabled = disabled;
		this.completed = completed;
		this.sortable = sortable;
		this.visited = visited;
		this.prerequisites = prerequisites;
		this.additional = false;
		this.outsource = null;
	},

	next: function() {
		return this.next;
	},

	previous: function() {
		return this.previous;
	},

	getName: function() {
		return this.name;
	},

	changeName: function(name) {
		this.name = name;
	},

	getAction: function() {
		return this.action;
	},

	changeAction: function(action) {
		this.action = action;
	},

	getDiv: function() {
		return this.el.div;
	},

	assignDiv: function(div) {
		this.el.div = div;
	},

	complete: function() {
		this.completed = true;
	},

	assignOutsource: function(outsource) {
		this.outsource = outsource;
	}

} 
