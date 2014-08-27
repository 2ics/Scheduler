@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
  	<div class="col-md-12">
		<table id="table_id" class="display">
		    <thead>
		        <tr>
		            <th>Description</th>
		            <th>Docket</th>
                <th>Customer</th>
                <th>Sheets</th>
                <th>Stock</th>
		            <th>Rep</th>
		            <th>Input Date</th>
		            <th>Due Date</th>
		            <th>Completion Time</th>
		            <th>Days Left</th>
		            <th>Scheduled?</th>
		            <th>Total tasks</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Modify</th>
		        </tr>
		    </thead>
		</table>
  	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete Project</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to remove this project permanently?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary delete">Delete</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Schedule Project</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to add this project to the scheduler?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary schedule">Add to Schedule</button>
      </div>
    </div>
  </div>
</div>
@stop

{{-- Javascript --}}
@section('javascript')
<script>
    t = $('#table_id').DataTable( {
      "dom": 'TCR<"clear">lfrtip',
	  "pageLength": 10,
      "ajax": { 
      	'url': root+"/project/getAll"
    	},
      "columns": [
          { "data": "description"},
          { "data": "docket", },
          { "data": "customer", },
          { "data": "sheets", },
          { "data": "stock", },
          { "data": "rep", },
          { "data": "input_date", },
          { "data": "due_date", },
          { "data": "completion_time", },
          { "data": "overdue", },
          { "data": "scheduled", },
          { "data": "total_tasks", },
          { "data": "status", },
          { "data": "notes", },
          { "data": "modify", "width": '60px'}
      ],
      "tableTools": {
          "sSwfPath": "{{ asset('packages/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
          "sRowSelect": "multi",
          "aButtons": [
              {
                  "sExtends":    "collection",
                  "bSelectedOnly": true,
                  "sButtonText": "Export",
                  "aButtons": [ 
                      {
                          "sExtends": "csv",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {
                          "sExtends": "xls",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {
                          "sExtends": "pdf",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {   
                          "sExtends": "print",
                          "sButtonText": "Printer Friendly",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      }
                  ]
              },
              'select_all', 'select_none'
          ]
      }
  } );
$('#myModal').on('shown.bs.modal', function (e) {
  var self = $(this);
  $(this).find('.delete').click(function(){
    $.ajax({
        url: root+'/project/delete/'+$(e.relatedTarget).data('project'),
        type: 'GET',
        success: function(data) {
          window.location.href = "{{action('ProjectController@editor')}}";
        }
    });
  });
});
$('#myModal').on('hidden.bs.modal', function (e) {
  $(this).find('.delete').unbind('click');
});
$('#scheduleModal').on('shown.bs.modal', function (e) {
  var self = $(this);
  $(this).find('.schedule').click(function(){
    $.ajax({
        url: root+'/project/schedule/'+$(e.relatedTarget).data('project'),
        type: 'GET',
        success: function(data) {
          window.location.href = "{{action('ProjectController@editor')}}";
        }
    });
  });
});
$('#scheduleModal').on('hidden.bs.modal', function (e) {
  $(this).find('.schedule').unbind('click');
});
</script>
@stop
