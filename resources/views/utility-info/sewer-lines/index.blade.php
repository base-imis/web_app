<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
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
    @can('Export Sewers to Excel')
    <a href="{{ action('UtilityInfo\SewerLineController@export') }}" id="export" class="btn btn-info">Export to CSV</a>
    @endcan
    @can('Export Sewers to Shape')
    <a href="#" id="export-shp" class="btn btn-info">Export to Shape File</a>
    @endcan
    @can('Export Sewers to KML')
    <a href="#" id="export-kml" class="btn btn-info">Export to KML</a>
    @endcan
    <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
      Show Filter
    </a>
  </div><!-- /.box-header -->
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <form class="form-horizontal" id="filter-form">
                  <div class="form-group row">
                    <label for="code" class="col-md-2 col-form-label ">Code</label>
                    <div class="col-md-2">
                      <input type="text" class="form-control" id="code" placeholder="Code" />
                    </div>
                    <label for="road_code" class="col-md-2 col-form-label ">Road Code</label>
                    <div class="col-md-2">
                      <input type="text" class="form-control" id="road_code" placeholder="Road Code" />
                    </div>
                    <label for="location" class="col-md-2 col-form-label ">Location</label>
                    <div class="col-md-2">
                      <select class="form-control" id="location">
                        <option value="">Location</option>
                        @foreach($location as $key)
                        <option value="{{$key}}">{{$key}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="card-footer text-right">
                    <button type="submit" class="btn btn-info ">Filter</button>
                    <button id="reset-filter" type="reset" class="btn btn-info">Reset</button>
                  </div>
                </form>
              </div> <!--- accordion body!-->
            </div> <!--- collapseOne!-->
          </div> <!--- accordion item!-->
        </div> <!--- accordion !-->
      </div>
    </div> <!--- row !-->
  </div> <!--- card body !-->

  <div class="card-body">
    <div style="overflow: auto; width: 100%;">
      <table id="data-table" class="table table-bordered table-striped dataTable dtr-inline" width="100%">
        <thead>
          <tr>
            <th>Code</th>
            <th>Road Code</th>
            <th>Location</th>
            <th>Length (m)</th>
            <th>Diameter (mm)</th>
            <th>Treatment Plant</th>
            <th>Actions</th>
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
        url: '{!! url("utilityinfo/sewerlines/data") !!}',
        data: function(d) {
          d.code = $('#code').val();
          d.location = $('#location').val();
          d.road_code = $('#road_code').val();
        }
      },
      columns: [{
          data: 'code',
          name: 'code'
        },
        {
          data: 'road_code',
          name: 'road_code'
        },
        {
          data: 'location',
          name: 'location'
        },
        {
          data: 'length',
          name: 'length'
        },
        {
          data: 'diameter',
          name: 'diameter'
        },
        {
          data: 'treatment_plant_id',
          name: 'treatment_plant_id'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false
        }
      ],
      order: [
        [0, 'desc']
      ]
    }).on('draw', function() {
      $('.delete').on('click', function(e) {
        var form = $(this).closest("form");
        event.preventDefault();
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!  ",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire(
              'Deleted!',
              'Sewer Line will be deleted.',
              'success'
            ).then((willDelete) => {
              if (willDelete) {
                form.submit();
              }
            })
          }
        })

      });
    });

    var code = '',
      location = '',
      road_code = '';

    $('#filter-form').on('submit', function(e) {
      var sewerLineR = $('#code').val();
      sewerLineR = sewerLineR.trim();
      var validR = /^S\d+$/gi.test(sewerLineR);
      if (sewerLineR != '') {
        if (!validR || (sewerLineR.length != 7)) {
          Swal.fire({
            title: `Sewer Line code should be in SXXXXXX format!`,
            icon: "warning",
            button: "Close",
            className: "custom-swal",
          })
          return false;
        }
      }
      e.preventDefault();
      dataTable.draw();
      code = $('#code').val();
      location = $('#location').val();
      road_code = $('#road_code').val();
    });

    //  $('#data-table_filter input[type=search]').attr('readonly', 'readonly');
    filterDataTable(dataTable);
    resetDataTable(dataTable);
    $("#export").on("click", function(e) {
      e.preventDefault();
      var searchData = $('input[type=search]').val();
      var code = $('#code').val();
      var location = $('#location').val();
      var road_code = $('#road_code').val();
      window.location.href = "{!! url('utilityinfo/sewerlines/export?searchData=') !!}" + searchData + "&code=" + code + "&location=" + location + "&road_code=" + road_code;
    })

    $("#export-shp").on("click", function(e) {
      e.preventDefault();
      var cql_param = getCQLParams();
      window.location.href = "{{ Config::get("constants.GEOSERVER_URL") }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get("constants.AUTH_KEY") }}&typeName={{ Config::get("constants.GEOSERVER_WORKSPACE") }}:sewerlines_layer+&CQL_FILTER=" + cql_param + " &outputFormat=SHAPE-ZIP";
      
    });

    $("#export-kml").on("click", function(e) {
      e.preventDefault();
      var cql_param = getCQLParams();
      window.location.href = "{{ Config::get("constants.GEOSERVER_URL") }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get("constants.AUTH_KEY") }}&typeName={{ Config::get("constants.GEOSERVER_WORKSPACE") }}:sewerlines_layer+&CQL_FILTER=" + cql_param +" &outputFormat=KML";

    });

    function getCQLParams() {
      code = $('#code').val();

      location = $('#location').val();
      var cql_param = "1=1 AND deleted_at IS NULL";
      if (code) {
        cql_param += " AND code ='" + code + "'";
      }
      if (road_code) {
        cql_param += " AND road_code ='" + road_code + "'";
      }
      if (location) {
        cql_param += " AND location ='" + location + "'";
      }
      return encodeURI(cql_param);
    }


  });
</script>
@endpush