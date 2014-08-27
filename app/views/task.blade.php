<div class="row">
  <div class="col-md-12">
  <h3 style="margin:0px;">Project Specs</h3>
  {{$project->docket}}<br />
  {{$project->customer()->first()->name}}<br />
  {{$project->sheets}} Sheets<br />
  {{($project->stock != "") ? "Stock: ".$project->stock."<br />" : ""}}
  {{($project->notes != "") ? "Notes: ".$project->notes."<br />" : ""}}
  <h3 style="margin:0px;">Task</h3>
  {{Process::find($task->process_id)->name}} - {{ProcessEquipment::find($task->equipment_id)->name}}<br />
  {{$task->duration}} {{($task->duration > 1) ? "hours" : "hour"}}<br />
  {{($task->notes != "") ? "Notes: ".$task->notes."<br />" : ""}}
  {{$task->status}}<br />
  </div>
</div>
