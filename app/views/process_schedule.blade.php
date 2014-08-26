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
                                <li class="list-group-item task" data-userid="{{$task->getCalendarUserId()}}" data-duration="{{$task->duration}}" data-colour="{{User::find($task->project()->first()->user_id)->colour}}" data-title="{{$task->project()->first()->description}}" data-description="{{$task->project()->first()->docket}}" data-id="{{$task->id}}"><span class="glyphicon glyphicon-pencil text-primary"></span>
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

<!-- Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Reschedule Task</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to reschedule this task?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary schedule">Reschedule</button>
      </div>
    </div>
  </div>
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
      $(this).data('calEvent', {userId: $(this).data('userid'), duration: $(this).data('duration'), colour: $(this).data('colour'), title: $(this).data('title'), id: $(this).data('id'), description: $(this).data('description')});
    });
    create_draggable_items();
  }

  function create_draggable_items() {
    $( ".task" ).draggable({
      helper: function(event){
        console.log($(event.target).data('calEvent'));
        var temp_task = $('<div class="wc-cal-event ui-corner-all" style="margin-left:-14px; width: '+$(".wc-day-column-inner").width()+';z-index: 1000;line-height: 15px; font-size: 13px; height: '+$calendar.data('weekCalendar').options.timeslotHeight * $(event.target).data('calEvent').duration+'px; display: block; background-color: rgb(170, 170, 170);"><div class="wc-time ui-corner-top" style="border: 1px solid rgb(136, 136, 136); background-color: rgb(153, 153, 153);">'+$(event.target).data('calEvent').title+'</div><div class="wc-title">'+$(event.target).data('calEvent').description+'</div><button type="button" class="btn btn-link btn-sm" style="position: absolute; bottom: 5px; right:5px; width: 1px;"><span class="glyphicon glyphicon-lock"></span></button><div class="ui-resizable-handle ui-resizable-s">=</div></div>');
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
      },
      drag: function(event, ui){
        $(event.target).data('draggable').containment = [$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().left,$(".wc-grid-row-events .wc-full-height-column.wc-user-"+$(ui.helper).data('calEvent').userId).offset().top+1200];
      }
    });
  }

  function rescheduleHandler(){
    $(".reschedule-btn").click(function(){
      console.log($(this).data());
      $.ajax({
          url: root+'/task/reschedule/'+$(this).data('id'),
          type: 'GET',
          success: function(data) {
            window.location.href = "{{action('ProjectController@editor')}}";
          }
      });
    });
  }
  </script>
@stop
