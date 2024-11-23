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
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card">

        <div class="card-body">
            {!! Form::model([
                'method' => 'PATCH',
                'action' => ['Fsm\CwisSettingController@update'],
                'class' => 'form-horizontal',
                'id' => 'editForm',
            ]) !!}

            @php
                $units = [
                    'average_water_consumption_lpcd' => 'liters/day',
                    'waste_water_conversion_factor' => 'liters/day',
                    'greywater_conversion_factor_connected_to_sewer' => 'm³',
                    'greywater_conversion_factor_not_connected_to_sewer' => 'm³',
                                     
                ];

                $abbreviations = [
                    'average_water_consumption_lpcd' => 'Average Water Consumption Lpcd',
                    'waste_water_conversion_factor' => 'Waste Water Conversion Factor',
                    'greywater_conversion_factor_connected_to_sewer' => 'Conversion Factor For Greywater And Supernantant',
                    'greywater_conversion_factor_not_connected_to_sewer' => 'Greywater Conversion Factor Not Connected To sewer'
                   
                ];
            @endphp

            @foreach (['average_water_consumption_lpcd', 'waste_water_conversion_factor', 'greywater_conversion_factor_connected_to_sewer','greywater_conversion_factor_not_connected_to_sewer'] as $key)
                <div class="form-group row">
                    {!! Form::label(
                        $key,
                        isset($abbreviations[$key])
                            ? $abbreviations[$key]
                            : ucwords(str_replace('_', ' ', $key)) . ' (' . $units[$key] . ')',
                        ['class' => 'col-sm-3 control-label'],
                    ) !!}
                    <div class="col-sm-3">
                        {!! Form::number($key, $data[$key], [
                            'class' => 'form-control',
                            'placeholder' => ucwords(str_replace('_', ' ', $key)),
                        ]) !!}
                    </div>
                </div>
            @endforeach





        </div><!-- /.box-body -->
        <div class="card-footer">
            <span id="editButton" class="btn btn-info">Edit</span>
            <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">Save</button>
        </div><!-- /.box-footer -->
    </div>
    {!! Form::close() !!}
    </div>


    </div><!-- /.box -->
@stop
@push('scripts')
    <script>
        $(document).ready(function() {
            // Function to toggle readonly attribute
            function toggleReadOnly(readonly) {
                $('input').prop('readonly', readonly);
            }

            // Initially set form fields as read-only
            toggleReadOnly(true);

            // Edit button click event
            $('#editButton').click(function() {
                $('input').removeAttr('readonly');
                $('#editButton').hide();
                $('#saveButton').show();
            });

            // Check for errors and update buttons accordingly
            var hasErrors = $('.alert-danger').length > 0;

            if (hasErrors) {
                $('input').removeAttr('readonly');
                $('#editButton').hide();
                $('#saveButton').show();
            } else {
                $('#saveButton').hide();
                $('#editButton').show();
            }
        });
    </script>
@endpush
