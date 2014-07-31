StepController = function(options) {
  this.init(options);
};

StepController.prototype = {

  init: function(options) {
    this.el = {
      div: $("#"+options.div),
      body:$("#"+options.body)
    };

    this.el.div.sortable({
      helper: "clone",
      update:  $.proxy(function(event, ui){
        this.updateOrder(ui);
      },this),
      items: ".stepwizard-step:not(.not-sortable)"
    });
    this.projectId = options.projectId;
    this.head = null;
    this.tail = null;
    this.num_steps = 0;
    this.active = null;
    this.printing = true;

    this.run();
  },

  //---------------------------------------------------------------------------

  run: function() {
    $.ajax({
      url: root+'/api/project/all_steps/'+this.projectId,
      type: 'GET',
      dataType: 'json',
      error: function() {

      },
      success: $.proxy(function(steps) {
        $.each(steps, $.proxy(function(index, step){
          step.prerequisites = JSON.parse(step.prerequisites);
          if (step.sortable == "false"){
            step.sortable = false;
          }
          if (step.sortable == "true"){
            step.sortable = true;
          }
          this.add_step_direct(step.step_name, step.step_action, false, null, null, step.sortable, true, step.prerequisites);
          this.load_step('projectDocket');
        }, this));
        if (Object.keys(steps).length == 0){
          this.add_step("Details", "projectInformation", false, null, null, false, false, {});
          this.add_step("Printing", "projectSettings", true, null, null, true, false, {'projectInformation': "true"});
          this.add_step("Cutting", "projectCutting", true, null, null, true, false, {'projectInformation': "true", 'projectSettings': "true"});
          this.add_step("Docket", "projectDocket", true, null, null, false, false, {});
          this.add_step("Create Step", "projectCreateStep", true, null, null, false, false, {});
          this.load_step(this.head.action);
        }
      },this),
    });
  },

  add_step: function(name, action, disabled, visited, elements, sortable, completed, prerequisites){
    if (this.step_exists(name)){
      return;
    }
    if (this.num_steps == 0){
      var new_step = new Step(name, action, null, null, disabled, visited, elements, sortable, completed, prerequisites);
      this.head = new_step;
      this.tail = new_step;
    }else{
      var new_step = new Step(name, action, null, this.tail, disabled, visited, elements, sortable, completed, prerequisites);
      this.tail.next = new_step;
      this.tail = new_step;
      while (!this.checkPrerequisites(action)){
        new_step.previous.next = new_step.next;
        new_step.next.previous = new_step.previous;
        new_step.next = new_step.previous;
        new_step.previous = new_step.previous.previous;

        new_step.previous.next = new_step;
        new_step.next.previous = new_step;
        if (new_step.next == null){
          this.tail = new_step;
        }
      }
    }

    this.sync_steps();
    this.num_steps++;
    this.update_statuses();
    this.draw();
    return new_step;

  },

  add_step_before: function(name, action, disabled, visited, elements, sortable, completed, prerequisites, before){
    if (this.step_exists(name) && !this.step_exists(before)){
      return;
    }
    if (this.num_steps == 0){
      var new_step = new Step(name, action, null, null, disabled, visited, elements, sortable, completed, prerequisites);
      this.head = new_step;
      this.tail = new_step;
    }else{
      for (var ptr = this.head; ptr != null; ptr = ptr.next){
        if (ptr.name == before){
          var new_step = new Step(name, action, ptr, ptr.previous, disabled, visited, elements, sortable, completed, prerequisites);
          ptr.previous.next = new_step;
          ptr.previous = new_step;
          while (!this.checkPrerequisites(action)){
            new_step.previous.next = new_step.next;
            new_step.next.previous = new_step.previous;
            new_step.next = new_step.previous;
            new_step.previous = new_step.previous.previous;

            new_step.previous.next = new_step;
            new_step.next.previous = new_step;
            if (new_step.next == null){
              this.tail = new_step;
            }
          }
        }
      }
    }

    this.sync_steps();
    this.num_steps++;
    this.update_statuses();
    this.draw();
    return new_step;

  },

  add_step_after: function(name, action, disabled, visited, elements, sortable, completed, prerequisites, after){
    if (this.step_exists(name) && !this.step_exists_action(after)){
      return;
    }
    if (this.num_steps == 0){
      var new_step = new Step(name, action, null, null, disabled, visited, elements, sortable, completed, prerequisites);
      this.head = new_step;
      this.tail = new_step;
    }else{
      for (var ptr = this.head; ptr != null; ptr = ptr.next){
        if (ptr.action == after){
          var new_step = new Step(name, action, ptr.next, ptr, disabled, visited, elements, sortable, completed, prerequisites);
          ptr.next.previous = new_step;
          ptr.next = new_step;
        }
      }
    }

    this.sync_steps();
    this.num_steps++;
    this.update_statuses();
    this.draw();
    return new_step;

  },

  add_step_direct: function(name, action, disabled, visited, elements, sortable, completed, prerequisites) {
    if (this.num_steps == 0){
      var new_step = new Step(name, action, null, null, disabled, visited, elements, sortable, completed, prerequisites);
      this.head = new_step;
      this.tail = new_step;
    }else{
      var new_step = new Step(name, action, null, this.tail, disabled, visited, elements, sortable, completed, prerequisites);
      this.tail.next = new_step;
      this.tail = new_step;
    }
    this.num_steps++;
    return new_step;
  },

  remove_step: function(action, move_next) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == action){
        var name = [];
        name = action.split("_");
        if (this.num_steps == 1){
          this.head = null;
          this.tail = null;
          this.num_steps--;
          ptr.el.div.remove();
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

        ptr.el.div.remove();
        this.num_steps--;

        $.ajax({
          url: root+'/api/project/remove_step',
          type: 'POST',
          dataType: 'text',
          data: {'id': this.projectId, 'action': ptr.action},
          error: function() {

          },
          success: function(res) {
          },
        });
        this.sync_steps();
        this.update_statuses();
        this.draw();

        if (move_next == true){
          this.load_step(this.active.next.action);
          ptr.el.div.hide();

        }

        return ptr;
      }
    }
  },

  sync_steps: function() {
    var index = 0;
    var stepsObj = {'id': this.projectId};
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      index++;
      stepsObj[ptr.action] = {};
      stepsObj[ptr.action]['step_name'] = ptr.name;
      stepsObj[ptr.action]['step_action'] = ptr.action;
      stepsObj[ptr.action]['step_order'] = index;
      stepsObj[ptr.action]['sortable'] = ptr.sortable;
      stepsObj[ptr.action]['prerequisites'] = ptr.prerequisites;
    }
    $.ajax({
      url: root+'/api/project/add_steps',
      type: 'POST',
      dataType: 'text',
      data: stepsObj,
      error: function() {

      },
      success: function(res) {
      },
    });
  },

  get_caliper: function(step) {
      return $.ajax({
          url: root+'/api/project/caliper/'+this.projectId+'/'+step,
          type: 'GET',
          data: null,
          dataType: 'json',
          error: function() {

          }
      });
  },

  clear_steps: function() {
    for (var ptr = stepController.head; ptr != null; ptr = ptr.next){
      if (ptr.action != "projectInformation" && ptr.action != "projectSettings" && ptr.action != "projectDocket"){
        stepController.remove_step(ptr.action);
      }
    }
  },

  step_exists: function(name) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.name == name){
        return true;
      }
    }
    return false;
  },

  step_exists_action: function(action) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == action){
        return true;
      }
    }
    return false;
  },

  toggle_step: function(name, action, prerequisites) {
    if (typeof(prerequisites)==='undefined' || prerequisites == null) {prerequisites = {};}
    if (this.step_exists(name)){
      this.remove_step(action);
    }else{
      if (!$.isEmptyObject(prerequisites)){
        prerequisites = JSON.parse(prerequisites);
      }
      if(action == "projectGraphicDesign" || action == "projectVariableData"){
        this.add_step_before(name, action, false, null, null, null, null, prerequisites, "Printing");
      }else{
        this.add_step_before(name, action, true, null, null, null, null, prerequisites, "Docket");
      }
    }
    this.update_statuses();
    this.draw();
  },

  disable_printing: function() {
    this.printing = false;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action != "projectInformation" && ptr.action != "projectSettings" && ptr.action != "projectDocket" && ptr.action != "projectCreateStep"){
        this.remove_step(ptr.action);
      }
    }
    this.load_step("projectInformation");
  },

  enable_printing: function() {
    this.printing = true;
  },

  draw: function() {
    this.el.div.html("");
    var i = 1;
    var state, btn, sortable, settingsPtr;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == "projectSettings"){
        var settingsPtr = ptr;
      }
    }
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      state = "default"; btn = "btn-default";
      if (ptr.visited){ state = "default"; btn = "btn-warning"; }
      if (ptr.completed){ state = "default"; btn = "btn-success"; }
      if (ptr == this.active){ state = "active"; btn = "btn-primary"; }
      if (ptr.sortable == false){
        sortable = "not-sortable";
      }else{
        sortable = "";
      }
      if (ptr.disabled){
        state = "disabled"; btn = "btn-danger";
      }
      if (ptr.action == "projectCreateStep"){
        sortable = "not-sortable";
        state = "default";
        btn = "btn-success";
      }
      this.el.div.append("<div class='stepwizard-step "+state+" "+sortable+"' id='step-"+i+"' data-name='"+ptr.action+"'><div class='step-handle'><a href='#' data-name='"+ptr.action+"'><div type='button' class='btn "+btn+" btn-circle' data-name='"+ptr.action+"'>"+i+"</div> <p data-name='"+ptr.action+"'>"+ptr.name+"</p></a></div></div>"); 

      $("#step-"+i).bind("click", $.proxy(function(element){
        this.load_step(element.target.dataset.name);
      }, this));

      i++;
    }

    this.el.div.sortable("refresh");
  },

  nextStep: function() {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == this.active.action){
        ptr.complete();
        if (ptr.next != null){
          if (ptr.next.action == "projectSettings" && this.printing != true){
            ptr.next.next.disabled = false;
            ptr.outsource.save();
            this.load_step(ptr.next.next.action);  
          }else{
            ptr.next.disabled = false;
            ptr.outsource.save();
            this.load_step(ptr.next.action);            
          }
        }
        this.sync_steps();
        return;
      }
    }
  },

  updateOrder: function(ui) {
    var order = [];
    var temp_head = null;
    var temp_tail = null;
    var original_head = this.head;
    var original_tail = this.tail;
    this.el.div.find('.stepwizard-step').each($.proxy(function(index, element){
      order.push($(element).data('name'));
    },this));

    $.each(order, $.proxy(function(index, element){
      for (var ptr = this.head; ptr != null; ptr = ptr.next){
        if (ptr.action == order[index]){
          if (index == 0){
            var new_step = new Step(ptr.name, ptr.action, null, null, ptr.disabled, ptr.visited, ptr.el, ptr.sortable, ptr.completed, ptr.prerequisites);
            new_step.outsource = ptr.outsource;
            temp_head = new_step;
            temp_tail = new_step;
          }else{
            var new_step = new Step(ptr.name, ptr.action, null, temp_tail, ptr.disabled, ptr.visited, ptr.el, ptr.sortable, ptr.completed, ptr.prerequisites);
            new_step.outsource = ptr.outsource;
            temp_tail.next = new_step;
            temp_tail = new_step;
          }
          if (ptr == this.active){
            this.active = new_step;
          }
        }
      }
    },this))
    this.head = temp_head;
    this.tail = temp_tail;
    if (!this.checkPrerequisites($(ui.item).data('name'))) {
      this.head = original_head;
      this.tail = original_tail;
    }

    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (this.active.action == ptr.action){
        this.active = ptr;
      }
    }

    this.sync_steps();
    this.update_functions();
    this.draw();
    if (typeof(draw_docket_table) == "function"){
      draw_docket_table(this);
    }
  },

  checkPrerequisites: function(stepAction) {
    var ptr_index = 0;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      ptr_index++;
      var first_prereq_index = 0;
      if (ptr.action == stepAction){
        for (var step = this.head; step != null; step = step.next){
          first_prereq_index++;
          if (ptr.prerequisites[step.action] == "true"){
            if (first_prereq_index > ptr_index){
              return false;
            }
          }
          if (step.prerequisites[ptr.action] == "true"){
            if (first_prereq_index < ptr_index){
              return false;
            } 
          }
        }
      }
    }
    return true;
  },

  load_step: function(stepAction) {
    var step = null;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == stepAction){
        step = ptr;
      }
    }

    if (step == null || step.disabled == true){
      return;
    }

    if (step.action == "projectCreateStep"){
      if (this.active.action == "projectDocket"){
        return;
      }
      var counter = 0;
      for (var ptr = this.head; ptr != null; ptr = ptr.next){
        var name = [];
        name = (ptr.action).split("_");
        if (name[0] == "custom"){
          if (counter < parseFloat(name[2])){
            counter = parseFloat(name[2]);
          }
        }
      }
      counter++;
      this.add_step_after("New Step", "custom_projectStep_"+counter, false, null, null, true, false, {"projectInformation": "true"}, this.active.action);
      return;
    }

    this.hide_steps();
    this.update_statuses();

    if (!step.el.div){
      $.ajax({
          url: root+'/api/project/steps/'+this.projectId+'/'+step.action,
          type: 'GET',
          dataType: 'text',
          success: $.proxy(function(data) {
            $("#current_step-projectLoading").hide();
            this.el.body.append("<div class='step_container' id='current_step-"+step.action+"'></div>");
            step.assignDiv($("#current_step-"+step.action));
            step.el.div.hide();
            step.el.div.html(data);
            this.show_step(step.action);
            step.outsource = new Outsource(this.projectId, step.action);
            this.step_functions(step);
            $("#current_step-loading").hide();
          }, this)
      });
    }else{
      this.show_step(step.action);
      this.step_functions(step);
      var stepNameParts = [];
      stepNameParts = step.action.split("_");
      if (stepNameParts[0] == "custom"){
        if (typeof(update_customs) == "function"){
          update_customs();
        }
      }
      $("#current_step-loading").hide();
    }
    step.visited = true;
    this.active = step;

    if (step.action == "projectSettings"){
      if (versions){
        versions.populate_quantities();
      }
      quantities = "";
      $.ajax({url:root+'/api/project/quantities/'+this.projectId, success: function(data) {
        $.each(data, function(index, element){
          quantities = quantities + "<option>"+element.quantity+"</option>";
        });
        $("#imposer_quantities").html(quantities);
      }, dataType: 'json'});
    }

    this.draw();
  },

  hide_steps: function() {
    $(".step_container").each(function(index, element){
      $(this).hide();
    });
    $("#current_step-loading").show();
  },

  step_functions: function(step) {
    if (typeof(window["init_"+step.action]) == "function"){
      window["init_"+step.action](this);
    }
  },

  add_prerequisite: function(stepAction, prerequisite) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == stepAction){
        ptr.prerequisites[prerequisite] = "true";
      }
    }
    this.update_statuses();
    this.draw();
  },

  remove_prerequisite: function(stepAction, prerequisite) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == stepAction){
        ptr.prerequisites[prerequisite] = "false";
      }
    }
    this.update_statuses();
    this.draw();
  },

  update_functions: function() {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      var actionParts = ptr.action.split("_");
      if (actionParts[0] == "custom"){
        if (typeof(update_customs) == "function"){
            update_customs();
        }
        return;
      }
      if (typeof(window["update_"+ptr.action]) == "function"){
        window["update_"+ptr.action](this);
      }
    }
  },

  is_step_before: function(target, step) {
    var target_index = 0;
    var step_index = 0;
    var cur_index = 0;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.action == target){
        target_index = cur_index;
      }
      if (ptr.action == step){
        step_index = cur_index;
      }
      cur_index++;
    }
    if (step_index < target_index) {
      return true;
    }
    return false;
  },

  show_step: function(stepAction) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr.el.div){
        ptr.el.div.hide();
      }

      if(ptr.action == stepAction){
        ptr.el.div.show();
      }
    }
  },

  update_docket_step: function() {
    if (this.find_step("projectDocket") != false){
      for (var ptr = this.head; ptr != null; ptr = ptr.next){
        if (ptr != this.tail.previous && ptr != this.tail && ptr.completed != true){
          this.tail.previous.completed = false;
          this.tail.previous.disabled = true;
          return false;
        }else if (ptr == this.tail){
          this.tail.previous.disabled = false;
        }
      }
    }
  },

  update_statuses: function() {
    var ignore = false;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (!$.isEmptyObject(ptr.prerequisites)){
        $.each(ptr.prerequisites, $.proxy(function(index, element){
          step = this.find_step(index);
          if (step != false && step.completed != true){
            ptr.disabled = true;
            ignore = true;
            return false;
          }
        }, this));
        if (!ignore){
          ptr.disabled = false;
          if (ptr.action == "projectSettings" && this.printing == false){
            ptr.disabled = true;
          }
        }
      }else{
        ptr.disabled = false;
      }
    }
    this.update_docket_step();
  },

  find_step: function(stepAction) {
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if(ptr.action == stepAction){
        return ptr;
      }
    }
    return false;
  },

  quantity_changed: function() {
    this.head.completed = false;
    for (var ptr = this.head; ptr != null; ptr = ptr.next){
      if (ptr != this.tail && ptr.action != "projectGraphicDesign" && ptr.action != "projectVariableData"){
        ptr.completed = false;
      }
    }
    this.update_statuses();
    this.draw();
  },

  stock_changed: function() {
    var settings = this.find_step("projectSettings");
    settings.completed = false;
    for (var ptr = settings.next; ptr != null; ptr = ptr.next){
      if (ptr != this.tail){
        ptr.completed = false;
      }
    }
    this.update_statuses();
    this.draw();

    if (this.find_step("projectMounting")){
      $(".mounting_stock_name option:first-child").attr('selected', 'selected');
      if ($('#mounting_stock_selector').data('plugin_cascadingDropdown') != undefined){
        $('#mounting_stock_selector').data('plugin_cascadingDropdown').update();
      }
    }
  },

  save_steps: function() {
   $.ajax({
        url: root+'/api/project/'+this.projectId+'/saveSteps',
        type: 'POST',
        dataType: 'text',
        data: null,
        async: false,
        success: $.proxy(function(data) {
          this.el.body.append("<div class='step_container' id='current_step-"+step.action+"'></div>");
          step.assignDiv($("#current_step-"+step.action));
          step.el.div.hide();
          step.el.div.html(data);
          this.show_step(step.name);
        }, this)
    });
  }

} 