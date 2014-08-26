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
<div class="container">
      <div class="row">
        <div class="col-sm-3 col-md-3">
          <div class="panel-group" id="accordion">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><span class="glyphicon glyphicon-folder-close">
                    </span>Tasks</a>
                </h4>
              </div>
              <div id="collapseOne" class="panel-collapse collapse in">
                <ul class="list-group">
                  @foreach($processes as $process)
                  @if($process->getNumTasks() > 0)
                  <li class="list-group-item"><span class="glyphicon glyphicon-pencil text-primary"></span><a data-toggle="collapse" href="#collapse{{$process->id}}">{{$process->name}}<span class="badge pull-right">{{$process->getNumTasks()}}</span></a>
                    <ul class="list-group collapse in" id="collapse{{$process->id}}">
                      @foreach($process->equipment()->get() as $equipment)
                        @if (count($equipment->unscheduledTasks()) > 0)
                        <li class="list-group-item"><span class="glyphicon glyphicon-pencil text-primary"></span><a data-toggle="collapse" href="#collapse{{$process->id}}{{$equipment->id}}">{{$equipment->name}}<span class="badge pull-right">{{count($equipment->unscheduledTasks())}}</span></a>
                          <ul class="list-group collapse in" id="collapse{{$process->id}}{{$equipment->id}}">
                              @foreach($equipment->unscheduledTasks() as $task)
                                <li class="list-group-item task" data-userid="{{$task->getCalendarUserId()}}" data-colour="{{User::find($task->project()->first()->user_id)->colour}}" data-title="{{$task->project()->first()->description}}" data-id="{{$task->id}}"><span class="glyphicon glyphicon-pencil text-primary"></span>
                                    {{Project::find($task->project_id)->description}} - {{Project::find($task->project_id)->docket}} - {{Customer::find(Project::find($task->project_id)->customer_id)->name}}
                                </li>
                              @endforeach
                          </ul>
                        </li>
                        @endif
                      @endforeach 
                    </ul>
                  </li>
                  @endif
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <ul class="nav nav-tabs" role="tablist">
      @foreach ($processes as $index => $process)
          <li {{ (Request::is('process/'.$process->name) ? 'class="active"' : '') }}><a href="{{action('HomeController@scheduleProcess', array('process_name' => $process->name))}}">{{$process->name}}</a></li>
      @endforeach
    </ul>
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
          setup_tasks();
        }
      });
  }

  function setup_tasks() {
    $( ".task" ).each(function(){
      $(this).data('calEvent', {userId: $(this).data('userid'), colour: $(this).data('colour'), title: $(this).data('title'), id: $(this).data('id')});
    });
    create_draggable_items();
  }

  function create_draggable_items() {
    $( ".task" ).draggable({
      helper: function(event){
        console.log($(event.target).data('calEvent'));
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
        console.log(ui);
        // console.log($(event.target).data('draggable'));
        // function update_task_count();
      },
      drag: function(event, ui){
        // console.log($(ui.helper).data('calEvent').userId);
        $(event.target).data('draggable').containment = [$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top+1200];
      }
    });
  }

  // function update_task_count() {

  // }
  </script>
@stop
