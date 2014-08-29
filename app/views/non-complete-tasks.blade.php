<style>
.cursor-list .non-complete-task{
	color: #428bca;
}
.cursor-list .non-complete-task:hover{
	cursor: pointer;
}
</style>
      <ul class="list-group cursor-list">
      @foreach(Process::find($process_id)->equipment()->get() as $equipment)
        @foreach ($equipment->getAllScheduledNonComplete() as $task)
          <?php $project = Project::find($task->project_id); ?>
          <?php $customer = Customer::find($project->customer_id); ?>
          <?php date_default_timezone_set('America/Toronto'); ?>
          <li class="list-group-item non-complete-task" data-date='{{$task->start_date}}' data-hour="{{date('G', substr($task->start_date, 0, 10))+1}}">{{$equipment->name}} - {{$project->description}} - {{$project->docket}} - {{$customer->name}} - {{strtoupper($task->status)}} - {{date('l, F d, Y', substr($task->start_date, 0, 10))}} ({{date('g', substr($task->start_date, 0, 10))}}{{date('A', substr($task->start_date, 0, 10))}} - {{date('g', substr($task->end_date, 0, 10))}}{{date('A', substr($task->end_date, 0, 10))}})</li>
        @endforeach
      @endforeach
      </ul>