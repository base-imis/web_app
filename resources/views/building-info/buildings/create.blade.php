@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

    @include('layouts.components.error-list')
    <div class="card card-info">
        {!! Form::open([
            'url' => 'building-info/buildings',
            'id' => 'prevent-multiple-submits',
            'files' => true,
            'class' => 'form-horizontal prevent-multiple-submits',
        ]) !!}
        @include('building-info.buildings.partial-form', ['submitButtomText' => 'Save'])
        {!! Form::close() !!}
    </div><!-- /.card -->
@stop



@push('scripts')
    <script>
        $(document).ready(function() {
            dynamicBuildingForm();

        });
        /*
          script to dynamically display child dropdown values according to value selected in parent dropdown
          */
        var usecatgs = JSON.parse('{!! $usecatgsJson !!}');
        $(document).on('ready', function() {
            @if ($errors->any())
                @if (old('functional_use'))
                    $('#functional_use_id').change();

                    @if (old('use_category_id'))
                        $('#use_category_id').val('{{ old('use_category_id') }}');
                    @endif
                @endif
            @endif
        });

        $('#toilet-connection select').on('change', function() {
            var selectedText = $(this).find('option:selected').text();
            var sanitationId;
            if (selectedText === "Septic Tank") {
                sanitationId = 3;
            } else if (selectedText === "Pit/ Holding Tank") {
                sanitationId = 4;
            } else {
                $('#containment-type select').empty();
                return;
            }
            $.ajax({
                url: "{{ route('building.get-containment-septic') }}",
                method: "GET",
                data: {
                    sanitation_system_id: sanitationId
                },
                success: function(response) {
                    var containmentSelect = $('#containment-type select');
                    containmentSelect.empty();
                    containmentSelect.append('<option selected value=" ">Containment Type</option>');
                    $.each(response, function(index, option) {
                        containmentSelect.append($('<option>').text(option.type).attr('value',
                            option.id));
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);

                }
            });
        });


          // searchable dropdown for building_associated_to
          $('#building_associated_to').prepend('<option selected=""></option>').select2({

        ajax: {
            url: "{{ route('building.get-house-numbers-all') }}",
            data: function (params) {
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

        $('#road_code').prepend('<option selected=""></option>').select2({

ajax: {
    url: "{{ route('roadlines.get-road-names') }}",
    data: function (params) {
        return {
            search: params.term,
            page: params.page || 1
        };
    },
},
placeholder: 'Road Code - Road Name',
allowClear: true,
closeOnSelect: true,
width: '85%',
});
$('#watersupply_pipe_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('watersupply.get-watersupply-code') }}",
                data: function (params) {
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
        $('#sewer_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('sewerlines.get-sewer-names') }}",
                data: function (params) {
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

        $('#drain_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('drains.get-drain-names') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        // ward: $('#ward').val(),
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Drain Code',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });

        $('#build_contain').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('building.get-house-numbers-containments') }}",
                data: function (params) {
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
        //To calculate volume of cylinder
        document.addEventListener('DOMContentLoaded', function() {
            var diameterField = document.querySelector('input[name="pit_diameter"]');
            var depthField = document.querySelector('input[name="pit_depth"]');
            var volumeField = document.querySelector('input[name="size"]');
            var calculateVolume = function() {
                var diameter = parseFloat(diameterField.value) || 0;
                var depth = parseFloat(depthField.value) || 0;

                // Calculate radius
                var radius = diameter / 2;

                // Calculate volume
                var volume = Math.PI * Math.pow(radius, 2) * depth;
                // Update the volume field with the calculated value
                volumeField.value = volume.toFixed(2); // Round to 2 decimal places

            };

            // Add event listeners to trigger volume calculation on input change
            diameterField.addEventListener('input', calculateVolume);
            depthField.addEventListener('input', calculateVolume);
        });


        //To calculate volume of recatangle
        document.addEventListener('DOMContentLoaded', function() {

            var lengthField = document.querySelector('input[name="tank_length"]');
            var widthField = document.querySelector('input[name="tank_width"]');
            var depthField = document.querySelector('input[name="depth"]');
            var volumeField = document.querySelector('input[name="size"]');

            var calculateVolume = function() {
                var length = parseFloat(lengthField.value) || 0;
                var width = parseFloat(widthField.value) || 0;
                var depth = parseFloat(depthField.value) || 0;

                // Calculate volume
                var volume = length * width * depth;

                // Update the volume field with the calculated value
                volumeField.value = volume.toFixed(2); // Round to 2 decimal places
            };

            // Add event listeners to trigger volume calculation on input change
            lengthField.addEventListener('input', calculateVolume);
            widthField.addEventListener('input', calculateVolume);
            depthField.addEventListener('input', calculateVolume);
        });


        // document.addEventListener('DOMContentLoaded', function() {
        //     var populationFields = document.querySelectorAll(
        //         'input[name="male_population"], input[name="female_population"], input[name="other_population"]'
        //     );
        //     populationFields.forEach(function(field) {
        //         field.addEventListener('input', function() {
        //             var malePopulation = parseInt(document.querySelector(
        //                 'input[name="male_population"]').value) || 0;
        //             var femalePopulation = parseInt(document.querySelector(
        //                 'input[name="female_population"]').value) || 0;
        //             var otherPopulation = parseInt(document.querySelector(
        //                 'input[name="other_population"]').value) || 0;
        //             var totalPopulation = malePopulation + femalePopulation + otherPopulation;
        //             document.querySelector('input[name="population_served"]').value =
        //                 totalPopulation;
        //         });
        //     });
        // });
    </script>
@endpush
