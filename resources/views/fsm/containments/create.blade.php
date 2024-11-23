@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

@include('layouts.components.error-list')
<!-- /.card-footer -->
<div class="card card-info">
	<!-- /.card-header -->

    {{-- @include('errors.list') --}}
    {!! Form::open([ 'action' => ['Fsm\ContainmentController@storeContainment', $id],'files' => true, 'class' => 'form-horizontal']) !!}
		@include('fsm.containments.partial-form', ['submitButtomText' => 'Save'])
	<div class="card-footer">

    <a href="{{ action('BuildingInfo\BuildingController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}





    </div>
	{!! Form::close() !!}
</div><!-- /.card -->

@stop

@push('scripts')
<script>

onloadDynamicContainmentType();


$('#containment-type').on('change', function () {

var selectedText = $("#containment-type option:selected").text();
var showOptions = [
    "Septic Tank connected to Drain Network",
    "Lined Pit connected to Drain Network"
];
if (showOptions.includes(selectedText)) {
    $('#drain-code').show();
} else {
    $('#drain-code').hide();
}
});

$('#containment-type').on('change', function () {

var selectedText = $("#containment-type option:selected").text();
var showOptions = [
    "Septic Tank connected to Sewer Network",
    "Lined Pit connected to Sewer Network"
];
if (showOptions.includes(selectedText)) {
    $('#sewer-code').show();
} else {
    $('#sewer-code').hide();
}
});


$('#containment-type, #pit-shape').on('change', function () {

var selectedText = $("#containment-type option:selected").text();
var showOptions = [
    "Double Pit",
    "Permeable/ Unlined Pit/Holding Tank",
    "Lined Pit connected to a Soak Pit",
    "Lined Pit connected to Water Body",
    "Lined Pit connected to Open Ground",
    "Lined Pit connected to Sewer Network",
    "Lined Pit connected to Drain Network",
    "Lined Pit without Outlet",
    "Lined Pit with Unknown Outlet Connection",
];
if (showOptions.includes(selectedText)) {
    $('#pit-shape').show();
    $('#tank-depth').hide();
    $('#tank-width').hide();
    $('#tank-length').hide();
    $('#septic-tank').hide();
}
else {
    $('#tank-length').show();
    $('#septic-tank').show();
    $('#pit-shape').hide();
}

// Check if the selected text is in the array of showOptions and if the pit shape is "Cylindrical"
if (showOptions.includes(selectedText) && ($("#pit-shape :selected").text() ==
    "Cylindrical")) {
    $('#pit-depth').show();
    $('#pit-size').show();
    $('#tank-depth').hide();
    $('#tank-width').hide();
    $('#tank-length').hide();
} else {
    $('#pit-size').hide();
    $('#pit-depth').hide();
    $('#tank-depth').show();
    $('#tank-width').show();
    $('#tank-length').show();

}


if (!showOptions.includes(selectedText) || $("#pit-shape :selected").text() !== "Rectangular") {
    $('#tank-length').show();
} else {
    $('#tank-length').hide();
}

if ($("#pit-shape :selected").text() == "Cylindrical") {
    $('#tank-length').hide();
} else {
    $('#tank-length').show();
}
if (!showOptions.includes(selectedText)) {
    $('#tank-length').show();
}

});
function onloadDynamicContainmentType() {
    $('#containment-info').show();
    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Septic Tank connected to Drain Network",
        "Lined Pit connected to Drain Network"
    ];
    if (showOptions.includes(selectedText)) {
        $('#drain-code').show();
    } else {
        $('#drain-code').hide();
    }

    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Septic Tank connected to Sewer Network",
        "Lined Pit connected to Sewer Network"
    ];
    if (showOptions.includes(selectedText)) {
        $('#sewer-code').show();
    } else {
        $('#sewer-code').hide();
    }


    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Double Pit",
        "Permeable/ Unlined Pit/Holding Tank",
        "Lined Pit connected to a Soak Pit",
        "Lined Pit connected to Water Body",
        "Lined Pit connected to Open Ground",
        "Lined Pit connected to Sewer Network",
        "Lined Pit connected to Drain Network",
        "Lined Pit without Outlet",
        "Lined Pit with Unknown Outlet Connection",
    ];
    if (showOptions.includes(selectedText)) {
        $('#pit-shape').show();
        $('#tank-depth').hide();
        $('#tank-width').hide();
        $('#tank-length').hide();
        $('#septic-tank').hide();
    }
    else {
        $('#septic-tank').show();
        $('#pit-shape').hide();
    }

    // Check if the selected text is in the array of showOptions and if the pit shape is "Cylindrical"
    if (showOptions.includes(selectedText) && ($("#pit-shape :selected").text() ==
        "Cylindrical")) {
        $('#pit-depth').show();
        $('#pit-size').show();
        $('#tank-depth').hide();
        $('#tank-width').hide();
        $('#tank-length').hide();
    } else {
        $('#pit-size').hide();
        $('#pit-depth').hide();
        $('#tank-depth').show();
        $('#tank-width').show();
        $('#tank-length').show();

    }

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

}

$('#sewer_code').prepend('<option selected=""></option>').select2({
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
</script>
@endpush
