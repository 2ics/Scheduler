<style>
.indv-task tbody{
  .tr .td{
    padding-right:5px;
  }
}
</style>
<div class="row" style="font-family: helvetica;">
  <div class="col-md-12">
  <h3 style="margin:0px;padding-bottom:5px;"><b>Project Specs</b></h3>
  <table class="indv-task">
    <tr>
      <td><b>Docket:</b></td>
      <td>{{$project->docket}}</td>
    </tr>
    <tr>
      <td><b>Customer:</b></td>
      <td>{{$project->customer()->first()->name}}</td>
    </tr>
    <tr>
      <td><b>Sheets:</b></td>
      <td>{{$project->sheets}}</td>
    </tr>
    @if ($project->stock != "")
    <tr>
      <td><b>Stock:</b></td>
      <td>{{$project->stock}}</td>
    </tr>
    @endif
    @if ($project->notes != "")
    <tr>
      <td><b>Notes:</b></td>
      <td>{{$project->notes}}</td>
    </tr>
    @endif
  </table>
  <h3 style="margin:0px;padding:5px 0px;"><b>Task</b></h3>
  <table class="indv-task">
    <tr>
      <td><b>Process:</b></td>
      <td>{{Process::find(ProcessEquipment::find($task->equipment_id)->process_id)->name}}</td>
    </tr>
    <tr>
      <td><b>Equipment:</b></td>
      <td>{{ProcessEquipment::find($task->equipment_id)->name}}</td>
    </tr>
    <tr>
      <td><b>Duration:</b></td>
      <td>{{$task->duration}} {{($task->duration > 1) ? "hours" : "hour"}}</td>
    </tr>
    @if ($task->notes != "")
    <tr>
      <td><b>Notes:</b></td>
      <td>{{$task->notes}}</td>
    </tr>
    @endif
    <tr>
      <td><b>Status:</b></td>
      <td>
        <select class="form-control status" id="status" placeholder="Select Status">
          <option value="pending" {{($task->status == "pending") ? "selected" : ""}}>Pending</option>
          <option value="approved" {{($task->status == "approved") ? "selected" : ""}}>Approved</option>
          <option value="in-progress" {{($task->status == "in-progress") ? "selected" : ""}}>In Progress</option>
          <option value="complete" {{($task->status == "complete") ? "selected" : ""}}>Complete</option>
        </select>
      </td>
    </tr>
  </table>
  </div>
</div>

<script>
$(".status").change(function(){
  var self = $(this);
  $.ajax({
      url: "{{action('TaskController@changeStatus')}}",
      type: 'POST',
      data: {'id': "{{$task->id}}", "status": self.val()},
      success: function(data) {
        $calendar.weekCalendar('refresh');
      }
  });
});
</script>
