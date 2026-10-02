<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    @can('Add Treatment Plant Efficiency Test')
                        <a href="{{ action('Fsm\TreatmentPlantTestController@create') }}" class="btn btn-info">Add Performance
                            Efficiency Test</a>
                    @endcan
                    @can('Export Treatment Plant Efficiency Tests')
                        <a href="{{ action('Fsm\TreatmentPlantTestController@export') }}" id="export"
                            class="btn btn-info">Export to
                            CSV</a>
                    @endcan
                    <a href class="btn btn-info float-right" data-toggle="collapse" data-target="#collapseFilter"
                        aria-expanded="false" aria-controls="collapseFilter">Show Filter</a>

                </div><!-- /.box-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="accordion" id="accordionFilter">
                                <div class="accordion-item">
                                    <div id="collapseFilter" class="collapse" aria-labelledby="filter"
                                        data-parent="#accordionFilter">
                                        <div class="accordion-body">
                                            <form class="form-horizontal" id="filter-form">


                                                <div class="form-group row mb-3">
                                                    <label for="treatment_plant_name"
                                                        class="col-md-2 col-form-label">Treatment Plant</label>
                                                    <div class="col-md-2">
                                                        <select class="form-control" id="treatment_plant_name"
                                                            name="treatment_plant_name">
                                                            <option value="">Treatment Plant</option>
                                                            @foreach ($tpnames as $id => $name)
                                                                <option value="{{ $id }}">{{ $name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                    </div>


                                                    <label for="temperature" class="col-md-2 col-form-label">Temperature
                                                        °C</label>
                                                    <div class="col-md-2">
                                                        <input type="number" step="any" class="form-control"
                                                            id="temperature" placeholder="Temperature in °C" />
                                                    </div>

                                                    <label for="cod" class="col-md-2 col-form-label">COD (mg/l)</label>
                                                    <div class="col-md-2">
                                                        <input type="text" step="any" class="form-control"
                                                            id="cod" placeholder="COD (mg/l)" oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"/>
                                                    </div>

                                                </div>

                                                <div class="form-group row mb-3">


                                                    <label for="date" class="col-md-2 col-form-label">Sample Date</label>
                                                    <div class="col-md-2">
                                                        <input type="date" class="form-control" id="date" onclick ='this.showPicker();'/>
                                                    </div>

                                                    <label for="bod_validation" class="col-md-2 col-form-label">BOD
                                                        Compliance</label>
                                                    <div class="col-md-2">
                                                        <select class="form-control" id="bod_validation"
                                                            name="bod_validation">
                                                            <option value="">Select</option>
                                                            <option value=true>Yes</option>
                                                            <option value=false>No</option>
                                                        </select>
                                                    </div>

                                                    <label for="ecoli_validation" class="col-md-2 col-form-label">Ecoli
                                                        Compliance</label>
                                                    <div class="col-md-2">
                                                        <select class="form-control" id="ecoli_validation"
                                                            name="ecoli_validation">
                                                            <option value="">Select</option>
                                                            <option value=true>Yes</option>
                                                            <option value=false>No</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group row mb-3">

                                                    <label for="tss_validation" class="col-md-2 col-form-label">TSS
                                                        Compliance</label>
                                                    <div class="col-md-2">
                                                        <select class="form-control" id="tss_validation"
                                                            name="tss_validation">
                                                            <option value="">Select</option>
                                                            <option value=true>Yes</option>
                                                            <option value=false>No</option>
                                                        </select>
                                                    </div>
                                                    <label for="ph_validation" class="col-md-2 col-form-label">pH Range
                                                        Compliance</label>
                                                    <div class="col-md-2">
                                                        <select class="form-control" id="ph_validation"
                                                            name="ph_validation">
                                                            <option value="">Select</option>
                                                            <option value=true>Yes</option>
                                                            <option value=false>No</option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="card-footer text-right">
                                                    <button type="submit" class="btn btn-info">Filter</button>
                                                    <button type="reset" id="reset-filter"
                                                        class="btn btn-info reset">Reset</button>
                                                </div>

                                                <div class="clearfix"></div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="overflow: auto; width: 100%;">
                            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                                <thead>
                                    <tr>
                                        <th>Treatment Plant</th>
                                        <th>Sample Date</th>
                                        <th>Temperature °C</th>
                                        <th>pH</th>
                                        <th>COD (mg/l)</th>
                                        <th>BOD (mg/l)</th>
                                        <th>TSS (mg/l)</th>
                                        <th>Ecoli</th>
                                        <th>Remark</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div><!-- /.box-body -->


                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="message"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @stop

            @push('scripts')
                <script>
                    $(document).ready(function() {
                        var dataTable = $('#data-table').DataTable({
                            bFilter: false,
                            processing: true,
                            serverSide: true,
                            scrollCollapse: true,
                            ajax: {
                                url: '{!! url('fsm/treatment-plant-test/data') !!}',
                                data: function(d) {
                                    d.treatment_plant_id = $('#treatment_plant_id').val();
                                    d.treatment_plant_name = $('#treatment_plant_name').val();
                                    d.temperature = $('#temperature').val();
                                    d.date = $('#date').val();
                                    d.ph = $('#ph').val();
                                    d.cod = $('#cod').val();
                                    d.bod = $('#bod').val();
                                    d.tss = $('#tss').val();
                                    d.remarks = $('#remarks').val();
                                    d.bod_validation = $('#bod_validation').val();
                                    d.ecoli_validation = $('#ecoli_validation').val();
                                    d.tss_validation = $('#tss_validation').val();
                                    d.ph_validation = $('#ph_validation').val();
                                }
                            },
                            columns: [

                                {
                                    data: 'treatment_plant_id',
                                    name: 'treatment_plant_id'
                                },
                                {
                                    data: 'date',
                                    name: 'date'
                                },
                                {
                                    data: 'temperature',
                                    name: 'temperature'
                                },
                                {
                                    data: 'ph',
                                    name: 'ph'
                                },
                                {
                                    data: 'cod',
                                    name: 'cod',
                                },
                                {
                                    data: 'bod',
                                    name: 'bod',
                                },
                                {
                                    data: 'tss',
                                    name: 'tss',
                                },
                                {
                                    data: 'ecoli',
                                    name: 'ecoli',
                                },
                                {
                                    data: 'remarks',
                                    name: 'remarks',
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                },


                            ],
                            order: [
                                [0, 'desc']
                            ]
                        }).on('draw', function() {
                            $('.delete').on('click', function(e) {
                                var form = $(this).closest("form");
                                event.preventDefault();
                                Swal.fire({
                                    title: '{{ __('Are you sure?') }}',
                        text: {{ Illuminate\Support\Js::from(__('You won\'t be able to revert this!')) }},
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '{{ __('Yes, delete it!') }}',
                        cancelButtonText: '{{ __('Cancel') }}',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit();
                                    }
                                })

                            });
                            resetDataTable(dataTable);

                        });


                        var treatment_plant_id = '',
                            treatment_plant_name = '',
                            date = '',
                            temperature = '',
                            ph = '',
                            cod = '',
                            bod = '',
                            tss = '',
                            remarks = '',
                            bod_validation = '',
                            ecoli_validation= '',
                            tss_validation= '',
                            ph_validation = '';

                        $('#filter-form').on('submit', function(e) {
                            treatment_plant_id = $('#treatment_plant_id').val();
                            treatment_plant_name = $('#treatment_plant_name').val();
                            date = $('#date').val();
                            temperature = $('#temperature').val();
                            cod = $('#cod').val();
                            remarks = $('#remarks').val();
                            bod_validation = $('#bod_validation').val();
                            ecoli_validation = $('#ecoli_validation').val();
                            tss_validation = $('#tss_validation').val();
                            ph_validation = $('#ph_validation').val();
                            e.preventDefault();
                            dataTable.draw();

                        });
                        $("#export").on("click", function(e) {
                            e.preventDefault();
                            const searchData = $('input[type=search]').val();
                            const treatment_plant_name = $('#treatment_plant_name').val();
                            const date = $('#date').val();
                            const temperature = $('#temperature').val();
                            const cod = $('#cod').val();
                            const remarks = $('#remarks').val();
                            const bod_validation = $('#bod_validation').val();
                            const ecoli_validation = $('#ecoli_validation').val();
                            const tss_validation = $('#tss_validation').val();
                            const ph_validation = $('#ph_validation').val();


                            window.location.href = "{!! url('fsm/treatment-plant-test/export?searchData=') !!}" + searchData +
                                "&treatment_plant_name=" + treatment_plant_name +
                                "&date=" + date +
                                "&temperature=" + temperature +
                                "&cod=" + cod +
                                "&remarks=" + remarks +
                                "&bod_validation=" + bod_validation +
                                "&ecoli_validation=" + ecoli_validation +
                                "&tss_validation=" + tss_validation +
                                "&ph_validation=" + ph_validation ;

                        });



                        function getCQLParams() {

                            var treatment_plant_id = $('#treatment_plant_id').val();
                            var date = $('#date').val();
                            var temperature = $('#temperature').val();
                            var cod = $('#cod').val();
                            var cql_param = "deleted_at IS NULL";

                            if (treatment_plant_id) {
                                cql_param += " AND treatment_plant_id ILIKE '%" + treatment_plant_id + "%'";
                            }
                            if (date) {
                                cql_param += " AND date ='" + date + "'";

                            }
                            if (temperature) {
                                cql_param += " AND temperature ILIKE '" + temperature + "%'";
                            }
                            if (cod) {
                                cql_param += " AND cod ='" + cod + "'";
                            }

                            return encodeURI(cql_param);
                        }

                        $('.date, #date_from, #date_to').datepicker({

                            format: 'yyyy-mm-dd',
                            todayHighlight: true

                        });

                        $('.date, #date_from, #date_to').focus(function() {
                            $(this).blur();
                        });
                    });
                </script>
            @endpush
