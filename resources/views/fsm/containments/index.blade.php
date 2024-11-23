<!-- Last Modified Date: 10-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->

@extends('layouts.dashboard')
@section('title', $page_title)


@section('content')

    <div class="card border-0">
        <div class="card-header">

            <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne"
                aria-expanded="true" aria-controls="collapseOne">
                Show Filter
            </a>
            @can('Export Containments to Excel')
                <a href="#{{-- action('Fsm\ContainmentController@export') --}}" id="export" class="btn btn-info">Export to CSV</a>
            @endcan
            @can('Export Containments to Shape')
                <a href="#" id="export-shp" class="btn btn-info">Export to Shape File</a>
            @endcan
            @can('Export Containments to KML')
                <a href="#" id="export-kml" class="btn btn-info">Export to KML</a>
            @endcan
        </div><!-- /.card-header -->
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
                                            <label for="containment_id" class="control-label col-md-2">Containment
                                                ID</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="containment_id"
                                                    placeholder="Containment ID" />
                                            </div>

                                            <label for="containment_volume" class="control-label col-md-2">Containment
                                                Volume
                                                (m³)</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="containment_volume"
                                                    placeholder="Containment Volume (m³)" />
                                            </div>

                                            <label for="bin" class="control-label col-md-2">BIN of Connected
                                                Building</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="bin"
                                                    placeholder="BIN of Connected Building" />
                                            </div>

                                        </div>
                                        <div class="form-group row">
                                            <label for="containment_location" class="control-label col-md-2">Containment
                                                Location</label>
                                            <div class="col-md-2">
                                                <select class="form-control" id="containment_location">
                                                    <option value="">Containment Location</option>
                                                    @foreach ($containmentLocations as $key => $value)
                                                        <option value="{{ $value }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <label for="
                                            "
                                                class="control-label col-md-2">Containment
                                                Type</label>
                                            <div class="col-md-2">
                                                <select class="form-control" id="type_id">
                                                    <option value="">Containment Type</option>
                                                    @foreach ($containmentTypes as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <label for="emptying_status" class="control-label col-md-2">Emptying
                                                Status</label>
                                            <div class="col-md-2">
                                                <select class="form-control" id="emptying_status">
                                                    <option value="">Emptying Status</option>
                                                    <option value="TRUE">Yes</option>
                                                    <option value="FALSE">No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="septic_compliance" class="control-label col-md-2">Septic Tank
                                                Standard Compliance </label>
                                            <div class="col-md-2">
                                                <select class="form-control" id="septic_compliance">
                                                    <option value="">Septic Tank Standard Compliance</option>
                                                    <option value="true">Yes</option>
                                                    <option value="false">No</option>
                                                </select>
                                            </div>
                                            <label for="const_date" class="control-label col-md-2">Date</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="const_date"
                                                    placeholder="Date" />
                                            </div>

                                            <label for="house_number" class="control-label col-md-2">House Number of
                                                Connected Building</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="house_number"
                                                    placeholder="House Number of Connected Building" />
                                            </div>
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-info ">Filter</button>
                                            <button type="reset" id="reset-filter"
                                                class="btn btn-info reset">Reset</button>
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
                            <th>Containment ID</th>
                            <th>Containment Type</th>
                            <th>Containment Volume (m³)</th>
                            <th>Containment Location</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div> <!-- /.card -->

@stop

@push('scripts')
    <script>
        $.fn.dataTable.ext.errMode = 'throw';
        $(function() {

            var containment_id = '';
            var type_id = '';
            var containment_volume = '';
            var containment_location = '';
            var emptying_status = '';
            var septic_compliance = '';
            var bin = '';
            var house_number = '';

            // var roadcd = '';

            var dataTable = $('#data-table').DataTable({
                bFilter: false,
                processing: true,
                processing: true,
                serverSide: true,
                scrollCollapse: true,
                stateSave: false,
                "stateDuration": 1800, // In seconds; keep state for half an hour

                ajax: {
                    url: '{!! url('fsm/containments/data') !!}',
                    data: function(d) {
                        d.containment_id = $('#containment_id').val();
                        d.type_id = $('#type_id').val();
                        d.containment_volume = $('#containment_volume').val();
                        d.containment_location = $('#containment_location').val();
                        d.emptying_status = $('#emptying_status').val();
                        d.septic_compliance = $('#septic_compliance').val();
                        d.bin = $('#bin').val();
                        d.house_number = $('#house_number').val();

                        d.const_date = $('#const_date').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'type_id',
                        name: 'type_id'
                    },
                    {
                        data: 'size',
                        name: 'size'
                    },
                    {
                        data: 'location',
                        name: 'location'
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
                            form.submit();
                            Swal.fire(
                                'Deleted!',
                                'Containment will be deleted.',
                                'Success'
                            )
                        }
                    })
                });
            });

            var containment_id = '',
                type_id = '',
                containment_volume = '',
                containment_location = '',
                emptying_status = '';
            septic_compliance = '',
                bin = '',
                house_number = '',
                const_date = '',
                $('#filter-form').on('submit', function(e) {
                    var containC = $('#containment_id').val();
                    containC = containC.trim();
                    var validC = /^C\d+$/gi.test(containC);
                    if (containC != '') {
                        if (!validC || (containC.length != 7)) {
                            Swal.fire({
                                title: `Containment ID should be in CXXXXXX format!`,
                                icon: "warning",
                                button: "Close",
                                className: "custom-swal",
                            })
                            return false;
                        }
                    }
                    var containszSZ = $('#containment_volume').val();
                    if (isNaN(containszSZ)) {
                        Swal.fire({
                            title: `Containment size should be numeric only!`,
                            icon: "warning",
                            button: "Close",
                            className: "custom-swal",
                        })
                        return false;
                    }


                    var binB = $('#bin').val();
                    binB = binB.trim();
                    var validB = /^B\d+$/gi.test(binB);
                    if (binB != '') {
                        if (!validB || (binB.length != 7)) {
                            Swal.fire({
                                title: `House Number should be in BXXXXXX format!`,
                                icon: "warning",
                                button: "Close",
                                className: "custom-swal",
                            })
                            return false;
                        }
                    }
                    e.preventDefault();
                    dataTable.draw();
                    containment_id = $('#containment_id').val();
                    type_id = $('#type_id').val();
                    containment_volume = $('#containment_volume').val();
                    containment_location = $('#containment_location').val();
                    emptying_status = $('#emptying_status').val();
                    septic_compliance = $('#septic_compliance').val();
                    bin = $('#bin').val();
                    house_number = $('#house_number').val();
                    const_date = $('#const_date').val();

                });

            resetDataTable(dataTable);
            $("#export").on("click", function(e) {
                e.preventDefault();
                var containment_id = $('#containment_id').val();
                var type_id = $('#type_id').val();
                var containment_volume = $('#containment_volume').val();
                var containment_location = $('#containment_location').val();
                var emptying_status = $('#emptying_status').val();
                var septic_compliance = $('#septic_compliance').val();
                var bin = $('#bin').val();
                var house_number = $('#house_number').val();
                var const_date = $('#const_date').val();
                var searchData = $('input[type=search]').val();
                window.location.href = "{!! url('fsm/containments/export?searchData=') !!}" + searchData +
                    "&containment_id=" + containment_id +
                    "&type_id=" + type_id +
                    "&containment_volume=" + containment_volume +
                    "&containment_location=" + containment_location +
                    "&emptying_status=" + emptying_status +
                    "&septic_compliance=" + septic_compliance +
                    "&bin=" + bin +
                    "&house_number=" + house_number +

                    "&const_date=" + const_date;

            });

            $("#export-shp").on("click", function(e) {
                e.preventDefault();
                var cql_param = getCQLParams();
                window.location.href =
                    "{{ Config::get('constants.GEOSERVER_URL') }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get('constants.AUTH_KEY') }}&typeName={{ Config::get('constants.GEOSERVER_WORKSPACE') }}:containments_layer+&CQL_FILTER=" +
                    cql_param + " &outputFormat=SHAPE-ZIP";

            });

            $("#export-kml").on("click", function(e) {
                e.preventDefault();
                var cql_param = getCQLParams();
                window.location.href =
                    "{{ Config::get('constants.GEOSERVER_URL') }}wfs?service=WFS&version=1.0.0&request=GetFeature&authkey={{ Config::get('constants.AUTH_KEY') }}&typeName={{ Config::get('constants.GEOSERVER_WORKSPACE') }}:containments_layer+&CQL_FILTER=" +
                    cql_param + " &outputFormat=KML";

            });

            function getCQLParams() {
                containment_id = $('#containment_id').val();
                type_id = $('#type_id').val();
                containment_volume = $('#containment_volume').val();
                containment_location = $('#containment_location').val();
                if (containment_location == '0') {
                    var location = 'Inside the building footprint';
                } else {
                    var location = 'Outside the building footprint';
                }
                emptying_status = $('#emptying_status').val();
                bin = $('#bin').val();
                house_number = $('#house_number').val();

                roadcd = $('#road_code').val();
                septic_compliance = $('#septic_compliance').val();
                const_date = $('#const_date').val();
                var cql_param = "deleted_at IS NULL";

                if (containment_id) {
                    cql_param += " AND id ='" + containment_id + "'";
                }
                if (type_id) {
                    cql_param += " AND type_id ='" + type_id + "'";
                }
                if (containment_volume) {
                    cql_param += " AND size ='" + containment_volume + "'";
                }

                if (containment_location) {
                    cql_param += " AND location ='" + containment_location + "'";
                }
                if (emptying_status) {
                    cql_param += " AND emptied_status ='" + emptying_status + "'";
                }
                if (septic_compliance) {
                    cql_param += " AND septic_criteria ='" + septic_compliance + "'";
                }
                if (bin) {
                    cql_param += " AND bin ='" + bin + "'";
                }
                if (const_date) {

                    // Split the date range string into start and end dates
                    const [startDate, endDate] = const_date.split(' - ');

                    // Trim any potential whitespace (though split handles it in this case)
                    const trimmedStartDate = startDate.trim();
                    const trimmedEndDate = endDate.trim();
                    cql_param += " AND construction_date BETWEEN '" + trimmedStartDate + "' AND '" +
                        trimmedEndDate + "'";
                }
                return encodeURI(cql_param);
            }

            $('#const_date').daterangepicker({
                autoUpdateInput: false,
                showDropdowns: true,
                autoApply: true,
                maxDate: moment().format('MM/DD/YYYY'),
                drops: "auto"
            });
            $('#const_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
                    'MM/DD/YYYY'));

            });

            $('#const_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });


        });
    </script>
@endpush
