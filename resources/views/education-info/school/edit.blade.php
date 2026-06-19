@extends('layouts.dashboard')
@section('title', 'Edit School')

@section('content')
@include('layouts.components.error-list')

@php
    $studentLevels = [
        'pre_primary' => [
            'label' => 'Pre-primary',
            'checkbox' => 'school_type_pre_primary',
            'fields' => ['girls' => 'pre_primary_girls_count', 'boys' => 'pre_primary_boys_count', 'other' => 'pre_primary_other_count', 'total' => 'pre_primary_total_count'],
        ],
        'basic_1_5' => [
            'label' => 'Basic (1 - 5)',
            'checkbox' => 'school_type_basic_1_5',
            'fields' => ['girls' => 'basic_1_5_girls_count', 'boys' => 'basic_1_5_boys_count', 'other' => 'basic_1_5_other_count', 'total' => 'basic_1_5_total_count'],
        ],
        'basic_6_8' => [
            'label' => 'Basic (6 - 8)',
            'checkbox' => 'school_type_basic_6_8',
            'fields' => ['girls' => 'basic_6_8_girls_count', 'boys' => 'basic_6_8_boys_count', 'other' => 'basic_6_8_other_count', 'total' => 'basic_6_8_total_count'],
        ],
        'secondary_9_10' => [
            'label' => 'Secondary (9 - 10)',
            'checkbox' => 'school_type_secondary_9_10',
            'fields' => ['girls' => 'secondary_9_10_girls_count', 'boys' => 'secondary_9_10_boys_count', 'other' => 'secondary_9_10_other_count', 'total' => 'secondary_9_10_total_count'],
        ],
        'secondary_9_12' => [
            'label' => 'Secondary (9 - 12)',
            'checkbox' => 'school_type_secondary_9_12',
            'fields' => ['girls' => 'secondary_9_12_girls_count', 'boys' => 'secondary_9_12_boys_count', 'other' => 'secondary_9_12_other_count', 'total' => 'secondary_9_12_total_count'],
        ],
    ];

    $facilityGroups = [
        'Toilets' => [
            ['label' => 'Teacher Staff', 'fields' => ['male' => 'toilet_teacher_male', 'female' => 'toilet_teacher_female', 'other' => 'toilet_teacher_other', 'total' => 'toilet_teacher_total']],
            ['label' => 'Students', 'fields' => ['male' => 'toilet_student_male', 'female' => 'toilet_student_female', 'other' => 'toilet_student_other', 'total' => 'toilet_student_total']],
        ],
        'Urinals' => [
            ['label' => 'Teacher Staff', 'fields' => ['male' => 'urinal_teacher_male', 'female' => 'urinal_teacher_female', 'other' => 'urinal_teacher_other', 'total' => 'urinal_teacher_total']],
            ['label' => 'Students', 'fields' => ['male' => 'urinal_student_male', 'female' => 'urinal_student_female', 'other' => 'urinal_student_other', 'total' => 'urinal_student_total']],
        ],
        'Hand Washing Units' => [
            ['label' => 'Teacher Staff', 'fields' => ['male' => 'handwash_teacher_male', 'female' => 'handwash_teacher_female', 'other' => 'handwash_teacher_other', 'total' => 'handwash_teacher_total']],
            ['label' => 'Students', 'fields' => ['male' => 'handwash_student_male', 'female' => 'handwash_student_female', 'other' => 'handwash_student_other', 'total' => 'handwash_student_total']],
        ],
    ];
@endphp

<div class="card card-info">
    {!! Form::model($school, [
        'route' => ['education.school.update', $school->custom_school_id],
        'method' => 'PUT',
        'files' => true,
        'id' => 'prevent-multiple-submits',
    ]) !!}
    <div class="card-body" style="font-family: 'Open Sans', sans-serif;">
        <h3 class="mt-4">School Information</h3>

        <div class="form-group row required">
            {!! Form::label('name', 'School Name', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('name', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'School Name', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('headteacher_name', 'Headteacher Name', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('headteacher_name', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Head Teacher Name', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('contact_person_name', 'Contact Person', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('contact_person_name', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Contact Person Name', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('contact_person_number', 'Contact Number', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('contact_person_number', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Contact Person Number', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <div class="form-group row required" id="bin">
            {!! Form::label('bin', 'House Number / BIN', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('bin', $bin, null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'House Number / BIN',
                    'id' => 'bin_select',
                ]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('ward_no', 'Ward Number', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('ward_no', null, ['class' => 'form-control col-sm-10', 'min' => 1, 'placeholder' => 'Ward Number']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('location_name', 'Location', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('location_name', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Location', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <h3 class="mt-3">Building Information</h3>

        <div class="form-group row">
            {!! Form::label('main_building_structure_type', 'Main Building Structure Type', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('main_building_structure_type', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Enter Structure Type', 'autocomplete' => 'off']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('main_building_floors', 'Main Building Floors', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('main_building_floors', null, ['class' => 'form-control col-sm-10', 'min' => 0, 'placeholder' => 'Enter Number of Floors']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('associate_buildings_count', 'Number of Associated Buildings', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('associate_buildings_count', null, ['class' => 'form-control col-sm-10', 'min' => 0, 'placeholder' => 'Enter Number']) !!}
            </div>
        </div>

        <h3 class="mt-3">School Type Information</h3>

        <div class="form-group row required">
            <label class="col-sm-3 control-label">School Types</label>
            <div class="col-sm-5">
                @foreach ($studentLevels as $levelKey => $level)
                    <div class="form-check">
                        {!! Form::checkbox($level['checkbox'], 1, null, [
                            'class' => 'form-check-input school-type-checkbox',
                            'id' => $level['checkbox'],
                            'data-target' => '#student-' . $levelKey,
                        ]) !!}
                        {!! Form::label($level['checkbox'], $level['label'], ['class' => 'form-check-label']) !!}
                    </div>
                @endforeach
            </div>
        </div>

        <h3 class="mt-3">Student Information</h3>

        @foreach ($studentLevels as $levelKey => $level)
            <div class="form-group row school-student-row" id="student-{{ $levelKey }}" style="display:none">
                <label class="col-sm-3 control-label">{{ $level['label'] }}</label>
                <div class="col-sm-5">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            {!! Form::label($level['fields']['girls'], 'Female') !!}
                            {!! Form::number($level['fields']['girls'], null, ['class' => 'form-control student-count', 'min' => 0, 'placeholder' => 'Female']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label($level['fields']['boys'], 'Male') !!}
                            {!! Form::number($level['fields']['boys'], null, ['class' => 'form-control student-count', 'min' => 0, 'placeholder' => 'Male']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label($level['fields']['other'], 'Other') !!}
                            {!! Form::number($level['fields']['other'], null, ['class' => 'form-control student-count', 'min' => 0, 'placeholder' => 'Other']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label($level['fields']['total'], 'Total') !!}
                            {!! Form::number($level['fields']['total'], null, ['class' => 'form-control row-total student-level-total', 'min' => 0, 'placeholder' => 'Total', 'readonly' => true]) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="form-group row">
            <label class="col-sm-3 control-label">Total Students</label>
            <div class="col-sm-5">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        {!! Form::label('total_girls', 'Female') !!}
                        {!! Form::number('total_girls', null, ['class' => 'form-control', 'min' => 0, 'placeholder' => 'Total Female', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('total_boys', 'Male') !!}
                        {!! Form::number('total_boys', null, ['class' => 'form-control', 'min' => 0, 'placeholder' => 'Total Male', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('total_other', 'Other') !!}
                        {!! Form::number('total_other', null, ['class' => 'form-control', 'min' => 0, 'placeholder' => 'Total Other', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('total_students', 'Total') !!}
                        {!! Form::number('total_students', null, ['class' => 'form-control', 'min' => 0, 'placeholder' => 'Grand Total', 'readonly' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-3">Staff Information</h3>

        <div class="form-group row">
            <label class="col-sm-3 control-label">Teachers</label>
            <div class="col-sm-5">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        {!! Form::label('teachers_male', 'Male') !!}
                        {!! Form::number('teachers_male', null, ['class' => 'form-control teacher-count', 'min' => 0]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('teachers_female', 'Female') !!}
                        {!! Form::number('teachers_female', null, ['class' => 'form-control teacher-count', 'min' => 0]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('teachers_other', 'Other') !!}
                        {!! Form::number('teachers_other', null, ['class' => 'form-control teacher-count', 'min' => 0]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('teachers_total', 'Total') !!}
                        {!! Form::number('teachers_total', null, ['class' => 'form-control', 'min' => 0, 'readonly' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-3">Sanitation Facility Information</h3>

        @foreach ($facilityGroups as $groupLabel => $rows)
            <h5 class="mt-3">{{ $groupLabel }}</h5>
            @foreach ($rows as $row)
                <div class="form-group row facility-count-row">
                    <label class="col-sm-3 control-label">{{ $row['label'] }}</label>
                    <div class="col-sm-5">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                {!! Form::label($row['fields']['male'], 'Male') !!}
                                {!! Form::number($row['fields']['male'], null, ['class' => 'form-control facility-count', 'min' => 0, 'placeholder' => 'Male']) !!}
                            </div>
                            <div class="form-group col-md-3">
                                {!! Form::label($row['fields']['female'], 'Female') !!}
                                {!! Form::number($row['fields']['female'], null, ['class' => 'form-control facility-count', 'min' => 0, 'placeholder' => 'Female']) !!}
                            </div>
                            <div class="form-group col-md-3">
                                {!! Form::label($row['fields']['other'], 'Other') !!}
                                {!! Form::number($row['fields']['other'], null, ['class' => 'form-control facility-count', 'min' => 0, 'placeholder' => 'Other']) !!}
                            </div>
                            <div class="form-group col-md-3">
                                {!! Form::label($row['fields']['total'], 'Total') !!}
                                {!! Form::number($row['fields']['total'], null, ['class' => 'form-control row-total', 'min' => 0, 'placeholder' => 'Total', 'readonly' => true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        <div class="form-group row">
            {!! Form::label('universal_design_toilet_count', 'No. of Universal Design Toilets', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('universal_design_toilet_count', null, ['class' => 'form-control col-sm-10', 'min' => 0, 'placeholder' => 'Number of Universal Design Toilets']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('main_toilet_type', 'Main Type of Toilet', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('main_toilet_type', [
                    'Flush / Pour-flush Toilet' => 'Flush / Pour-flush Toilet',
                    'Pit Latrine' => 'Pit Latrine',
                    'Composting Toilet' => 'Composting Toilet',
                    'Other' => 'Other'
                ], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select Main Toilet Type']) !!}
            </div>
        </div>

        <div class="form-group row" id="school-toilet-connection">
            {!! Form::label('toilet_connection', 'Toilet Connection', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('toilet_connection', [
                    'Septic Tank' => 'Septic Tank',
                    'Sewer Network' => 'Sewer Network',
                    'Drain Network' => 'Drain Network',
                    'Onsite Treatment (biogas)' => 'Onsite Treatment (biogas)',
                    'Pit / Holding' => 'Pit / Holding'
                ], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select Toilet Connection']) !!}
            </div>
        </div>

        <div class="form-group row" id="septic-outlet-row" style="display:none">
            {!! Form::label('septic_outlet', 'Septic Outlet Connection', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('septic_outlet', [
                    'Without Outlet Connection' => 'Without Outlet Connection',
                    'Connected to Sewer Network' => 'Connected to Sewer Network',
                    'Connected to Drain Network' => 'Connected to Drain Network',
                    'Connected to Soak Pit' => 'Connected to Soak Pit',
                    'Connected to Water Body' => 'Connected to Water Body'
                ], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select Septic Outlet Connection']) !!}
            </div>
        </div>

        <div class="form-group row" id="pit-outlet-row" style="display:none">
            {!! Form::label('pit_outlet', 'Pit / Holding Outlet Connection', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('pit_outlet', [
                    'Without Outlet Connection' => 'Without Outlet Connection',
                    'Connected to Sewer Network' => 'Connected to Sewer Network',
                    'Connected to Drain Network' => 'Connected to Drain Network',
                    'Connected to Soak Pit' => 'Connected to Soak Pit',
                    'Connected to Water Body' => 'Connected to Water Body'
                ], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select Pit Outlet Connection']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('soap_and_water_available', 'Soap and Water Available for Handwashing', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('soap_and_water_available', [1 => 'Yes', 0 => 'No'], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select']) !!}
            </div>
        </div>

        <h3 class="mt-3">Water Source Information</h3>

        <div class="form-group row">
            {!! Form::label('main_drinking_water_source', 'Main Water Source for Drinking', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('main_drinking_water_source', [
                    'Piped / Municipal / Community Water' => 'Piped / Municipal / Community Water',
                    'Jar Water' => 'Jar Water',
                    'Tanker Water' => 'Tanker Water',
                    'Deep Boring' => 'Deep Boring',
                    'Protected Dug Well' => 'Protected Dug Well',
                    'Other' => 'Other'
                ], null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Select Main Water Source']) !!}
            </div>
        </div>

        <h3 class="mt-3">Registration Information</h3>

        <div class="form-group row">
            {!! Form::label('last_registration_renewal_date', 'Last Registration Renewal Date', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::date('last_registration_renewal_date', null, ['class' => 'form-control date col-sm-10', 'max' => now()->format('Y-m-d'), 'onclick' => 'this.showPicker();']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('certificate_picture_url', 'Picture of Certificate', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                @if ($school->certificate_picture_url)
                    <p class="mb-2">
                        <a href="{{ asset('storage/' . $school->certificate_picture_url) }}" target="_blank">View Current Certificate</a>
                    </p>
                @endif
                {!! Form::file('certificate_picture_url', ['class' => 'form-control col-sm-10']) !!}
            </div>
        </div>
    </div>

    <div class="card-footer">
        <a href="{{ route('education.school.index') }}" class="btn btn-info">Back to List</a>
        {!! Form::submit('Update', ['class' => 'btn btn-info prevent-multiple-submits']) !!}
    </div>
    {!! Form::close() !!}
</div>
@stop

@push('scripts')
<script>
    $(function () {
        $('#bin_select').select2({
            placeholder: 'House Number / BIN',
            allowClear: true,
            width: '85%'
        });

        function numberValue(field) {
            return parseInt($(field).val(), 10) || 0;
        }

        function resetInputs(container) {
            $(container).find('input[type="number"]').val(0);
            $(container).find('select').val('').trigger('change.select2');
        }

        function updateRowTotal(row) {
            var total = 0;
            $(row).find('input[type="number"]:not(.row-total)').each(function () {
                total += numberValue(this);
            });
            $(row).find('.row-total').val(total);
        }

        function updateStudentTotals() {
            var girls = 0;
            var boys = 0;
            var other = 0;
            var total = 0;

            $('.school-student-row:visible').each(function () {
                girls += numberValue($(this).find('input[name$="_girls_count"]'));
                boys += numberValue($(this).find('input[name$="_boys_count"]'));
                other += numberValue($(this).find('input[name$="_other_count"]'));
                total += numberValue($(this).find('.student-level-total'));
            });

            $('input[name="total_girls"]').val(girls);
            $('input[name="total_boys"]').val(boys);
            $('input[name="total_other"]').val(other);
            $('input[name="total_students"]').val(total);
        }

        function updateTeachersTotal() {
            var total = numberValue('input[name="teachers_male"]') +
                numberValue('input[name="teachers_female"]') +
                numberValue('input[name="teachers_other"]');
            $('input[name="teachers_total"]').val(total);
        }

        function handleSchoolTypeSkipLogic() {
            $('.school-type-checkbox').each(function () {
                var target = $(this).data('target');
                if ($(this).is(':checked')) {
                    $(target).show();
                } else {
                    resetInputs(target);
                    $(target).hide();
                }
            });
            $('.school-student-row').each(function () {
                updateRowTotal(this);
            });
            updateStudentTotals();
        }

        function handleToiletConnectionSkipLogic() {
            var connection = $('select[name="toilet_connection"]').val();

            if (connection === 'Septic Tank') {
                $('#septic-outlet-row').show();
            } else {
                resetInputs('#septic-outlet-row');
                $('#septic-outlet-row').hide();
            }

            if (connection === 'Pit / Holding') {
                $('#pit-outlet-row').show();
            } else {
                resetInputs('#pit-outlet-row');
                $('#pit-outlet-row').hide();
            }
        }

        $('.school-type-checkbox').on('change', handleSchoolTypeSkipLogic);
        $('select[name="toilet_connection"]').on('change', handleToiletConnectionSkipLogic);

        $('.school-student-row').on('input', '.student-count', function () {
            updateRowTotal($(this).closest('.school-student-row'));
            updateStudentTotals();
        });

        $('.facility-count-row').on('input', '.facility-count', function () {
            updateRowTotal($(this).closest('.facility-count-row'));
        });

        $('.teacher-count').on('input', updateTeachersTotal);

        handleSchoolTypeSkipLogic();
        handleToiletConnectionSkipLogic();
        updateTeachersTotal();
        $('.facility-count-row').each(function () {
            updateRowTotal(this);
        });
    });
</script>
@endpush
