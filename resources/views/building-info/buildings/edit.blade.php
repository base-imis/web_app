@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
    <div class="card card-info">
        <div class="card-header with-border">
            <h3 class="card-title">House Number : {{ $building->bin }}</h3>
        </div><!-- /.card-header -->
        @include('layouts.components.error-list')
        {!! Form::model($building, [
            'method' => 'PATCH',
            'action' => ['BuildingInfo\BuildingController@update', $building->bin],
            'files' => true,
            'id' => 'prevent-multiple-submits',
            'class' => 'form-horizontal',
        ]) !!}

        @include('building-info.buildings.partial-form', ['submitButtomText' => 'Update'])
        {!! Form::close() !!}
    </div><!-- /.card -->
@stop



@push('scripts')
    <script>
        $(function() {
            var dataTable = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! url("fsm/containments/$building->bin/containmentData") !!}',
                    data: function(d) {

                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'type',
                        name: 'type'
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
                                'Your file has been deleted.',
                                'success'
                            )
                        }
                    })
                });
            });
        });



        // searchable dropdown for building_associated_to
        $('#building_associated_to').prepend(
            '<option selected="{{ $building->building_associated_to }}">{{ $building->building_associated_to }}</option>'
        ).select2({
            ajax: {
                url: "{{ route('building.get-house-numbers-all') }}",
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'BIN of Main Building',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });


        $('#road_code').prepend('<option selected="{{ $building->road_code }}">{{ $building->road_code }}</option>')
            .select2({
                ajax: {
                    url: "{{ route('roadlines.get-road-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            // ward: $('#ward').val(),
                            page: params.page || 1
                        };
                    },
                },
                placeholder: 'Road Code - Road Name',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

        //show use category only when functional use is filled

        function handleMainBuildingChange() {
            if ($("#main_building :selected").text() == "Yes") {
                $('#building_associated').hide();
            } else if ($("#main_building :selected").text() == "No") {
                $('#building_associated').show();
            }
        }

        ////thees are to be removed after the deadline to clean the code




        // Logic for lic_status field
        function handleLowIncomeChange() {
            if ($("#lic_status select").val() == "1") {
                $('#lic_status').show();
            } else {

                $('#lic_id').hide();
            }
        }

        function handleLicStatusChange() {
            $('#lic_status').on('change', function() {
                if ($("#lic_status :selected").text() == "Yes") {
                    $('#lic_id').show();
                } else {
                    $('#lic_id').hide();
                }
            });
        }



        function maindrinkingWaterSource() {
            if ($("#water-id select").val() == "11") {
                $('#water-customer-id').show();
                $('#water-pipe-id').show();
            } else {
                $('#water-customer-id').hide();
                $('#water-pipe-id').hide();
            }
        }

        function wellpresenceStatus() {
            if ($("#well-presence select").val() == "1") {

                $('#distance-from-well').show();
            } else {
                $('#distance-from-well').hide();
            }
        }

        function handleToiletPresenceChange() {
            if ($("#toilet-presence :selected").text() == "Yes") {
                $('#toilet-info').show();
                $('#shared-toilet').show();
                $('#toilet-connection').show();
                $('#shared-toilet-popn').show();
                $('#defecation-place').hide();
                $('#ctpt-toilet').hide();
            } else {
                $('#vacutug-accessible').hide();
                $('#defecation-place').show();
                $('#toilet-info').hide();
                $('#shared-toilet').hide();
                $('#toilet-connection').hide();
                $('#shared-toilet-popn').hide();
                $('#containment-info').hide();
                $('#containment-id').hide();
                $('#drain-code').hide();
                $('#sewer-code').hide();
            }
        }

        function handleToiletConnectionChange() {

            if ($("#toilet-connection :selected").text() === "Septic Tank" || $("#toilet-connection :selected").text() ===
                "Pit/ Holding Tank") {
                $('#containment-info').show();
                $('#containment-id').hide();
                $('#drain-code').hide();
                $('#sewer-code').hide();
                $('#vacutug-accessible').show();
            } else if ($("#toilet-connection :selected").text() === "Shared Septic Tank") {
                $('#containment-id').show();
                $('#containment-info').hide();
                $('#drain-code').hide();
                $('#sewer-code').hide();
                $('#vacutug-accessible').hide();
            } else if ($("#toilet-connection :selected").text() === "Drain Network") {
                $('#drain-code').show();
                $('#containment-id').hide();
                $('#containment-info').hide();
                $('#sewer-code').hide();
                $('#vacutug-accessible').hide();
            } else if ($("#toilet-connection :selected").text() === "Sewer Network") {
                $('#drain-code').hide();
                $('#containment-id').hide();
                $('#containment-info').hide();
                $('#sewer-code').show();
                $('#vacutug-accessible').hide();
            } else {
                $('#containment-id').hide();
                $('#containment-info').hide();
                $('#drain-code').hide();
                $('#sewer-code').hide();
                $('#vacutug-accessible').hide();
            }
        }

        function hideoffice() {
            if ($("#functional-use select").val() == 1 || $("#functional-use select").val() == 15) {
                $('#office-business').hide();
            } else {
                $('#office-business').show();
            }
        }

        function defecationStatus() {
            if ($("#defecation-place select").val() == 9) {
                $('#ctpt-toilet').show();
            } else {
                $('#ctpt-toilet').hide();
            }
        }

        // Trigger event handlers when page loads
        $(document).ready(function() {
            handleMainBuildingChange();
            handleLowIncomeChange();
            handleLicStatusChange();
            maindrinkingWaterSource();
            wellpresenceStatus();
            handleToiletPresenceChange(); // Ensure the function runs on page load
            handleToiletConnectionChange();
            defecationStatus()
            hideoffice();
            // Ensure the function runs on page load
        });

        // Bind change event to trigger event handlers
        $('#main_building').on('change', handleMainBuildingChange);
        $('#low_income_hh select').on('change', handleLowIncomeChange);
        $('#lic_status select').on('change', handleLicStatusChange);
        $('#well-presence select').on('change', wellpresenceStatus);
        $('#water-id select').on('change', maindrinkingWaterSource);
        $('#toilet-presence').on('change', handleToiletPresenceChange); // Bind the change event to the function
        $('#toilet-connection').on('change', handleToiletConnectionChange); // Bind the change event to the function
        $('#defecation-place').on('change', defecationStatus); // Bind the change event to the function
        $('#functional-use').on('change', hideoffice);

        /*
        script to dynamically display child dropdown values according to value selected in parent dropdown
        */
        var usecatgs = JSON.parse('{!! $usecatgsJson !!}');
        $(document).ready(function() {

            $('#functional_use_id').change(function() {
                var html = '<option value="">Use Categories of Building</option>';

                var functional_use = $(this).val();
                if (functional_use) {
                    $.each(usecatgs[functional_use], function(key, value) {
                        html += '<option value="' + key + '">' + value + '</option>';
                    });
                    if (functional_use == 1 || functional_use == 15) {
                        $('#office-business').hide();
                    } else {
                        $('#office-business').show();
                    }
                }

                $('#use_category_id').html(html);
            });

            @if ($errors->any())
                @if (old('functional_use'))
                    $('#functional_use_id').change();

                    @if (old('use_category_id'))
                        $('#use_category_id').val('{{ old('use_category_id') }}');
                    @endif
                @endif
            @endif
        });
        document.addEventListener('DOMContentLoaded', function() {

            var populationFields = document.querySelectorAll(
                'input[name="male_population"], input[name="female_population"], input[name="other_population"]'
            );


            populationFields.forEach(function(field) {
                field.addEventListener('input', function() {
                    var malePopulation = parseInt(document.querySelector(
                        'input[name="male_population"]').value) || 0;
                    var femalePopulation = parseInt(document.querySelector(
                        'input[name="female_population"]').value) || 0;
                    var otherPopulation = parseInt(document.querySelector(
                        'input[name="other_population"]').value) || 0;
                    var totalPopulation = malePopulation + femalePopulation + otherPopulation;
                    document.querySelector('input[name="population_served"]').value =
                        totalPopulation;
                });
            });
        });






        $('#watersupply_pipe_code').prepend(
            '<option selected=""{{ $building->watersupply_pipe_code }}"">{{ $building->watersupply_pipe_code }}</option>'
        ).select2({
            ajax: {
                url: "{{ route('watersupply.get-watersupply-code') }}",
                data: function(params) {
                    return {
                        search: params.term,
                        // ward: $('#ward').val(),
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Water Supply Pipe Line Code',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });
        $('#sewer_code').prepend('<option selected="{{ $building->sewer_code }}">{{ $building->sewer_code }}</option>')
            .select2({
                ajax: {
                    url: "{{ route('sewerlines.get-sewer-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            // ward: $('#ward').val(),
                            page: params.page || 1
                        };
                    },
                },
                placeholder: 'Sewer Code',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });


        /*
              script to  make dropdowns searchable
          */
        $('#build_contain').prepend().select2({
            ajax: {
                url: "{{ route('building.get-house-numbers-containments') }}",
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'BIN of Pre Connected Building',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });

        // for dynamic dropdown for containment type acc to toilet connection

        $('#toilet-connection select').on('change', function() {
            var selectedText = $(this).find('option:selected').text();

            if (selectedText == "Septic Tank") {
                $.ajax({
                    url: "{{ route('building.get-containment-septic') }}",
                    method: "GET",
                    data: {
                        sanitation_system_id: 3
                    },
                    success: function(response) {
                        var containmentSelect = $('#containment-type select');
                        containmentSelect.empty();
                        containmentSelect.prepend('<option selected="">Containment Type</option>')
                        $.each(response, function(index, option) {
                            containmentSelect.append($('<option>').text(option.type).attr(
                                'value', option.id));
                        });
                    },

                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            } else if (selectedText == "Pit/ Holding Tank") {
                $.ajax({
                    url: "{{ route('building.get-containment-septic') }}",
                    method: "GET",
                    data: {
                        sanitation_system_id: 4
                    },
                    success: function(response) {
                        var containmentSelect = $('#containment-type select');
                        containmentSelect.empty();
                        containmentSelect.prepend('<option selected="">Containment Type</option>')
                        $.each(response, function(index, option) {
                            containmentSelect.append($('<option>').text(option.type).attr(
                                'value', option.id));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#containment-type select').empty();
            }
        });


        $('#functional_use_id').on('change', function() {
            if ($("#functional_use_id :selected").text() == "Community/Public Toilet") {
                $('#population-info').hide();
                $('#family-count').hide();
                $('#male-population').hide();
                $('#female-population').hide();
                $('#other-population').hide();
                $('#male-diff-population').hide();
                $('#female-diff-population').hide();
                $('#other-diff-population').hide();
            } else {
                $('#male-population').show();
                $('#female-population').show();
                $('#other-population').show();
                $('#population-info').show();
                $('#family-count').show();
                $('#male-diff-population').show();
                $('#female-diff-population').show();
                $('#other-diff-population').show();
            }
        });
        // To set toilet status == 1 and show related fields(also applied in backend part (Building Structure Service))
        $('#functional_use_id').on('change', function() {
            var selectedText = $("#functional_use_id option:selected").text();
            if (selectedText == "Community/Public Toilet") {
                $('#toilet_status').val('1'); // Set toilet_status to 'Yes'
                $('#toilet-info').show();
                $('#shared-toilet').show();
                $('#toilet-connection').show();
                $('#shared-toilet-popn').show();
                $('#defecation-place').hide();
                $('#ctpt-toilet').hide();
            }
        });

        //show use category only when functional use is filled

        var functionalUseId = $('#functional_use_id').val();
        if (functionalUseId) {
            $('#use-category').show();
        } else {
            $('#use-category').hide();
        }

        $('#functional_use_id').on('change', function() {
            var functionalUseId = $(this).val();
            if (functionalUseId) {
                $('#use-category').show();
            } else {
                $('#use-category').hide();
            }
        });
    </script>
@endpush
