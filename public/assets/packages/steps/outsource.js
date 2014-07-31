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


Outsource = function(id, action) {
	this.init(id, action);
};

Outsource.prototype = {
	init: function(id, action) {
		this.el = {
			div: $("#"+action+"_container"),
			outsource: $("#"+action+"_container #outsource"),
			company: null,
            comments: null,
            reference: null,
			table: null
		};

		this.id = id;
		this.action = action;
		this.company = null;
        this.comments = "";
        this.reference = "";
		this.costs = {};
		this.markup = {};
        this.total = {};
        this.time = {};
		if (action == "projectGraphicDesign" || action == "projectVariableData"){
			this.retrieve_outsource();
		}
		this.quantities = null;
	},

	retrieve_outsource: function() {
		this.el.outsource.hide();
     	$.ajax({
          url: root+'/api/project/outsource/'+this.id+'/'+this.action,
          type: 'GET',
          dataType: 'json',
          success: $.proxy(function(data) {
          	$.ajax({
				url: root+'/api/project/quantitiesOvers/'+this.id,
				type: 'GET',
				dataType: 'json',
				success: $.proxy(function(quantities) {
					this.quantities = quantities;
					if (data.outsource){
		          		this.company = data.outsource.field_value;
		          	}
                    if (data.outsource_description){
                        this.comments = data.outsource_description.field_value;
                    }
                    if (data.outsource_reference){
                        this.reference = data.outsource_reference.field_value;
                    }
                    if (data.outsource_cost){
                        this.costs = data.outsource_cost;
                    }
                    if (data.outsource_time){
                        this.time = data.outsource_time;
                    }
		          	if (data.outsource_markup){
		          		this.markup = data.outsource_markup;
		          	}
					if (this.el.outsource.length > 0){
						this.draw();
						this.get_suppliers();
					}
					this.el.outsource.show();
				}, this)
		  	});          	
          }, this)
      });
	},

	draw: function() {
		this.el.outsource.html("");
		this.el.outsource.html("<div class='col-md-12'> <hr /> </div> <h4>Outsource</h4> <p class='text-info'>Please specify a supplier below to outsource this project</p> <div class='form-group'> <div class='controls'> <select multiple='multiple' id='outsource_company' class='form-control chosen-select' placeholder='Supplier...' style='width: 100%;' name='outsource'></select> </div> </div> <div class='form-group'> <div class='controls'> <input type='text' class='form-control' placeholder='Reference #' id='outsource_reference' /></div> </div><div class='form-group'> <div class='controls'> <textarea class='form-control' placeholder='Outsource description' rows='5' id='outsource_description' name='outsource_description' cols='50'></textarea> </div>  </div><table class='table' id='outsource_table'></table>");
		this.el.company = $("#"+this.action+"_container #outsource_company");
        this.el.reference = $("#"+this.action+"_container #outsource_reference");
        this.el.comments = $("#"+this.action+"_container #outsource_description");
		this.el.table = $("#"+this.action+"_container #outsource_table");
		this.el.comments.hide();
        this.el.reference.hide();
        this.el.table.hide();
		this.el.table.html("");

        this.el.comments.val(this.comments);
        this.el.reference.val(this.reference);
		this.draw_table();

		this.el.company.chosen({
		  width: '100%',
		  max_selected_options: 1,
		  no_results_text: "Oops, nothing found!",
		  placeholder_text_multiple: "Suppliers..."
		});

		this.el.company.on('change', $.proxy(function(evt, params) {
			if (params.selected){
                this.el.comments.show();
                this.el.reference.show();
				this.el.table.show();
				this.company = params.selected;
			    if (typeof(window["outsource_enabled_"+this.action]) == "function"){
			      window["outsource_enabled_"+this.action](false);
			    }
			}
			if (params.deselected){
				this.company = null;
                this.el.comments.hide();
                this.el.reference.hide();
				this.el.table.hide();
				this.el.comments.val("");
			    if (typeof(window["outsource_disabled_"+this.action]) == "function"){
			      window["outsource_disabled_"+this.action](false);
			    }
			}
		},this));
	},

	draw_table: function() {
		this.el.table.html("");
		this.el.table.append("<thead><tr id='head-"+this.id+"'></tr></thead>");
		
		$("#"+this.action+"_container #head-"+this.id).append("<th></th>");
		$.each(this.quantities, $.proxy(function(index, element){
			$("#"+this.action+"_container #head-"+this.id).append("<th>"+element.quantity+"</th>");
		}, this));

		this.el.table.append("<tbody id='body-"+this.id+"''></tbody>");

		$("#"+this.action+"_container #body-"+this.id).append("<tr id='cost-"+this.id+"''><td>Cost</td></tr>");
		$.each(this.quantities, $.proxy(function(index, element){
			if (this.costs[element.id] != undefined){
				$("#"+this.action+"_container #cost-"+this.id).append("<td>$<a href='#' class='outsource-cost' data-id='"+element.id+"'>"+parseFloat(this.costs[element.id]).toFixed(2)+"</a></td>");
			}else{
				this.costs[element.id] = 0.00;
				$("#"+this.action+"_container #cost-"+this.id).append("<td>$<a href='#' class='outsource-cost' data-id='"+element.id+"'>0.00</a></td>");
			}
		}, this));

		$("#"+this.action+"_container #body-"+this.id).append("<tr id='markup-"+this.id+"''><td>Mark Up (%)</td></tr>");
		$.each(this.quantities, $.proxy(function(index, element){
			if (this.markup[element.id] != undefined){
				$("#"+this.action+"_container #markup-"+this.id).append("<td><a href='#' class='outsource-markup' data-id='"+element.id+"'>"+parseFloat(this.markup[element.id])+"</a>%</td>");
			}else{
				this.markup[element.id] = 0;
				$("#"+this.action+"_container #markup-"+this.id).append("<td><a href='#' class='outsource-markup' data-id='"+element.id+"'>0</a>%</td>");
			}
		}, this));
		$("#"+this.action+"_container #body-"+this.id).append("<tr id='sale-"+this.id+"''><td>Sale</td></tr>");
		$.each(this.quantities, $.proxy(function(index, element){
			this.total[element.id] = parseFloat((parseFloat(this.costs[element.id])*parseFloat(this.markup[element.id])/100) + parseFloat(this.costs[element.id])).toFixed(2);
			$("#"+this.action+"_container #sale-"+this.id).append("<td>$"+this.total[element.id]+"</td>");
		}, this));

        $("#"+this.action+"_container #body-"+this.id).append("<tr id='time-"+this.id+"''><td>Time (Hours)</td></tr>");
        $.each(this.quantities, $.proxy(function(index, element){
            if (this.time[element.id] != undefined){
                $("#"+this.action+"_container #time-"+this.id).append("<td><a href='#' class='outsource-time' data-id='"+element.id+"'>"+parseFloat(this.time[element.id])+"</a></td>");
            }else{
                this.time[element.id] = 0;
                $("#"+this.action+"_container #time-"+this.id).append("<td><a href='#' class='outsource-time' data-id='"+element.id+"'>0</a></td>");
            }
        }, this));

		var self = this;
		$.fn.editable.defaults.mode = 'popup';

	    this.el.table.find(".outsource-cost").each($.proxy(function(index, element){
	    	$(element).editable({
			    type: 'text',
			    title: 'Enter price',
			    validate: function(value) {
				    if($.trim(value) == '') {
				        return 'This field is required';
				    }
				    if(!$.isNumeric($.trim(value))){
				        return 'This field must be a number';
				    }
				},
			    success: function(response, newValue) {
			    	if (self.costs[$(this).data('id')] == undefined){
			    		self.costs[$(this).data('id')] = 0.00;
			    	}
			    	self.costs[$(this).data('id')] = parseFloat(newValue).toFixed(2);
			    	self.draw_table();
			    }
			});
	    },this));
        this.el.table.find(".outsource-markup").each($.proxy(function(index, element){
            $(element).editable({
                type: 'text',
                title: 'Enter price',
                validate: function(value) {
                    if($.trim(value) == '') {
                        return 'This field is required';
                    }
                    if(!$.isNumeric($.trim(value))){
                        return 'This field must be a number';
                    }
                },
                success: function(response, newValue) {
                    if (self.costs[$(this).data('id')] == undefined){
                        self.costs[$(this).data('id')] = 0;
                    }
                    self.markup[$(this).data('id')] = parseFloat(newValue);
                    self.draw_table();
                }
            });
        },this));
        this.el.table.find(".outsource-time").each($.proxy(function(index, element){
            $(element).editable({
                type: 'text',
                title: 'Enter price',
                validate: function(value) {
                    if($.trim(value) == '') {
                        return 'This field is required';
                    }
                    if(!$.isNumeric($.trim(value))){
                        return 'This field must be a number';
                    }
                },
                success: function(response, newValue) {
                    if (self.time[$(this).data('id')] == undefined){
                        self.time[$(this).data('id')] = 0;
                    }
                    self.time[$(this).data('id')] = parseFloat(newValue);
                    self.draw_table();
                }
            });
        },this));
	},

	get_suppliers: function() {
        this.suppliers = "";
		$.ajax({
			url: root+'/api/search/suppliers',
			type: 'GET',
			dataType: 'json',
			data: {
			},
			error: function() {
				callback();
			},
			success: $.proxy(function(res) {
				defaults = res.data;
				for (var key in defaults) {
					if (defaults.hasOwnProperty(key)) {
						var obj = defaults[key];
						if (this.company == obj['id']+"-"+obj['name']){
                            this.el.comments.show();
                            this.el.reference.show();
							this.el.table.show();
						    if (typeof(window["outsource_enabled_"+this.action]) == "function"){
						      window["outsource_enabled_"+this.action](true);
						    }
							this.el.company.append($("<option></option>").attr("value", obj['id']+"-"+obj['name']).text(obj['name']).attr('selected', 'selected'));
						}else{
							this.el.company.append($("<option></option>").attr("value", obj['id']+"-"+obj['name']).text(obj['name']));
						}
					}
				}
				this.el.company.trigger('chosen:updated');
			}, this),
		});
	},

	save: function() {
		if (this.el.outsource.length > 0){
			$.ajax({
				url: root+'/api/project/outsource/'+this.id+'/'+this.action+'/save',
				type: 'POST',
				data: {company: this.company, costs: this.costs, markup: this.markup, time: this.time, total: this.total, comments: this.el.comments.val(), reference: this.el.reference.val()},
				success: $.proxy(function(data) {
				}, this)
			});
		}
	}
} 