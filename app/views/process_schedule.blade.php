@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row" id="calendar-row">
  <div class="col-md-3">
	<div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a>TASKS</a>
            </h4>
          </div>
          <div class="panel-collapse">
            <ul class="list-group">
              <li class="list-group-item"><a data-toggle="collapse" href="#collapseTasks">Tasks</a><span class="badge">4</span>
                <ul class="list-group in" id="collapseTasks">
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
  </div>
  <div class="col-md-9">
    <div id="calendar"></div>
  </div>
</div>

@stop

{{-- Javascript --}}
@section('javascript')
  <script type="text/javascript">
  (function($) {
    users = Array();
    $(document).ready(function() {
      setup_tasks();
      setup_calendar('{{$process_id}}');
    });
  })(jQuery);

  function setup_calendar(process_id) {
    $.ajax({
        url: root+'/api/tasks/process/{{$process_id}}/equipment',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          users = data;
          var $calendar = $('#calendar').weekCalendar({
            timeslotsPerHour: 4,
            scrollToHourMillis : 0,
            height: function($calendar){
              return $(window).height() - $('h1').outerHeight(true) - 50;
            },
            data: function(start, end, callback) {
              $.ajax({
                url: root+'/api/tasks/bydate',
                type: 'POST',
                dataType: 'json',
                data: {start: start.getTime(), end: end.getTime()},
                success: function(events) {
                  console.log(events);
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
            dateFormat: 'F d, y'
          });
        }
      });
  }

  function setup_tasks() {
    $.ajax({
      url: root+'/api/tasks/unscheduledtasks',
      type: 'GET',
      dataType: 'json',
      success: function(events) {
        if ($.isEmptyObject(events)){
          $("#collapseTasks").append('<li class="list-group-item empty-item">Empty</li>');
        }
        $.each(events, function(index, element){
          var task = $('<li class="list-group-item" id="task_'+element.id+'">'+element.description+'</li>');
          task.data('calEvent', {userId: element.userId, title: element.description, id: element.id});
          $("#collapseTasks").append(task);
          create_draggable_items();
        });
      }
    });
  }

  function create_draggable_items() {
    $( ".list-group-item" ).draggable({
      helper: function(event){
        var temp_task = $('<div class="wc-cal-event ui-corner-all" style="margin-left:2px; width: '+$(".wc-day-column-inner").width()+';z-index: 1000;line-height: 15px; font-size: 13px; height: 60px; display: block; background-color: rgb(170, 170, 170);"><div class="wc-time ui-corner-top" style="border: 1px solid rgb(136, 136, 136); background-color: rgb(153, 153, 153);">'+$(event.target).data('calEvent').title+'<div class="wc-cal-event-delete ui-icon ui-icon-close"></div></div><div class="wc-title">'+$(event.target).data('calEvent').title+'</div><div class="ui-resizable-handle ui-resizable-s">=</div></div>');
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
        console.log($(event.target).data('draggable'));
        $calendar.data('weekCalendar').save();
      },
      drag: function(event, ui){
        console.log($(ui.helper).data('calEvent').userId);
        $(event.target).data('draggable').containment = [$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top+1200];
      }
    });
  }
  </script>
@stop
