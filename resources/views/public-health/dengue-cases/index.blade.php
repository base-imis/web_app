@extends('layouts.dashboard')
@section('title', 'Dengue Cases')
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

        <a href="#" class="btn btn-info">Create new Case</a>

        <a href="#" id="export" class="btn btn-info">Export to Excel</a>


        <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
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
                                        <label for="code" class="col-md-2 col-form-label">ID</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="code" />
                                        </div>
                                        <label for="sex" class="col-md-2 col-form-label ">Sex</label>
                                        <div class="col-md-2">
                                            <select class="form-control" id="sex">
                                                <option value="">All</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>

                                            </select>
                                        </div>
                                        <label for="age" class="col-md-2 col-form-label ">Age</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="age" />
                                        </div>
                                    </div>
                                    <div class="form-group row">

                                        <label for="ward" class="col-md-2 col-form-label ">Ward</label>
                                        <div class="col-md-2">
                                            <select class="form-control" id="ward">
                                                <option value="">All</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info ">Filter</button>
                                        <button type="reset" class="btn btn-info reset">Reset</button>
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

    <div class="card-body"> <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>OPD/EME/IPD</th>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Ward</th>
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
        "fnStateSave": function(oSettings, oData) {
            localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
        },
        "fnStateLoad": function(oSettings) {
            return JSON.parse(localStorage.getItem('DataTables_' + window.location.pathname));
        },
        ajax: {
            url: '{!! url("publichealth/dengue-cases/data") !!}',
            data: function(d) {
                d.id = $('#code').val();
                d.age = $('#age').val();
                d.sex = $('#sex').val();
                d.ward = $('#ward').val();
            }
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'opd_eme_ipd',
                name: 'opd_eme_ipd'
            },
            {
                data: 'patient_name',
                name: 'patient_name'
            },
            {
                data: 'age',
                name: 'age'
            },
            {
                data: 'sex',
                name: 'sex'
            },
            {
                data: 'ward',
                name: 'ward'
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
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        'Deleted!',
                        'Road will be deleted.',
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

    var id = '',
        age = '',
        sex = '',
        ward = '';

    $('#filter-form').on('submit', function(e) {

        e.preventDefault();
        dataTable.draw();
        id = $('#code').val();
        age = $('#age').val();
        sex = $('#sex').val();
        ward = $('#ward').val();
    });

    //  $('#data-table_filter input[type=search]').attr('readonly', 'readonly');

    $("#export").on("click", function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        window.location.href = "{!! url('publichealth/roadlines/export?searchData=') !!}" + searchData +
            "&code=" + code + "&hierarchy=" + hierarchy + "&surface_type=" + surface_type;
    })

    $("#export-shp").on("click", function(e) {
        e.preventDefault();
        var cql_param = getCQLParams();
        window.location.href = "{{ Config::get("
        constants
            .GEOSERVER_URL ") }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get("
        constants.AUTH_KEY ") }}&typeName={{ Config::get("
        constants.GEOSERVER_WORKSPACE ") }}:roadlines_layer+&CQL_FILTER=" + cql_param +
            " &outputFormat=SHAPE-ZIP";

    })

    $("#export-kml").on("click", function(e) {
        e.preventDefault();
        var cql_param = getCQLParams();

        window.location.href = "{{ Config::get("
        constants
            .GEOSERVER_URL ") }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get("
        constants.AUTH_KEY ") }}&typeName={{ Config::get("
        constants.GEOSERVER_WORKSPACE ") }}:roadlines_layer+&CQL_FILTER=" + cql_param +
            " &outputFormat=KML";
    });

    function getCQLParams() {
        code = $('#code_text').val();
        hierarchy = $('#road_hier_select').val();
        surface_type = $('#surface_type').val();

        var cql_param = "1=1 AND deleted_at IS NULL";
        if (code) {
            cql_param += " AND code ='" + code + "'";
        }
        if (hierarchy) {
            cql_param += " AND hierarchy ='" + hierarchy + "'";
        }
        if (surface_type) {
            cql_param += " AND surface_type ='" + surface_type + "'";
        }

        return encodeURI(cql_param);
    }

    $(".reset").on("click", function(e) {
        $('#code').val('');
        $('#age').val('');
        $('#sex').val('');
        $('#ward').val('');
        $('#data-table').dataTable().fnDraw();
        localStorage.removeItem('DataTables_' + window.location.pathname);
        localStorage.clear();
        // window.location.reload();
    })

    setTimeout(function() {
        localStorage.clear();
    }, 60 * 60 * 1000); ///for 1 hour
});
</script>

@endpush