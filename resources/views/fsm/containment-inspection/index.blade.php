@extends('layouts.dashboard')
@push('style')
<style type="text/css">
.dataTables_filter {
  display: none;
}
</style>
@endpush
@section('title', $page_title)
@section('content')
<div class="card">
<div class="card-header">
<a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Show Filter
            </a>
</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                    <form class="form-horizontal" id="filter-form">
                                        
                                        <div class="form-group row">
                                            <label for="ebps_id" class="col-md-2 col-form-label ">eBPS Id</label>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control" id="ebps_id" placeholder=""/>
                                                </div>
                                    
                                     
                                        <label for="compliance_status" class="control-label col-md-2">Compliance Status</label>
                                            <div class="col-md-2">
                                                <select class="form-control" id="compliance_status">
                                                    <option value="">All</option>
                                                    <option value="true">Compliant</option>
                                                    <option value="false">Not Compliant</option>
                                                </select>
                                            </div>
                                            </div>
                                        
                                      <div class="card-footer text-right">
                                          <button type="submit" class="btn btn-info">Filter</button>
                                          <button type="reset" id="reset-filter" class="btn btn-info">Reset</button>
                                      </div>
                                    </form>
                                    </div>  <!--- accordion body!-->
                                </div>    <!--- collapseOne!-->
                            </div>      <!--- accordion item!-->
                        </div>        <!--- accordion !-->
                    </div>            <!---col!-->
                </div>            <!--- row !-->
        </div>  
        <div class="card-body"> 
    <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
            <thead>
        <tr>
            <th>eBPS Id</th>
            <th>Compliance Status</th>
            <th>Actions</th>
        </tr>
        </thead>
    </table>
</div>
</div>
</div>
@stop

@push('scripts')
<script>
$(function() {
  var dataTable = $('#data-table').DataTable({
  bFilter: false,
  processing: true,
  serverSide: true,
  scrollCollapse: true,
  ajax: {
    url: '{!! url("fsm/containment-inspection/data") !!}',
    data: function(d) {
                d.ebps_id = $('#ebps_id').val();
                d.compliance_status = $('#compliance_status').val();
    }
  },
  columns: [
    { data: 'ebps_id', name: 'ebps_id' },
    { data: 'compliance_status', name: 'compliance_status' },
    { data: 'action', name: 'action', orderable: false, searchable: false }
  ]
})

    var id = '';
$('#filter-form').on('submit', function(e){
      e.preventDefault();
      dataTable.draw();
      id = $('#ebps_id').val();
      
    });
    resetDataTable(dataTable);


});


</script>
@endpush