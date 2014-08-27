
    <div class="container">
      <div class="row">
        <div class="col-sm-3 col-md-3">
          <div class="panel-group" id="accordion">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" style="font-size: 19px;"><img src="{{asset('/img/task.png')}}" style="padding-right: 10px;" />Tasks</a>
                </h4>
              </div>
              <div id="collapseOne" class="panel-collapse collapse in">
                <ul class="list-group">
                  <?php $alltasks = 0; ?>
                  @foreach($processes as $process)
                  @if($process->getNumTasks() > 0)
                  <?php $alltasks += $process->getNumTasks(); ?>
                  <li class="list-group-item"><img src="{{asset('/img/process.png')}}" style="padding-right: 10px;"/><a data-toggle="collapse" href="#collapse{{$process->id}}" style="font-size: 19px;">{{$process->name}}<span class="badge pull-right">{{$process->getNumTasks()}}</span></a>
                    <ul class="list-group collapse {{($process->id == $process_id) ? 'in' : ''}}" id="collapse{{$process->id}}">
                      @foreach($process->equipment()->get() as $equipment)
                        @if (count($equipment->unscheduledTasks()) > 0)
                        <li class="list-group-item"><img src="{{asset('/img/equipment.png')}}" style="padding-right: 10px;"/><a data-toggle="collapse" href="#collapse{{$process->id}}{{$equipment->id}}" style="font-size: 19px;">{{$equipment->name}}<span class="badge pull-right">{{count($equipment->unscheduledTasks())}}</span></a>
                          <ul class="list-group collapse in" id="collapse{{$process->id}}{{$equipment->id}}">
                              @foreach($equipment->unscheduledTasks() as $task)
                                <li style="padding-left:0px;" class="list-group-item {{($process->id == $process_id) ? 'task' : ''}}" data-userid="{{$task->getCalendarUserId()}}" data-duration="{{$task->duration}}" data-hasnote="{{($task->notes == "") ? false : true}}" data-colour="{{User::find($task->project()->first()->user_id)->colour}}" data-title="{{$task->project()->first()->description}}" data-description="{{$task->project()->first()->docket}}<br />{{$task->project()->first()->customer()->first()->name}}<br />{{$task->notes}}<br />{{strtoupper($task->status)}}" data-id="{{$task->id}}"><img src="{{asset('/img/task.png')}}" style="padding-right: 10px;" />
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
                  @if ($alltasks == 0)
                  <li class="list-group-item">NO TASKS</li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>