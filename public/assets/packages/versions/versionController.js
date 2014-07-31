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


VersionController = function(options) {
  this.init(options);
};

VersionController.prototype = {

  init: function(options) {
    this.el = {
      div:    null,
      table:  null
    };

    this.id = options.id;
    this.head = null;
    this.tail = null;
    this.num_versions = 0;
    this.quantities;
    this.type = options.type;
    this.indexError = Array();
    this.quantityPercents = Array();
    this.alreadyRun = false;
  },

  //---------------------------------------------------------------------------

  run: function() {
    this.el.div = $("#version_body");
    this.populate_quantities();
  },

  getData: function() {
    var versionObj = {version: null};
    versionObj.version = [];
    versionObj.quantities = [];
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      versionObj.version.push(ptr);
    }
    versionObj.quantities.push(this.quantities);
    return JSON.stringify(versionObj, function(key, value) {
      if (key == "next" || key == "previous"){
        return;
      }
      return value;
    });
  },

  retrieve_versions: function(){
    $.ajax({url:root+'/api/project/versions/'+this.id, success: $.proxy(function(data) {
      temp_versions = data;
      if (Object.keys(temp_versions).length > 0){
        $.each(temp_versions, $.proxy(function(index, element){
          var quantities = Array();
          $.each(element, function(quantity_id, quantity){
            quantities.push(quantity);
          });
          if (this.find_version(index) == false){
            this.create_version(index, quantities);
          }
        },this));
      }else if (this.head == null){
        this.create_version("");
      }
      this.calculateVersionQuantities();
      $("#version-loader").hide();
      if (this.el.div != undefined){
        this.el.div.show();
      }
    }, this), dataType: 'json'});
  },

  create_version: function(name, quantities){
    if (typeof(quantities)==='undefined' || quantities == null) {quantities = Array();}
    if (this.num_versions == 0){
      var new_version = new Version(1, name, null, null, quantities);
      this.head = new_version;
      this.tail = new_version;
    }else{
      var new_version = new Version(this.tail.id + 1, name, null, this.tail, quantities);
      this.tail.next = new_version;
      this.tail = new_version;
    }

    this.num_versions++;
    return new_version;

  },

  remove_version: function(target){

    version_info = target[0].id.split("-");
    
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.id == version_info[1]){
        if (this.num_versions == 1){
          this.head = null;
          this.tail = null;
          this.num_versions--;
          return;
        }
        if(ptr == this.head){
          this.head = ptr.next;
          ptr.next.previous = null;
        }else if(ptr == this.tail){
          this.tail = ptr.previous;
          ptr.previous.next = null;
        }else{
          ptr.previous.next = ptr.next;
          ptr.next.previous = ptr.previous;
        }

        this.num_versions--;
        return ptr;
      }
    }

    this.calculateVersionQuantities();
    this.draw();

  },

  find_version: function(name){
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.name == name){
        return ptr;
      }
    }
    return false;
  },

  populate_quantities: function(){
    $("#version-loader").show();
    if (this.el.div != undefined){
      this.el.div.hide();
    }
    $.ajax({url:root+'/api/project/quantities/'+this.id, success: $.proxy(function(data) {
      this.quantities = data;
      this.refresh_quantities();
      this.retrieve_versions();
      return this.quantities;
    }, this), dataType: 'json'});
  },

  refresh_quantities: function(){
    $.ajax({url:root+'/api/project/quantities/'+this.id, success: $.proxy(function(data) {
      $.each(this.quantities, $.proxy(function(old_index, old_quantity){
        var match = false;
        $.each(data, $.proxy(function(new_index, new_quantity){
          if (old_quantity.quantity == new_quantity.quantity){
            match = true;
          }
        }, this));
        if (!match){
          this.quantities.splice(old_index, 1);
          for (var ptr = this.head; ptr != null; ptr = ptr.next){
            ptr.quantities.splice(old_index, 1);  
          }
        }
      }, this));
      $.each(data, $.proxy(function(new_index, new_quantity){
        var match = false;
        $.each(this.quantities, $.proxy(function(old_index, old_quantity){
          if (old_quantity.quantity == new_quantity.quantity){
            match = true;
          }
        }, this));
        if (!match){
          this.quantities.push(new_quantity);
        }
      }, this));
    }, this), dataType: 'json'});
  },

  calculateVersionQuantities: function(){

    if (this.type == "percent"){
      $.each(this.quantities, $.proxy(function(index, element){
        var total = 0;
        for (var version = this.head.next; version != null; version = version.next){
          if (index == 0){
            this.quantityPercents[version.id] = version.quantity(index)/this.quantities[index].quantity;
          }else{
            version.changeQuantity(index, Math.ceil(this.quantityPercents[version.id]*this.quantities[index].quantity));
          }
          if (!version.quantity(index) || isNaN(version.quantity(index))){
            version.changeQuantity(index, 0);
          }
          total = total + parseFloat(version.quantity(index));
        }
        var cur_index = index + 1;
        if (total > this.quantities[index].quantity){
          total = 0;
          this.indexError[index] = "has-error";
        }else{
          this.indexError[index] = "";
        }
        this.head.changeQuantity(index, this.quantities[index].quantity - total);
      }, this));  
    }else if (this.type == "manual"){
      $.each(this.quantities, $.proxy(function(index, element){
        var total = 0;
        for (var version = this.head.next; version != null; version = version.next){
          if (!version.quantity(index) || isNaN(version.quantity(index))){
            version.changeQuantity(index, 0);
          }
          total = total + parseFloat(version.quantity(index));
        }
        var cur_index = index + 1;
        if (total > this.quantities[index].quantity){
          total = 0;
          this.indexError[index] = "has-error";
        }else{
          this.indexError[index] = "";
        }
        this.head.changeQuantity(index, this.quantities[index].quantity - total);
      }, this));  
    }

    this.draw();

  },

  updateVersionQuantity: function(target){

    version_info = target[0].id.split("-");

    for (var version = this.head; version != null; version = version.next){
      if (version.id == version_info[1]){
        version.changeQuantity(version_info[2], target.val());
      }
    }
    this.calculateVersionQuantities();

  },

  updateVersionName: function(target){

    version_info = target[0].id.split("-");

    for (var version = this.head; version != null; version = version.next){
      if (version.id == version_info[1]){
        version.changeName(target.val());
      }
    }

    this.calculateVersionQuantities();

  },

  draw: function(){

    this.el.div.html("");
    
    if (this.type == "percent"){
      this.el.div.append("<div class='checkbox' id='version_type'><label><input type='checkbox' name='percent' checked> Percentage based</label></div>");
    }else{
      this.el.div.append("<div class='checkbox' id='version_type'><label><input type='checkbox' name='percent'> Percentage based</label></div>");
    }
    this.el.version_type = $("#version_type");

    this.el.version_type.bind("click", $.proxy(function(){
      if (this.type == "percent"){
        this.type = "manual";
      }else{
        this.type = "percent";
      }
      this.calculateVersionQuantities();
      this.draw();
    }, this));
    this.el.div.append("<table id='version_table' class='table table-striped table-condensed'></table>");
    this.el.table = $("#version_table");

    this.el.table.append("<thead><tr id='table_header'></tr></thead>");
    this.el.table_header = $("#table_header");
    this.el.table_header.append("<th></th>");
    this.el.table_header.append("<th>Version Name</th>");

    $.each(this.quantities, $.proxy(function(index, element){
      var cur_index = index + 1;
      this.el.table_header.append("<th>Quantity "+cur_index+"</th>");
    }, this));+3

    this.el.table.append("<tbody id='table_body'></tbody>");
    this.el.table_body = $("#table_body");

    for (var version = this.head; version != null; version = version.next){
      this.el.table_body.append("<tr id='"+version.id+"'></tr>");
      if (version != this.head){
        $("#"+version.id).append("<td><button type='button' class='btn btn-danger btn-sm' id='remove-"+version.id+"'><span class='glyphicon glyphicon-remove'></span></button></td>");
      }else{
        $("#"+version.id).append("<td></td>");
      }
      $("#"+version.id).append("<td><div class='form-group'><input type='text' id='name-"+version.id+"' value='"+version.name+"' placeholder='Version Name' class='form-control input-sm'></div></td>");
      $.each(this.quantities, $.proxy(function(index, element){
        if (!version.quantity(index)){
          version.quantities[index] = 0;
        }
        if (index == 0 && version != this.head){
          $("#"+version.id).append("<td><div class='form-group'><input type='text' id='quantity-"+version.id+"-"+index+"' placeholder='Quantity' value='"+version.quantity(index)+"' class='form-control input-sm'></div></td>");
        }else if (this.type == 'percent' && version != this.head){
          $("#"+version.id).append("<td><div class='form-group'><input type='text' id='quantity-"+version.id+"-"+index+"' placeholder='Quantity' disabled value='"+version.quantity(index)+"' class='form-control input-sm'></div></td>");
        }else if (version == this.head){
          $("#"+version.id).append("<td><div class='form-group "+this.indexError[index]+"'><input type='text' id='quantity-"+version.id+"-"+index+"' placeholder='Quantity' disabled value='"+version.quantity(index)+"' class='form-control input-sm'></div></td>");
        }else{
          $("#"+version.id).append("<td><div class='form-group'><input type='text' id='quantity-"+version.id+"-"+index+"' placeholder='Quantity' value='"+version.quantity(index)+"' class='form-control input-sm'></div></td>");
        }

        $("#name-"+version.id).bind( "change", $.proxy(function(e) {
          this.updateVersionName($(e.target));
        }, this));

        $("#remove-"+version.id).bind( "click", $.proxy(function(e) {
          this.remove_version($(e.currentTarget));
        }, this));

        $("#quantity-"+version.id+"-"+index).bind( "change", $.proxy(function(e) {10
          this.updateVersionQuantity($(e.target));
        }, this));

      }, this));
    }

    this.el.div.append("<a id='add_row' class='btn btn-success pull-left'>Add Version</a>");
    this.el.add_row = $("#add_row");

    this.el.add_row.bind("click", $.proxy(function(){
      this.create_version('');
      this.calculateVersionQuantities();
    }, this));

  }

} 