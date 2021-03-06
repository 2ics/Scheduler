@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row" id="calendar-row">
  <div class="col-md-3 all-tasks">

  </div>
  <div class="col-md-9">
  <div class="row">
    <div class="col-md-12">
    <ul class="nav nav-tabs" role="tablist">
      @foreach ($processes as $index => $process)
          <li {{ (Request::is('process/'.$process->name) ? 'class="active"' : '') }}><a href="{{action('HomeController@scheduleProcess', array('process_name' => $process->name))}}">{{$process->name}}</a></li>
      @endforeach
    </ul>
    </div>
    <div class="col-md-12" style="padding-bottom: 15px;">
      <div class="col-md-12">
        <h5><b><a data-toggle="collapse" href="#non-complete-tasks">Upcoming Tasks</a></b></h5>
      </div>
      <div class="col-md-12 non-complete-tasks in" id="non-complete-tasks" style="padding: 0px;max-height: 226px; overflow-y:scroll"></div>
    </div>
    <div class="col-md-3" style="padding-right: 0px;">
      <div class="btn-group" style="width: 100%;">
        <button type="button" class="btn btn-default prev-day" style="width: 20%"><span class="glyphicon glyphicon-chevron-left" style="margin-right:0px;"></span></button>
        <button type="button" class="btn btn-default today" style="width: 60%">Today (Now)</button>
        <button type="button" class="btn btn-default next-day" style="width: 20%"><span class="glyphicon glyphicon-chevron-right" style="margin-right:0px;"></span></button>
      </div>
    </div>
    <div class="col-md-6 text-center" style="padding-left:4px;padding-right: 5px;">        
      <button type="button" class="btn btn-default refresh" style="width: 100%;"><span class="glyphicon glyphicon-refresh" style="margin-right:0px;"></span></button>
    </div>
    <div class="col-md-3">
      <div class="row">
      <div class="col-md-12" style="padding-left:0px;">
        <div class="input-group">
          <span class="input-group-addon" style="background:#fff"><span style="font-size: 14px;margin:0px;" class="glyphicon glyphicon-calendar"></span></span>
          <input type="text" class="form-control" value="{{date('d/m/Y', time())}}" id="datepicker">
        </div>
      </div>
      </div>
    </div>
    </div>
    <div id="calendar"></div>
  </div>
</div>

<div id="dialog" title="View Task">

</div>
@stop

{{-- Javascript --}}
@section('javascript')
  <script type="text/javascript">
  var $calendar = null;
  var users = Array();
  $(document).ready(function() {
    setup_calendar('{{$process_id}}');
  });

  function setup_calendar(process_id) {
    $.ajax({
        url: root+'/api/tasks/process/{{$process_id}}/equipment',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          users = data;
          $calendar = $('#calendar').weekCalendar({
            timeslotsPerHour: 4,
            scrollToHourMillis : 0,
            height: function($calendar){
              return $(window).height() - $('h1').outerHeight(true) - 200;
            },
            data: function(start, end, callback) {
              $.ajax({
                url: root+'/api/tasks/{{$process_id}}/bydate',
                type: 'POST',
                dataType: 'json',
                data: {start: start.getTime(), end: end.getTime()},
                success: function(events) {
                  callback(events);
                }
              });
            },
            users: users,
            showAsSeparateUser: true,
            displayOddEven: true,
            daysToShow: 1,
            use24Hour: false,
            headerSeparator: ' ',
            useShortDayNames: true,
            // I18N
            dateFormat: 'F d, Y'
          });
          $.datepicker.setDefaults($.datepicker.regional['en-GB']);
          $( "#datepicker" ).datepicker();
          $('#datepicker').change(function(){
            $calendar.weekCalendar("gotoDate", $(this).val());
          });
          $('.prev-day').click(function(){
            $calendar.weekCalendar("prev");
          });
          $('.next-day').click(function(){
            $calendar.weekCalendar("next");
          });
          $('.today').click(function(){
            $calendar.weekCalendar("today");
            $calendar.weekCalendar("now");
          });
          $('.refresh').click(function(){
            $calendar.weekCalendar("refresh");
          });
          populate_tasks();
        }
      });
  }

  function populate_tasks(){
    $.ajax({
      url: "{{action('TaskController@allTasks', $process_id)}}",
      type: 'GET',
      dataType: 'text',
      success: function(data) {
        $(".all-tasks").html(data);
        setup_tasks();
        $calendar.weekCalendar("refresh");
      }
    });
    $.ajax({
      url: "{{action('TaskController@nonCompleteTasks', $process_id)}}",
      type: 'GET',
      dataType: 'text',
      success: function(data) {
        $(".non-complete-tasks").html(data);
        setup_nonCompleteTasks();
        $calendar.weekCalendar("refresh");
      }
    });
  }

  function setup_nonCompleteTasks(){
    $(".non-complete-tasks").find(".non-complete-task").each(function(){
      $(this).click(function(){
          $calendar.weekCalendar("gotoDate", $(this).data('date'));
          $calendar.weekCalendar("gotoHour", $(this).data('hour'));
      });
    });
  }

  function load_task(id, title) {
    $.ajax({
      url: root+'/task/individual/'+id,
      type: 'GET',
      dataType: 'text',
      success: function(data) {
        $("#dialog").html(data);
        $( "#dialog" ).dialog({
          modal: true,
          minWidth: 500,
          draggable: false,
          title: title,
          buttons: {
            Close: function() {
              $( this ).dialog( "close" );
            }
          }
        });
      }
    });
  }

  function setup_tasks() {
    $( ".task" ).each(function(){
      $(this).data('calEvent', {userId: $(this).data('userid'), admin: $(this).data('admin'), duration: $(this).data('duration'), hasnote: $(this).data('hasnote'), colour: $(this).data('colour'), title: $(this).data('title'), id: $(this).data('id'), description: $(this).data('description')});
    });
    @if (Sentry::check() && (Sentry::getUser()->hasAccess('Super Admin') || Sentry::getUser()->hasAccess('Admin')))
    create_draggable_items();
    @endif
  }

  function create_draggable_items() {
    $( ".task" ).draggable({
      helper: function(event){
        var temp_task = $('<div class="wc-cal-event ui-corner-all" style="margin-left:-14px; width: '+$(".wc-day-column-inner").width()+';font-size: 9px;line-height: 10px;z-index: 1000;line-height: 15px; font-size: 13px; height: '+$calendar.data('weekCalendar').options.timeslotHeight * $(event.target).data('calEvent').duration+'px; display: block; background-color: rgb(170, 170, 170);"><div class="wc-time ui-corner-top" style="font-size: 9px;line-height: 10px;border: 1px solid rgb(136, 136, 136); background-color: rgb(153, 153, 153);">'+$(event.target).data('calEvent').title+'</div><div class="wc-title" style="font-size: 9px;line-height: 10px;">'+$(event.target).data('calEvent').description+'</div><div class="ui-resizable-handle ui-resizable-s">=</div></div>');
        temp_task.data('calEvent', $(event.target).data('calEvent'));
        return temp_task;
      },
      snap: ".wc-time-slot",
      snapMode: 'inner',
      snapTolerance: 59,
      scroll: false,
      appendTo: ".wc-grid-row-events .wc-full-height-column.wc-user-0",
      containment: [$(".wc-grid-row-events .wc-full-height-column.wc-user-1").offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-1").offset().top,$(".wc-grid-row-events .wc-full-height-column.wc-user-1").offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-1").offset().top+1200],
      stop: function(event, ui){
        var equipBadge = $(this).parent().parent().find('a .badge').text() - 1;
        var processBadge = $(this).parent().parent().parent().parent().find('a:first .badge').text() - 1;
        $(this).parent().parent().find('a .badge').text(equipBadge);
        $(this).parent().parent().parent().parent().find('a:first .badge').text(processBadge);
        if (processBadge == 0){
          $(this).parent().parent().parent().parent().remove();
        }
        $(this).remove();
        populate_tasks();
        $(".master-tasks").text(parseFloat($(".master-tasks").text()) - 1);
        if ($(".master-tasks").text() == 0){
          $(".master-tasks").hide();
        }
        populate_tasks();
        $calendar.weekCalendar("refresh");
      },
      drag: function(event, ui){
        $(event.target).data('draggable').containment = [$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top+1200];
      }
    });
  }

  function eventIconHandler(){
    $(".reschedule-btn").click(function(){
      $.ajax({
          url: root+'/task/reschedule/'+$(this).data('id'),
          type: 'GET',
          success: function(data) {
            $calendar.weekCalendar("refresh");
            populate_tasks();
            $(".master-tasks").text(parseFloat($(".master-tasks").text()) + 1);
            if ($(".master-tasks").text() > 0){
              $(".master-tasks").show();
            }
          }
      });
    });
    $(".edit-btn").click(function(){
      window.location.href = root+'/project/edit/'+$(this).data('project_id');
    });
    $(".view-btn").click(function(){
      load_task($(this).parent().data('calEvent').id, $(this).parent().data('calEvent').title);
    });
  }
  </script>
@stop
