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
                    <ul class="list-group collapse {{($process->id == $process_id) ? 'in' : ''}}" id="collapse{{$process->id}}">
                      @foreach($process->equipment()->get() as $equipment)
                        @if (count($equipment->unscheduledTasks()) > 0)
                        <li class="list-group-item"><span class="glyphicon glyphicon-pencil text-primary"></span><a data-toggle="collapse" href="#collapse{{$process->id}}{{$equipment->id}}">{{$equipment->name}}<span class="badge pull-right">{{count($equipment->unscheduledTasks())}}</span></a>
                          <ul class="list-group collapse in" id="collapse{{$process->id}}{{$equipment->id}}">
                              @foreach($equipment->unscheduledTasks() as $task)
                                <li class="list-group-item {{($process->id == $process_id) ? 'task' : ''}}" data-userid="{{$task->getCalendarUserId()}}" data-duration="{{$task->duration}}" data-colour="{{User::find($task->project()->first()->user_id)->colour}}" data-title="{{$task->project()->first()->description}}" data-description="{{$task->project()->first()->docket}}<br />{{$task->project()->first()->customer()->first()->name}}<br />{{$task->notes}}<br />{{$task->status}}" data-id="{{$task->id}}"><span class="glyphicon glyphicon-pencil text-primary"></span>
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
  <div class="row">
    <div class="col-md-9">
    <ul class="nav nav-tabs" role="tablist">
      @foreach ($processes as $index => $process)
          <li {{ (Request::is('process/'.$process->name) ? 'class="active"' : '') }}><a href="{{action('HomeController@scheduleProcess', array('process_name' => $process->name))}}">{{$process->name}}</a></li>
      @endforeach
    </ul>
    </div>
    <div class="col-md-3">
      <div class="row">
      <div class="col-md-2" style="padding:0px;text-align:center;">
        <span style="font-size: 20px;margin:0px;" class="glyphicon glyphicon-calendar"></span>
      </div>
      <div class="col-md-10" style="padding-left:0px;">
        <input type="text" class="form-control" value="{{date('d/m/Y', time())}}" id="datepicker">
      </div>
      </div>
    </div>
    </div>
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
          $.datepicker.setDefaults($.datepicker.regional['en-GB']);
          $( "#datepicker" ).datepicker();
          $('#datepicker').change(function(){
            $calendar.weekCalendar("gotoDate", $(this).val());
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
          console.log($(this).parent().parent().parent().parent().remove());
        }
        $(this).remove();
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
            window.location.href = "{{action('ProjectController@editor')}}";
          }
      });
    });
    $(".edit-btn").click(function(){
      window.location.href = root+'/project/edit/'+$(this).data('project_id');
    });
  }
  </script>
@stop
