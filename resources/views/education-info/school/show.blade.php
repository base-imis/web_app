@extends('layouts.dashboard')
@section('title', 'School Details')

@section('content')
@php
    $readonlyClass = 'form-control col-sm-10 font-weight-bold';

    $studentLevels = [
        'pre_primary' => 'Pre-primary',
        'basic_1_5' => 'Basic (1 - 5)',
        'basic_6_8' => 'Basic (6 - 8)',
        'secondary_9_10' => 'Secondary (9 - 10)',
        'secondary_9_12' => 'Secondary (9 - 12)',
    ];

    $schoolTypes = [
        'school_type_pre_primary' => 'Pre-primary',
        'school_type_basic_1_5' => 'Basic 1-5',
        'school_type_basic_6_8' => 'Basic 6-8',
        'school_type_secondary_9_10' => 'Secondary 9-10',
        'school_type_secondary_9_12' => 'Secondary 9-12',
    ];

    $facilityGroups = [
        'Toilets' => [
            ['label' => 'Teacher Staff', 'prefix' => 'toilet_teacher'],
            ['label' => 'Students', 'prefix' => 'toilet_student'],
        ],
        'Urinals' => [
            ['label' => 'Teacher Staff', 'prefix' => 'urinal_teacher'],
            ['label' => 'Students', 'prefix' => 'urinal_student'],
        ],
        'Hand Washing Units' => [
            ['label' => 'Teacher Staff', 'prefix' => 'handwash_teacher'],
            ['label' => 'Students', 'prefix' => 'handwash_student'],
        ],
    ];

    $dateValue = $school->last_registration_renewal_date
        ? \Carbon\Carbon::parse($school->last_registration_renewal_date)->format('Y-m-d')
        : '';
@endphp

<div class="card card-info">
    <div class="card-body" style="font-family: 'Open Sans', sans-serif;">
        {!! Form::open(['class' => 'form-horizontal']) !!}

        <h3 class="mt-4">School Information</h3>

        <div class="form-group row">
            {!! Form::label('custom_school_id', 'School ID', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('custom_school_id', $school->custom_school_id, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('name', 'School Name', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('name', $school->name, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('headteacher_name', 'Headteacher Name', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('headteacher_name', $school->headteacher_name, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('contact_person_name', 'Contact Person', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('contact_person_name', $school->contact_person_name, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('contact_person_number', 'Contact Number', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('contact_person_number', $school->contact_person_number, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('bin', 'House Number / BIN', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('bin', $school->bin, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('ward_no', 'Ward Number', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('ward_no', $school->ward_no, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('location_name', 'Location', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('location_name', $school->location_name, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <h3 class="mt-3">Building Information</h3>

        <div class="form-group row">
            {!! Form::label('main_building_structure_type', 'Main Building Structure Type', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('main_building_structure_type', $school->main_building_structure_type, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('main_building_floors', 'Main Building Floors', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('main_building_floors', $school->main_building_floors, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('associate_buildings_count', 'Number of Associated Buildings', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('associate_buildings_count', $school->associate_buildings_count, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <h3 class="mt-3">School Type Information</h3>

        <div class="form-group row">
            <label class="col-sm-3 control-label">School Types</label>
            <div class="col-sm-5">
                @foreach ($schoolTypes as $field => $label)
                    @if ($school->{$field})
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" checked disabled>
                            <label class="form-check-label">{{ $label }}</label>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <h3 class="mt-3">Student Information</h3>

        @foreach ($studentLevels as $level => $label)
            @if ($school->{'school_type_' . $level} || $school->{$level . '_total_count'})
                <div class="form-group row">
                    <label class="col-sm-3 control-label">{{ $label }}</label>
                    <div class="col-sm-5">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Female</label>
                                {!! Form::text($level . '_girls_count', $school->{$level . '_girls_count'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Male</label>
                                {!! Form::text($level . '_boys_count', $school->{$level . '_boys_count'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Other</label>
                                {!! Form::text($level . '_other_count', $school->{$level . '_other_count'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Total</label>
                                {!! Form::text($level . '_total_count', $school->{$level . '_total_count'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="form-group row">
            <label class="col-sm-3 control-label">Total Students</label>
            <div class="col-sm-5">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Female</label>
                        {!! Form::text('total_girls', $school->total_girls, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Male</label>
                        {!! Form::text('total_boys', $school->total_boys, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Other</label>
                        {!! Form::text('total_other', $school->total_other, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Total</label>
                        {!! Form::text('total_students', $school->total_students, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-3">Staff Information</h3>

        @foreach (['teachers' => 'Teachers', 'support_staff' => 'Support Staff'] as $prefix => $label)
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ $label }}</label>
                <div class="col-sm-5">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Male</label>
                            {!! Form::text($prefix . '_male', $school->{$prefix . '_male'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                        </div>
                        <div class="form-group col-md-3">
                            <label>Female</label>
                            {!! Form::text($prefix . '_female', $school->{$prefix . '_female'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                        </div>
                        <div class="form-group col-md-3">
                            <label>Other</label>
                            {!! Form::text($prefix . '_other', $school->{$prefix . '_other'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                        </div>
                        <div class="form-group col-md-3">
                            <label>Total</label>
                            {!! Form::text($prefix . '_total', $school->{$prefix . '_total'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <h3 class="mt-3">Sanitation Facility Information</h3>

        @foreach ($facilityGroups as $groupLabel => $rows)
            <h5 class="mt-3">{{ $groupLabel }}</h5>
            @foreach ($rows as $row)
                <div class="form-group row">
                    <label class="col-sm-3 control-label">{{ $row['label'] }}</label>
                    <div class="col-sm-5">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Male</label>
                                {!! Form::text($row['prefix'] . '_male', $school->{$row['prefix'] . '_male'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Female</label>
                                {!! Form::text($row['prefix'] . '_female', $school->{$row['prefix'] . '_female'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Other</label>
                                {!! Form::text($row['prefix'] . '_other', $school->{$row['prefix'] . '_other'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                            <div class="form-group col-md-3">
                                <label>Total</label>
                                {!! Form::text($row['prefix'] . '_total', $school->{$row['prefix'] . '_total'}, ['class' => 'form-control font-weight-bold', 'readonly' => true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        <div class="form-group row">
            {!! Form::label('universal_design_toilet_count', 'No. of Universal Design Toilets', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('universal_design_toilet_count', $school->universal_design_toilet_count, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('main_toilet_type', 'Main Type of Toilet', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('main_toilet_type', $school->main_toilet_type, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('toilet_connection', 'Toilet Connection', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('toilet_connection', $school->toilet_connection, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        @if ($school->toilet_connection === 'Septic Tank')
            <div class="form-group row">
                {!! Form::label('septic_outlet', 'Septic Outlet Connection', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-5">
                    {!! Form::text('septic_outlet', $school->septic_outlet, ['class' => $readonlyClass, 'readonly' => true]) !!}
                </div>
            </div>
        @endif

        @if ($school->toilet_connection === 'Pit / Holding')
            <div class="form-group row">
                {!! Form::label('pit_outlet', 'Pit / Holding Outlet Connection', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-5">
                    {!! Form::text('pit_outlet', $school->pit_outlet, ['class' => $readonlyClass, 'readonly' => true]) !!}
                </div>
            </div>
        @endif

        <div class="form-group row">
            {!! Form::label('soap_and_water_available', 'Soap and Water Available for Handwashing', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('soap_and_water_available', $school->soap_and_water_available ? 'Yes' : 'No', ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <h3 class="mt-3">Water Source Information</h3>

        <div class="form-group row">
            {!! Form::label('main_drinking_water_source', 'Main Water Source for Drinking', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('main_drinking_water_source', $school->main_drinking_water_source, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <h3 class="mt-3">Registration Information</h3>

        <div class="form-group row">
            {!! Form::label('last_registration_renewal_date', 'Last Registration Renewal Date', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('last_registration_renewal_date', $dateValue, ['class' => $readonlyClass, 'readonly' => true]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('certificate_picture_url', 'Picture of Certificate', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                @if ($school->certificate_picture_url)
                    <a href="{{ asset('storage/' . $school->certificate_picture_url) }}" target="_blank" class="btn btn-info btn-sm">View Certificate</a>
                @else
                    {!! Form::text('certificate_picture_url', '', ['class' => $readonlyClass, 'readonly' => true]) !!}
                @endif
            </div>
        </div>

        {!! Form::close() !!}
    </div>

    <div class="card-footer">
        <a href="{{ route('education.school.index') }}" class="btn btn-info">Back to List</a>
        <a href="{{ route('education.school.edit', ['id' => $school->custom_school_id]) }}" class="btn btn-info">Edit</a>
    </div>
</div>
@stop
