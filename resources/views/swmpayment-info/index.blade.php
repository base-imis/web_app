@extends('layouts.dashboard')
@section('title', $page_title)
@push('style')
<style type="text/css">
.dataTables_filter {
    display: none;
}
</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header">
    @can('Import Service Payment')
      <a href="{{ route('swm-payment.create') }}" class="btn btn-info">Import to CSV </a>
    @endcan
    @can('Download Service Payment Template')
    <a href="/templates/swmservice-payment-collection-iss-template.csv" download="Solid Waste Information Support System-Template.csv" class="btn btn-info">Download CSV Template</a>
    @endcan
    @can('Export Service Payment')
      <a href="{{ route('swm-payment.export') }}" id="export" class="btn btn-info">Export to CSV </a>
      <a href="{{ route('tax-payment.exportunmatched') }}" id="exportunmatched" class="btn btn-info">Export Unmatched Records</a>
      @endcan
      <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Show Filter
      </a>
    </div><!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="form-horizontal" id="filter-form">
                                    <div class="form-group row">
                                        <label for="code" class="col-md-0 col-form-label "
                                            style="margin-left:5%"> Wards</label>
                                        <div class="col-md-2" style="margin-left:5%">
                                        <select class="form-control" id="ward_select">
                                        <option value=""> Wards</option>
                                        @foreach($wards as $key=>$value)
                                        <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                      </select>
                                        </div>
                                        <label for="road_hier_select" class="col-md-0 col-form-label "
                                            style="margin-left:5%">Years Due</label>
                                        <div class="col-md-2" style="margin-left:5%">
                                        <select class="form-control" id="dueyear_select">
                                        <option value="">Years Due</option>
                                          @foreach($dueYears as $key=>$value)
                                          <option value="{{$key}}">{{$value}}</option>
                                          @endforeach
                                    </select>
                                        </div>
                                       
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info ">Filter</button>
                                        <button id="reset-filter" type="reset" class="btn btn-info">Reset</button>
                                    </div>
                                </form>
                            </div>
                            <!--- accordion body!-->
                        </div>
                        <!--- collapseOne!-->
                    </div>
                    <!--- accordion item!-->
                </div>
                <!--- accordion !-->
            </div>
        </div>
        <!--- row !-->
    </div>
    <!--- card body !-->

    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                    <th>SWM Customer ID</th>
                    <th>BIN</th>
                    <th>Tax Code</th>
                    <th>Customer Name</th>
                    <th>Years Due</th>
                    <th>Ward Number</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        "bStateSave": true,
        "stateDuration": 1800, // In seconds; keep state for half an hour

        ajax: {
          url: '{!! url("swm-payment/data") !!}',
            data: function(d) {
              d.ward_select = $('#ward_select').val();
            d.dueyear_select = $('#dueyear_select').val();
            }
        },
        columns: [
            { data: 'swm_customer_id', name: 'swm_customer_id' },
            { data: 'bin', name: 'bin' },
            { data: 'tax_code', name: 'tax_code' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'name', name: 'name' },
            { data: 'ward', name: 'ward' }
        ]
    }).on('draw', function() {
      $('.delete').on('click', function(e) {

      var form =  $(this).closest("form");
      event.preventDefault();
      swal({
          title: `Are you sure you want to delete this record?`,
          text: "If you delete this, it will be gone forever.",
          icon: "warning",
          buttons: true,
          dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          form.submit();
        }
      })
      });
    });

    var ward_select = '', dueyear_select = '';


    $('#filter-form').on('submit', function(e) {

        e.preventDefault();
        dataTable.draw();
        ward_select = $('#ward_select').val();
      dueyear_select = $('#dueyear_select').val();
    });
    filterDataTable(dataTable);
    resetDataTable(dataTable);
    //  $('#data-table_filter input[type=search]').attr('readonly', 'readonly');

    $("#export").on("click", function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        var  ward_select = $('#ward_select').val();
        var dueyear_select = $('#dueyear_select').val();
        window.location.href = "{!! url('swm-payment/export?searchData=') !!}"+searchData+"&ward="+ward_select+"&due_year="+encodeURIComponent(dueyear_select);
    });

    $("#exportunmatched").on("click", function(e) {
        e.preventDefault();
        window.location.href = "{!! url('swm-payment/exportunmatched') !!}";
    });



});
</script>

@endpush
