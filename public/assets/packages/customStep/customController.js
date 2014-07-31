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


customStepController = function (options) {
    this.init(options);
};

customStepController.prototype = {

    init: function (options) {
        this.el = {
            div: $("#" + options.div),
            table: null
        };

        this.id = options.id;
        this.step = options.step;
        this.head = null;
        this.tail = null;
        this.num_customs = 0;
        this.currentField = null;
        this.quantities;
        this.type = options.type;
        this.indexError = Array();
        this.quantityPercents = Array();
        this.suppliers = "";
    },

    //---------------------------------------------------------------------------

    run: function () {
        var self = this;
        this.el.div.editable({
            selector: 'a',
            type: 'text',
            title: 'Enter price',
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
                if (!$.isNumeric($.trim(value))) {
                    return 'This field must be a number';
                }
            },
            success: function (response, newValue) {
                var field = self.find_customId($(this).data('customfieldid'));
                if ($(this).data('pricetype') == "cost") {
                    field.quantities[$(this).data('quantityid')]["cost"] = parseFloat(newValue).toFixed(2);
                    var markup = parseFloat(field.quantities[$(this).data('quantityid')]["markup"]) / 100;
                    if (markup < 1) {
                        markup = markup + 1;
                    }
                    $.each (self.quantities, function(index,element){
                        for (var ptr = self.head; ptr != null; ptr = ptr.next) {
                            if (parseFloat(ptr.quantities[element.id].cost) == 0){
                                var markup = ptr.quantities[element.id].markup;
                                if(markup < 1){
                                    markup = markup + 1;
                                }
                                ptr.quantities[element.id].cost =  parseFloat(newValue).toFixed(2);
                                ptr.quantities[element.id].total =  parseFloat(ptr.quantities[element.id].cost * markup).toFixed(2);
                            }
                        }
                    });
                    field.quantities[$(this).data('quantityid')]["total"] = parseFloat(parseFloat(field.quantities[$(this).data('quantityid')].cost) * parseFloat(markup)).toFixed(2);
                }
                if ($(this).data('pricetype') == "markup") {
                    field.quantities[$(this).data('quantityid')]["markup"] = parseFloat(newValue);
                    var markup = parseFloat(field.quantities[$(this).data('quantityid')]["markup"]) / 100;
                    if (markup < 1) {
                        markup = markup + 1;
                    }
                    $.each (self.quantities, function(index,element){
                        for (var ptr = self.head; ptr != null; ptr = ptr.next) {
                            if (parseFloat(ptr.quantities[element.id].markup) == 0){
                                ptr.quantities[element.id].markup =  parseFloat(newValue).toFixed(0);
                                var markup = ptr.quantities[element.id].markup;
                                if(markup < 1){
                                    markup = markup + 1;
                                }
                                ptr.quantities[element.id].markup =  parseFloat(newValue).toFixed(0);
                                ptr.quantities[element.id].total =  parseFloat(ptr.quantities[element.id].cost * markup).toFixed(2);
                            }
                        }
                    });
                    field.quantities[$(this).data('quantityid')]["total"] = parseFloat(parseFloat(field.quantities[$(this).data('quantityid')].cost) * parseFloat(markup)).toFixed(2);
                }
                if ($(this).data('pricetype') == "total") {
                    field.quantities[$(this).data('quantityid')]["total"] = parseFloat(newValue).toFixed(2);
                    var markup = parseFloat(field.quantities[$(this).data('quantityid')]["markup"]) / 100;
                    console.log(markup);
                    if (markup < 1) {
                        markup = markup + 1;
                    }
                    $.each (self.quantities, function(index,element){
                        for (var ptr = self.head; ptr != null; ptr = ptr.next) {
                            if (parseFloat(ptr.quantities[element.id].total) == 0){
                                var markup = ptr.quantities[element.id].markup;
                                if(markup < 1){
                                    markup = markup + 1;
                                }
                                ptr.quantities[element.id].total =  parseFloat(newValue).toFixed(2);
                                ptr.quantities[element.id].cost =  parseFloat(ptr.quantities[element.id].total / markup).toFixed(2);
                            }
                        }
                    });
                    console.log(markup);
                    field.quantities[$(this).data('quantityid')]["cost"] = parseFloat(parseFloat(field.quantities[$(this).data('quantityid')].total) / parseFloat(markup)).toFixed(2);
                }
                if ($(this).data('pricetype') == "time") {
                    $.each (self.quantities, function(index,element){
                        for (var ptr = self.head; ptr != null; ptr = ptr.next) {
                            if (parseFloat(ptr.quantities[element.id].time) == 0){
                                ptr.quantities[element.id].time =  parseFloat(newValue).toFixed(0);
                            }
                        }
                    });
                    field.quantities[$(this).data('quantityid')]["time"] = parseFloat(newValue);
                }
                self.draw();
            }
        });
        $("#" + this.step + "_custom-loader").show();
        this.el.div.hide();
        this.suppliers = "";
        $.ajax({
            url: root + '/api/search/suppliers',
            type: 'GET',
            dataType: 'json',
            data: {
            },
            error: function () {
                callback();
            },
            success: $.proxy(function (res) {
                defaults = res.data;
                $.each(res, $.proxy(function (index, element) {
                    $.each(element, $.proxy(function (index, select) {
                        this.suppliers = this.suppliers + "<option value='" + select.id + "-" + select.name + "'>" + select.name + "</option>";
                    }, this));
                }, this));
                this.populate_quantities();
            }, this)
        });
    },

    retrieveData: function () {
        this.head = null;
        this.tail = null;
        this.num_customs = 0;
        $.ajax({
            url: root + '/api/project/customs/' + this.id + '/' + this.step,
            type: 'GET',
            dataType: 'json',
            data: {
            },
            error: function () {
                callback();
            },
            success: $.proxy(function (data) {
                $.each(data, $.proxy(function (index, field) {
                    this.create_field(field.relatables.description.field_value, field.relatables.reference.field_value, field.relatables.caliper.field_value,  field.prices, field.relatables.outsource.field_value);
                }, this));
                $("#" + this.step + "_custom-loader").hide();
                this.el.div.show();
                if (this.num_customs == 0){
                    this.create_field("", "", "", {}, "");
                }
                this.draw();
            }, this)
        });
    },

    getData: function () {
        var customObj = {custom: null};
        customObj.custom = [];
        customObj.quantities = [];
        for (var ptr = this.head; ptr != null; ptr = ptr.next) {
            customObj.custom.push(ptr);
        }
        return JSON.stringify(customObj, function (key, value) {
            if (key == "next" || key == "previous") {
                return;
            }
            return value;
        });
    },

    create_field: function (name, reference, caliper, quantities, outSource) {
        if (typeof(quantities) === 'undefined' || quantities == null) {
            quantities = {};
            $.each(this.quantities, $.proxy(function (index, element) {
                quantities[element.id] = {'cost': '0.00', 'markup': '0', 'total': '0.00', 'time': '0'};
            }, this));
        }
        if (typeof(outSource) === 'undefined' || quantities == null) {
            outSource = "";
        }
        if (typeof(reference) === 'undefined' || quantities == null) {
            reference = "";
        }
        if (typeof(caliper) === 'undefined' || quantities == null) {
            caliper = "";
        }
        $.each(this.quantities, $.proxy(function (index, element) {
            if (quantities[element.id] == undefined) {
                quantities[element.id] = {'cost': '0.00', 'markup': '0', 'total': '0.00', 'time': '0'};
            }
        }, this));
        if (this.num_customs == 0) {
            var new_custom = new Custom(1, name, reference, caliper, null, null, quantities, outSource);
            this.head = new_custom;
            this.tail = new_custom;
        } else {
            var new_custom = new Custom(this.tail.id + 1, name, reference, caliper, null, this.tail, quantities, outSource);
            this.tail.next = new_custom;
            this.tail = new_custom;
        }

        this.num_customs++;
        return new_custom;
    },

    remove_custom: function (custom_id) {

        for (var ptr = this.head; ptr != null; ptr = ptr.next) {
            if (ptr.id == custom_id) {
                if (this.num_customs == 1) {
                    return;
                }
                if (ptr == this.head) {
                    this.head = ptr.next;
                    ptr.next.previous = null;
                } else if (ptr == this.tail) {
                    this.tail = ptr.previous;
                    ptr.previous.next = null;
                } else {
                    ptr.previous.next = ptr.next;
                    ptr.next.previous = ptr.previous;
                }

                this.num_customs--;
                this.draw();
                return ptr;
            }
        }

    },

    find_custom: function (name) {
        for (var ptr = this.head; ptr != null; ptr = ptr.next) {
            if (ptr.name == name) {
                return ptr;
            }
        }
        return false;
    },

    find_customId: function (id) {
        for (var ptr = this.head; ptr != null; ptr = ptr.next) {
            if (ptr.id == id) {
                return ptr;
            }
        }
        return false;
    },

    populate_quantities: function () {
        $.ajax({
            url: root + '/api/project/quantities/' + this.id,
            success: $.proxy(function (data) {
                this.quantities = {};
                $.each(data, $.proxy(function (index, element) {
                    element = element;
                    this.quantities[element.id] = element;
                }, this));
                this.retrieveData();
                this.draw();
                return this.quantities;
            }, this),
            dataType: 'json'
        });
    },

    updateDescription: function (id, value) {
        for (var custom = this.head; custom != null; custom = custom.next) {
            if (custom.id == id) {
                custom.changeName(value);
            }
        }
    },

    updateReference: function (id, value) {
        for (var custom = this.head; custom != null; custom = custom.next) {
            if (custom.id == id) {
                custom.changeReference(value);
            }
        }
    },

    updateCaliper: function (id, value) {
        for (var custom = this.head; custom != null; custom = custom.next) {
            if (custom.id == id) {
                custom.changeCaliper(value);
            }
        }
    },

    updateOutSource: function (id, value) {
        for (var custom = this.head; custom != null; custom = custom.next) {
            if (custom.id == id) {
                custom.changeOutSource(value);
            }
        }
    },

    draw: function () {

        this.el.div.html("");

        for (var custom = this.head; custom != null; custom = custom.next) {
            this.el.div.append("<div class='col-md-1' style='padding:0px;text-align:center;margin-bottom: 15px;'><button type='button' data-custom='" + custom.id + "' class='btn btn-danger btn-sm' id='remove-" + this.step + "-" + custom.id + "'><span class='glyphicon glyphicon-remove'></span></button></div>");
            this.el.div.append("<div class='col-md-5' style='padding:0px; margin-bottom: 15px;'><textarea class='form-control' id='description-" + this.step + "-" + custom.id + "' data-customfieldid='" + custom.id + "'>" + custom.name + "</textarea></div>");
            this.el.div.append("<div class='col-md-3' style='padding-right:0px; margin-bottom: 15px;'><select multiple='multiple' class='form-control chosen-select' data-customfieldid='" + custom.id + "' placeholder='Supplier...' style='width: 100%; display: none;' id='outsource-" + this.step + "-" + custom.id + "'></select></div>");
            this.el.div.append("<div class='col-md-3' style='padding-right:0px; margin-bottom: 15px;'><input type='text' class='form-control' placeholder='Reference #' id='reference-" + this.step + "-" + custom.id + "' data-customfieldid='" + custom.id + "' value='"+custom.reference+"'/></div>");
            this.el.div.append("<div class='col-md-3' style='padding-right:0px; margin-bottom: 15px;'><input type='text' class='form-control' placeholder='Caliper Increase' id='caliper-" + this.step + "-" + custom.id + "' data-customfieldid='" + custom.id + "' value='"+custom.caliper+"'/></div>");
            this.el.div.append("<table id='" + this.step + "_custom_table-" + custom.id + "' style='padding-top:15px;' class='table table-striped table-bordered table-condensed'></table>");
            this.el.table = $("#" + this.step + "_custom_table-" + custom.id);

            this.el.table.append("<thead><tr id='" + this.step + "_table_header-" + custom.id + "'></tr></thead>");
            this.el.table_header = $("#" + this.step + "_table_header-" + custom.id);
            this.el.table_header.append("<th class='text-center'></th>");

            $.each(this.quantities, $.proxy(function (index, element) {
                this.el.table_header.append("<th class='text-center'>" + Math.ceil(element.quantity) + "</th>");
            }, this));

            this.el.table_header.parent('thead').append("<tr id='" + this.step + "_table_subheader-" + custom.id + "'></tr>");
            this.el.table_subheader = $("#" + this.step + "_table_subheader-" + custom.id);

            this.el.table.append("<tbody id='" + this.step + "_table_body-" + custom.id + "'></tbody>");
            this.el.table_body = $("#" + this.step + "_table_body-" + custom.id);
            this.currentField = custom;
            var outsourced = false;

            this.el.table_body.append("<tr id='" + this.step + "-" + custom.id + "-cost'></tr>");
            $("#" + this.step + "-" + custom.id + "-cost").append("<td>Cost</td>");
            this.el.table_body.append("<tr id='" + this.step + "-" + custom.id + "-markup'></tr>");
            $("#" + this.step + "-" + custom.id + "-markup").append("<td>Mark Up (%)</td>");
            this.el.table_body.append("<tr id='" + this.step + "-" + custom.id + "-total'></tr>");
            $("#" + this.step + "-" + custom.id + "-total").append("<td>Total</td>");
            this.el.table_body.append("<tr id='" + this.step + "-" + custom.id + "-time'></tr>");
            $("#" + this.step + "-" + custom.id + "-time").append("<td>Time (Hours)</td>");
            $.each(this.quantities, $.proxy(function (index, element) {
                if (custom.quantities[element.id] == undefined) {
                    $("#" + this.step + "-" + custom.id + "-cost").append("<td class='text-right'>$<a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='cost'>0.00</a></td>");
                } else {
                    $("#" + this.step + "-" + custom.id + "-cost").append("<td class='text-right'>$<a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='cost'>" + custom.quantities[element.id].cost + "</a></td>");
                }

                if (custom.quantities[element.id] == undefined) {
                    $("#" + this.step + "-" + custom.id + "-markup").append("<td class='text-right'><a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='markup'>0</a>%</td>");
                } else {
                    $("#" + this.step + "-" + custom.id + "-markup").append("<td class='text-right'><a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='markup'>" + custom.quantities[element.id].markup + "</a>%</td>");
                }

                if (custom.quantities[element.id] == undefined) {
                    $("#" + this.step + "-" + custom.id + "-total").append("<td class='text-right'>$<a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='total'>0.00</a></td>");
                } else {
                    $("#" + this.step + "-" + custom.id + "-total").append("<td class='text-right'>$<a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='total'>" + custom.quantities[element.id].total + "</a></td>");
                }

                if (custom.quantities[element.id] == undefined) {
                    $("#" + this.step + "-" + custom.id + "-time").append("<td class='text-right'><a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='time'>0</a></td>");
                } else {
                    $("#" + this.step + "-" + custom.id + "-time").append("<td class='text-right'><a href='#' data-quantityid='" + element.id + "' data-customfieldid='" + custom.id + "' data-pricetype='time'>" + custom.quantities[element.id].time + "</a></td>");
                }

            }, this));

            $("#description-" + this.step + "-" + custom.id).bind("keyup", $.proxy(function (e) {
                this.updateDescription($(e.target).data('customfieldid'), $(e.target).val());
            }, this));

            $("#reference-" + this.step + "-" + custom.id).bind("keyup", $.proxy(function (e) {
                this.updateReference($(e.target).data('customfieldid'), $(e.target).val());
            }, this));

            $("#caliper-" + this.step + "-" + custom.id).bind("keyup", $.proxy(function (e) {
                this.updateCaliper($(e.target).data('customfieldid'), $(e.target).val());
            }, this));

            $("#remove-" + this.step + "-" + custom.id).bind("click", $.proxy(function (e) {
                this.remove_custom($(e.currentTarget).data('custom'));
            }, this));

            $("#outsource-" + this.step + "-" + custom.id).chosen({
                width: '100%',
                max_selected_options: 1,
                no_results_text: "Oops, nothing found!",
                placeholder_text_multiple: "Suppliers..."
            });
            $("#outsource-" + this.step + "-" + this.currentField.id).append(this.suppliers);
            if (custom.outSource != "" && outsourced == false) {
                outsourced = true;
                $("#outsource-" + this.step + "-" + this.currentField.id + " option[value='" + custom.outSource + "']").attr('selected', 'selected');
            }
            $("#outsource-" + this.step + "-" + this.currentField.id).trigger('chosen:updated');
            $("#outsource-" + this.step + "-" + custom.id).change($.proxy(function (evt, params) {
                if (params.selected) {
                    this.updateOutSource($(evt.target).data('customfieldid'), params.selected);
                } else if (params.deselected) {
                    this.updateOutSource($(evt.target).data('customfieldid'), "");
                }
            }, this));
        }

        this.el.div.append("<a id='" + this.step + "_add_row' class='btn btn-success pull-left'>Add Custom Field</a>");
        this.el.add_row = $("#" + this.step + "_add_row");

        this.el.add_row.bind("click", $.proxy(function () {
            this.create_field('');
            this.draw();
        }, this));

    }

} 