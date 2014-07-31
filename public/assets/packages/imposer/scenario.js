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


Imposer = function (options) {
    this.init(options);
};

Imposer.prototype = {

    init: function (options) {
        this.el = {
            canvas: $('#canvas_' + options.id)[0],
            canvas_column: $('#col-canvas-' + options.id),
            table_row: $("#table_row_" + options.id),
            grain: $("#grain_" + options.id),
            rotation: $("#rotation_" + options.id),
            printer: $("#printer_" + options.id),
            min_waste: $("#min_waste_" + options.id),
            gripper: $("#gripper"),
            front_colour: $("input:radio[name=front]"),
            back_colour: $("input:radio[name=back]"),
            gripper: $("#gripper"),
            front_colour_checked: $("input:radio[name=front]:checked"),
            back_colour_checked: $("input:radio[name=back]:checked"),
            gripper_side: $("#gripper_side"),
            quantities: $("#imposer_quantities"),
            overs: $("#overs"),
            gutter: $("#gutter"),
            margin: $("#margin"),
            bleed: $("#bleed")
        };

        this.margin = this.el.margin.val();
        this.stock = options.stock;
        this.selected_row = null;
        this.cutData = null;
        this.waste = null;
        this.cost = null;
        this.blocks = null;
        this.packerObj = null;

        this.width = options.paper_width;
        this.height = options.paper_height;
        this.width_master = options.paper_width;
        this.height_master = options.paper_height;

        this.canvasWidth = options.canvas_width;
        this.canvasHeight = options.canvas_height;
        this.canvasWidth_master = options.canvas_width;
        this.canvasHeight_master = options.canvas_height;

        this.widthMargin = options.paper_width - (2 * this.margin);
        this.heightMargin = options.paper_height - (2 * this.margin);

        this.block_width_master = options.block_width;
        this.block_height_master = options.block_height;

        this.num_blocks = 1;

        if (!this.el.canvas.getContext) // no support for canvas
            return false;

        this.el.grain.change(this.sortChange.bind(this));
        this.el.quantities.change(this.updatePrinters.bind(this));
        this.el.overs.change(this.run.bind(this));
        this.el.gutter.change(this.gutterChange.bind(this));
        this.el.margin.change(this.marginChange.bind(this));
        this.el.front_colour.change(this.colourChange.bind(this));
        this.el.back_colour.change(this.colourChange.bind(this));
        this.el.printer.change(this.printerChange.bind(this));
        this.el.bleed.change(this.bleedChange.bind(this));
        this.el.gripper.change(this.gripperChange.bind(this));
        this.el.gripper_side.change(this.gripperChange.bind(this));
        if (options.optimal) {
            this.optimizeController();
            this.blockOrientation();
        }
        this.el.rotation.change(this.rotationChange.bind(this));
        this.el.min_waste.change(this.sortChange.bind(this));
        this.el.draw = this.el.canvas.getContext("2d");

        var printer_colour = 0;
        if ($("input:radio[name=front]:checked").val() == "colour" || $("input:radio[name=back]:checked").val() == "colour") {
            printer_colour = 1;
        }
        $.getJSON(root + '/api/stock/printers/' + this.stock.name + '/' + this.stock.type + '/' + this.stock.weight + '/' + this.stock.colour + '/' + this.stock.coating + '/' + parseFloat(this.stock.press_width).toFixed(2) + '/' + parseFloat(this.stock.press_height).toFixed(2) + '/' + printer_colour + '/' + this.el.quantities.val() + '/' + price_list, null, $.proxy(function (data) {
            this.stock.printer = data;
            var printers = "";
            $.each(this.stock.printer, function (index, printer) {
                var colour_mode = "(black)";
                if (printer.colour == 1) {
                    colour_mode = "(colour)";
                }
                printers = printers + "<option value='" + printer.id + "'>" + printer.name + " " + colour_mode + "</option>"
            });
            this.el.printer.html(printers);
            this.sortChange();
            this.bleedChange();
            this.printerChange();
            this.run();
        }, this));

    },

    //---------------------------------------------------------------------------

    run: function () {
        this.packerObj = this.packer();

        this.num_blocks = 1;

        var totalBlocks = this.packerObj.maxBlocks(this.block_width, this.block_height);

        this.blocks = this.deserialize(this.block_width + "x" + this.block_height + "x" + totalBlocks);
        this.packerObj.fit(this.blocks);
        this.draw(this.width, this.height, this.blocks, this.packerObj.root, this.el.canvas);
        this.report(this.blocks, this.width, this.height);
        this.getCuttingData(this.blocks);
    },

    //---------------------------------------------------------------------------

    packer: function () {
        if (this.sort_type == "grain_short" || this.sort_type == "grain_long") {
            return new Packer(this.widthMargin, this.heightMargin);
        } else if (this.sort_type == "dutch_long") {
            return new PackerDutch(this.widthMargin, this.heightMargin, this.block_width, this.block_height);
        } else if (this.sort_type == "dutch_short") {
            return new PackerDutch(this.widthMargin, this.heightMargin, this.block_height, this.block_width);
        }
    },

    //---------------------------------------------------------------------------

    report: function (blocks, w, h) {
        var fit = 0, block, n, len = blocks.length, page_cost, total_sheets, print_cost, time, sheets_per_min;
        var overs_percent = this.el.overs.val() / 100;
        if (overs_percent < 1) {
            overs_percent = overs_percent + 1;
        }
        for (n = 0; n < len; n++) {
            block = blocks[n];
            if (block.fit) {
                fit = fit + ((block.w - (2 * this.el.gutter.val()) - (2 * this.bleed)) * (block.h - (2 * this.el.gutter.val()) - (2 * this.bleed)))
            }
        }
        if (blocks.length == 0) {
            this.el.canvas_column.css('background', '#f2dede');
            this.el.table_row.removeClass('active');
            this.el.canvas_column.addClass('inactive');
            $('#projectSettings')
                .data('bootstrapValidator')
                .updateStatus('imposition_table', 'NOT_VALIDATED')
                .validateField('imposition_table');
        } else {
            this.el.canvas_column.css('background', '#ffffff');
            this.el.canvas_column.removeClass('inactive');
        }

        total_sheets = Math.ceil(this.el.quantities.val() * overs_percent / blocks.length);
        if (this.printer) {
            if (($("input:radio[name=front]:checked").val() == "colour" && $("input:radio[name=back]:checked").val() == "none") || ($("input:radio[name=front]:checked").val() == "none" && $("input:radio[name=back]:checked").val() == "colour")) {
                page_cost = this.printer.colour_none;
                sheets_per_min = this.printer.single_side_per_min;
            }
            if ($("input:radio[name=front]:checked").val() == "colour" && $("input:radio[name=back]:checked").val() == "colour") {
                page_cost = this.printer.colour_colour;
                sheets_per_min = this.printer.double_side_per_min;
            }
            if (($("input:radio[name=front]:checked").val() == "black" && $("input:radio[name=back]:checked").val() == "none") || ($("input:radio[name=front]:checked").val() == "none" && $("input:radio[name=back]:checked").val() == "black")) {
                page_cost = this.printer.black_none;
                sheets_per_min = this.printer.single_side_per_min;
            }
            if ($("input:radio[name=front]:checked").val() == "black" && $("input:radio[name=back]:checked").val() == "black") {
                page_cost = this.printer.black_black;
                sheets_per_min = this.printer.double_side_per_min;
            }
            if (($("input:radio[name=front]:checked").val() == "black" && $("input:radio[name=back]:checked").val() == "colour") || ($("input:radio[name=front]:checked").val() == "colour" && $("input:radio[name=back]:checked").val() == "black")) {
                page_cost = this.printer.colour_black;
                sheets_per_min = this.printer.double_side_per_min;
            }

            print_cost = (page_cost * total_sheets);
            time = total_sheets / sheets_per_min;
            paper_cost = (this.stock.press_price_m / 1000 * total_sheets);
            labour_cost = (time * this.printer.labour_rate);
            total_cost = print_cost + paper_cost + labour_cost;
            var values = [parseFloat(this.stock.press_width).toFixed(2) + "x" + parseFloat(this.stock.press_height).toFixed(2), total_sheets, blocks.length, blocks.length * total_sheets, (100 - Math.floor(100 * fit / (w * h))) + "%", print_cost.toFixed(2), paper_cost.toFixed(2), labour_cost.toFixed(2), total_cost.toFixed(2)];
            this.waste = (100 - Math.floor(100 * fit / (w * h)));
            this.selected_row = values;

            $.each(values, $.proxy(function (index, element) {
                if (this.el.table_row.find(":nth-child(" + (index + 1) + ")").length == 0) {
                    this.el.table_row.append("<td></td>");
                }
                if (index + 1 == 9) {
                    this.cost = element;
                }
                this.el.table_row.find(":nth-child(" + (index + 1) + ")").text(element);
            }, this));

            this.el.table_row.click($.proxy(function (e) {
                stepController.stock_changed();
                if ($(e.target).closest('tr').find(':nth-child(3)').text() == 0) {
                    return;
                }
                var table = $(e.target).closest('table').find('tr').each(function (index, row) {
                    $(row).removeClass("active");
                });
                $(e.target).closest('tr').addClass("active");
                var column = $("#col-canvas-" + $(e.target).closest('tr').data('row'));
                $("canvas").parent('div').each(function (index, element) {
                    if (!$(element).hasClass('inactive')) {
                        $(element).css('background', "#ffffff");
                    }
                });
                column.css('background', "#d9edf7");
            }, this));

            if (this.el.table_row.data('row') == this.el.table_row.parent('tbody').find('tr.active').data('row')) {
                var column = $("#col-canvas-" + this.el.table_row.data('row'));
                column.css('background', "#d9edf7");
            }

            this.getCuttingData(blocks);
        }
    },

    getCuttingData: function (blocks) {
        cutData = {};
        if (this.sort_type == "grain_long" || this.sort_type == "dutch_long") {
            cutData.rows = Math.floor(this.heightMargin / this.block_height);
            cutData.columns = Math.floor(this.widthMargin / this.block_width);
        } else {
            cutData.rows = Math.floor(this.heightMargin / this.block_width);
            cutData.columns = Math.floor(this.widthMargin / this.block_height);
        }
        if (this.sort_type == "dutch_long") {
            cutData.mixed = true;
            cutData.type = "long";
            if (this.block_height <= (this.widthMargin - (cutData.columns * this.block_width))) {
                cutData.side = "right";
                cutData.mixedColumns = Math.floor((this.widthMargin - (cutData.columns * this.block_width)) / this.block_height);
                cutData.mixedRows = Math.floor(this.heightMargin / this.block_width);
            } else if (this.block_width <= (this.heightMargin - (cutData.rows * this.block_height))) {
                cutData.side = "down";
                cutData.mixedColumns = Math.floor(this.widthMargin / this.block_height);
                cutData.mixedRows = Math.floor((this.heightMargin - (cutData.rows * this.block_height)) / this.block_width);
            } else {
                cutData.mixedColumns = 0;
                cutData.mixedRows = 0;
            }
        } else if (this.sort_type == "dutch_short") {
            cutData.mixed = true;
            cutData.type = "short";
            if (this.block_height <= (this.heightMargin - (cutData.rows * this.block_width))) {
                cutData.side = "right";
                cutData.mixedColumns = Math.floor(this.widthMargin / this.block_width);
                cutData.mixedRows = Math.floor((this.heightMargin - (cutData.rows * this.block_width)) / this.block_height);
            } else if (this.block_width <= (this.widthMargin - (cutData.columns * this.block_height))) {
                cutData.side = "down";
                cutData.mixedColumns = Math.floor((this.widthMargin - (cutData.columns * this.block_height)) / this.block_width);
                cutData.mixedRows = Math.floor(this.heightMargin / this.block_height);
            } else {
                cutData.mixedColumns = 0;
                cutData.mixedRows = 0;
            }
        }
        this.cutData = cutData;
    },

    optimizeController: function () {
        var sizes = Array();
        this.rotationChangeDirection("Portrait");
        sizes.push({sort_type: 'dutch_long', 'waste': this.optimizeGetFilled('dutch_long', this.width, this.height), 'rotation': 'Portrait'});
        sizes.push({sort_type: 'dutch_short', 'waste': this.optimizeGetFilled('dutch_short', this.width, this.height), 'rotation': 'Portrait'});
        sizes.push({sort_type: 'grain_short', 'waste': this.optimizeGetFilled('grain_short', this.width, this.height), 'rotation': 'Portrait'});
        sizes.push({sort_type: 'grain_long', 'waste': this.optimizeGetFilled('grain_long', this.width, this.height), 'rotation': 'Portrait'});
        this.rotationChangeDirection("Landscape");
        sizes.push({sort_type: 'dutch_long', 'waste': this.optimizeGetFilled('dutch_long', this.width, this.height), 'rotation': 'Landscape'});
        sizes.push({sort_type: 'dutch_short', 'waste': this.optimizeGetFilled('dutch_short', this.width, this.height), 'rotation': 'Landscape'});
        sizes.push({sort_type: 'grain_short', 'waste': this.optimizeGetFilled('grain_short', this.width, this.height), 'rotation': 'Landscape'});
        sizes.push({sort_type: 'grain_long', 'waste': this.optimizeGetFilled('grain_long', this.width, this.height), 'rotation': 'Landscape'});

        function compare(a, b) {
            if (a.waste < b.waste)
                return -1;
            if (a.waste > b.waste)
                return 1;
            return 0;
        }

        sizes.sort(compare);

        var optimal_config = sizes.pop();

        var sort = optimal_config.sort_type.split("_");

        if (sort[0] == "dutch") {
            this.el.min_waste.attr('checked', 'checked');
        }

        this.el.grain.val(sort[1]);
console.log(optimal_config);
        this.el.rotation.val(optimal_config.rotation);

        this.sort_type = optimal_config.sort_type;
    },

    optimizeGetFilled: function (sort_type, w, h) {
        this.sort_type = sort_type;
        var packer = this.packer();
        this.blockOrientation();

        var totalBlocks = packer.maxBlocks(this.block_width, this.block_height);
        var blocks = this.deserialize(this.block_width + "x" + this.block_height + "x" + totalBlocks);
        packer.fit(blocks);
        var fit = 0, block, n, len = blocks.length;
        for (n = 0; n < len; n++) {
            block = blocks[n];
            if (block.fit)
                fit = fit + ((block.w - (2 * this.el.gutter.val()) - (2 * this.bleed)) * (block.h - (2 * this.el.gutter.val()) - (2 * this.bleed)));
        }
        return Math.ceil(100 * fit / (w * h));

    },

    sortChange: function () {
        if (this.el.min_waste.is(':checked')) {
            this.sort_type = "dutch_" + this.el.grain.val();
        } else {
            this.sort_type = "grain_" + this.el.grain.val();
        }

        this.blockOrientation();
        this.run();
        select_lowest_cost();
    },

    rotationChange: function () {
        if (this.el.rotation.val() == "Landscape") {
            this.widthMargin = this.width_master - (2 * this.margin);
            this.heightMargin = this.height_master - (2 * this.margin);
            this.width = this.width_master;
            this.height = this.height_master;
            this.canvasWidth = this.canvasWidth_master;
            this.canvasHeight = this.canvasHeight_master;
        } else {
            this.widthMargin = this.height_master - (2 * this.margin);
            this.heightMargin = this.width_master - (2 * this.margin);
            this.width = this.height_master;
            this.height = this.width_master;

            this.canvasWidth = this.canvasHeight_master;
            this.canvasHeight = this.canvasWidth_master;
        }

        this.run();
        select_lowest_cost();
    },

    rotationChangeDirection: function (rotation) {
        if (rotation == "Landscape") {
            this.widthMargin = this.width_master - (2 * this.margin);
            this.heightMargin = this.height_master - (2 * this.margin);
            this.width = this.width_master;
            this.height = this.height_master;
            this.canvasWidth = this.canvasWidth_master;
            this.canvasHeight = this.canvasHeight_master;
        } else {
            this.widthMargin = this.height_master - (2 * this.margin);
            this.heightMargin = this.width_master - (2 * this.margin);
            this.width = this.height_master;
            this.height = this.width_master;

            this.canvasWidth = this.canvasHeight_master;
            this.canvasHeight = this.canvasWidth_master;
        }

        select_lowest_cost();
    },

    gripperChange: function () {
        var griper_value = this.el.gripper.val();
        if (this.el.gripper_side.val() == "left" || this.el.gripper_side.val() == "right") {
            if (this.el.gripper.val() > this.width) {
                griper_value = this.width;
            }
            this.widthMargin = this.width - (2 * this.margin) - griper_value;
            this.heightMargin = this.height - (2 * this.margin);
        } else {
            if (this.el.gripper.val() > this.height) {
                griper_value = this.height;
            }
            this.heightMargin = this.height - (2 * this.margin) - griper_value;
            this.widthMargin = this.width - (2 * this.margin);
        }

        this.blockOrientation();
        this.optimizeController();
        this.blockOrientation();
        this.run();
        select_lowest_cost();
    },

    printerChange: function () {
        $.each(this.stock.printer, $.proxy(function (index, printer) {
            if (printer.id == this.el.printer.val()) {
                this.printer = printer;
            }
        }, this));

        this.run();
    },

    gutterChange: function () {

        this.blockOrientation();
        this.optimizeController();
        this.blockOrientation();
        this.run();
        select_lowest_cost();

    },

    marginChange: function () {

        this.margin = this.el.margin.val();
        this.widthMargin = this.width - (2 * this.margin);
        this.heightMargin = this.height - (2 * this.margin);
        this.gripperChange();
        this.blockOrientation();
        this.optimizeController();
        this.blockOrientation();
        this.run();
        select_lowest_cost();

    },

    colourChange: function () {
        var printer_colour = 0;
        if ($("input:radio[name=front]:checked").val() == "colour" || $("input:radio[name=back]:checked").val() == "colour") {
            printer_colour = 1;
        }
        $.getJSON(root + '/api/stock/printers/' + this.stock.name + '/' + this.stock.type + '/' + this.stock.weight + '/' + this.stock.colour + '/' + this.stock.coating + '/' + (this.stock.press_width).toFixed(2) + '/' + (this.stock.press_height).toFixed(2) + '/' + printer_colour + '/' + this.el.quantities.val() + '/' + price_list, null, $.proxy(function (data) {
            this.stock.printer = data;
            var printers = "";
            $.each(this.stock.printer, function (index, printer) {
                var colour_mode = "(black)";
                if (printer.colour == 1) {
                    colour_mode = "(colour)";
                }
                printers = printers + "<option value='" + printer.id + "'>" + printer.name + " " + colour_mode + "</option>"
            });
            this.el.printer.html(printers);

            this.printerChange();
            this.optimizeController();
            this.blockOrientation();
            this.run();
            select_lowest_cost();
        }, this));
    },

    updatePrinters: function () {
        var printer_colour = 0;
        if ($("input:radio[name=front]:checked").val() == "colour" || $("input:radio[name=back]:checked").val() == "colour") {
            printer_colour = 1;
        }
        $.getJSON(root + '/api/stock/printers/' + this.stock.name + '/' + this.stock.type + '/' + this.stock.weight + '/' + this.stock.colour + '/' + this.stock.coating + '/' + parseFloat(this.stock.press_width).toFixed(2) + '/' + parseFloat(this.stock.press_height).toFixed(2) + '/' + printer_colour + '/' + this.el.quantities.val() + '/' + price_list, null, $.proxy(function (data) {
            this.stock.printer = data;
            var printers = "";
            $.each(this.stock.printer, function (index, printer) {
                var colour_mode = "(black)";
                if (printer.colour == 1) {
                    colour_mode = "(colour)";
                }
                printers = printers + "<option value='" + printer.id + "'>" + printer.name + " " + colour_mode + "</option>"
            });
            this.el.printer.html(printers);

            this.printerChange();
            this.run();
        }, this));
    },

    bleedChange: function () {
        if (this.el.bleed.is(':checked')) {
            this.bleed = 0.125;
        } else {
            this.bleed = 0;
        }

        this.blockOrientation();
        this.optimizeController();
        this.blockOrientation();
        this.run();
        select_lowest_cost();
    },

    blockOrientation: function () {
        if (this.sort_type == "grain_short") {
            this.block_width = this.block_height_master + (2 * this.el.gutter.val()) + (2 * this.bleed);
            this.block_height = this.block_width_master + (2 * this.el.gutter.val()) + (2 * this.bleed);
        } else {
            this.block_width = this.block_width_master + (2 * this.el.gutter.val()) + (2 * this.bleed);
            this.block_height = this.block_height_master + (2 * this.el.gutter.val()) + (2 * this.bleed);
        }
    },

    draw: function (width, height, blocks, root, canvas) {
        paper.setup(this.el.canvas);
        var scaleX = Math.max(width, this.canvasWidth) / Math.min(width, this.canvasWidth);
        var scaleY = Math.max(height, this.canvasHeight) / Math.min(height, this.canvasHeight);
        paper.view.viewSize = new paper.Size(width * scaleX + 26, height * scaleY + 26);
        var path = new paper.Path.Rectangle({
            point: [0, 24],
            size: [width * scaleX + 1, height * scaleY + 1],
            strokeColor: 'black',
            strokeWidth: 1,
            fillColor: "#EEEEEE"
        });
        var text = new paper.PointText(new paper.Point(width * scaleX + 7, height * scaleY / 2 + 24));
        text.justification = 'center';
        text.fillColor = 'black';
        text.rotation = 90;
        text.content = parseFloat(this.height).toFixed(2);
        var text = new paper.PointText(new paper.Point(width * scaleX / 2, 17));
        text.justification = 'center';
        text.fillColor = 'black';
        text.content = parseFloat(this.width).toFixed(2);
        var point = [this.margin * scaleX, this.margin * scaleY + 24];
        if (this.el.gripper_side.val() == "left") {
            point = [this.margin * scaleX + this.el.gripper.val() * scaleX, this.margin * scaleY + 24];
        }
        if (this.el.gripper_side.val() == "top") {
            point = [this.margin * scaleX, this.margin * scaleY + this.el.gripper.val() * scaleY + 24];
        }
        var path = new paper.Path.Rectangle({
            point: point,
            size: [this.widthMargin * scaleX, this.heightMargin * scaleY],
            strokeColor: 'black',
            strokeWidth: 0.5,
            fillColor: "#FFFFFF"
        });
        var n, block;
        for (n = 0; n < blocks.length; n++) {
            block = blocks[n];
            if (block.fit) {
                var point = [block.fit.x * scaleX + this.margin * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + 24.5];
                if (this.el.gripper_side.val() == "left") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gripper.val() * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + 24.5];
                }
                if (this.el.gripper_side.val() == "top") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gripper.val() * scaleY + 24.5];
                }
                var path = new paper.Path.Rectangle({
                    point: point,
                    size: [block.w * scaleX - 0.5, block.h * scaleY - 0.5],
                    fillColor: "#FFFFFF"
                });
                var point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + 24.5];
                if (this.el.gripper_side.val() == "left") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + this.el.gripper.val() * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + 24.5];
                }
                if (this.el.gripper_side.val() == "top") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + this.el.gripper.val() * scaleY + 24.5];
                }
                var path = new paper.Path.Rectangle({
                    point: point,
                    size: [block.w * scaleX - (2 * this.el.gutter.val() * scaleX) - 0.5, block.h * scaleY - (2 * this.el.gutter.val() * scaleY) - 0.5],
                    strokeColor: 'black',
                    strokeWidth: 0.5,
                    dashArray: [10, 4],
                    fillColor: "#FF5B5A"
                });
                var point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + this.bleed * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + this.bleed * scaleY + 24.5];
                if (this.el.gripper_side.val() == "left") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + this.bleed * scaleX + this.el.gripper.val() * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + this.bleed * scaleY + 24.5];
                }
                if (this.el.gripper_side.val() == "top") {
                    point = [block.fit.x * scaleX + this.margin * scaleX + this.el.gutter.val() * scaleX + this.bleed * scaleX + 0.5, block.fit.y * scaleY + this.margin * scaleY + this.el.gutter.val() * scaleY + this.bleed * scaleY + this.el.gripper.val() * scaleY + 24.5];
                }
                var path = new paper.Path.Rectangle({
                    point: point,
                    size: [block.w * scaleX - (2 * this.el.gutter.val()) * scaleX - (2 * this.bleed) * scaleX - 0.5, block.h * scaleY - (2 * this.el.gutter.val()) * scaleY - (2 * this.bleed) * scaleY - 0.5],
                    strokeColor: 'black',
                    strokeWidth: 0.5,
                    fillColor: this.color(n)
                });
            }
        }
        paper.view.draw();
    },

    deserialize: function (val) {
        var i, j, block, blocks = val.split("\n"), result = [];
        for (i = 0; i < blocks.length; i++) {
            block = blocks[i].split("x");
            if (block.length >= 2)
                result.push({w: parseFloat(block[0]), h: parseFloat(block[1]), num: (block.length == 2 ? 1 : parseFloat(block[2])) });
        }
        var expanded = [];
        for (i = 0; i < result.length; i++) {
            for (j = 0; j < result[i].num; j++)
                expanded.push({w: result[i].w, h: result[i].h, area: result[i].w * result[i].h});
        }
        return expanded;
    },

    serialize: function (blocks) {
        var i, block, str = "";
        for (i = 0; i < blocks.length; i++) {
            block = blocks[i];
            str = str + block.w + "x" + block.h + (block.num > 1 ? "x" + block.num : "") + "\n";
        }
        return str;
    },

    //---------------------------------------------------------------------------

    colors: {
        pastel: [ "#428bca" ]
    },

    color: function (n) {
        var cols = this.colors['pastel'];
        return cols[n % cols.length];
    }

    //---------------------------------------------------------------------------

} 