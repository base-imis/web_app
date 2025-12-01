@extends('layouts.dashboard')
@section('title', 'Add School')

@section('content')
@include('layouts.components.error-list')

<div class="card card-info">
    {!! Form::open([
        'url' => route('education.school.store'),
        'id' => 'prevent-multiple-submits',
        'files' => true
    ]) !!}
    <div class="card-body">
            <div class="form-group row required">
                {!! Form::label('name', 'School Name', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('name', null, ['class' => 'form-control col-sm-10 bin', 'placeholder' => 'School Name']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('headteacher_name', 'Headteacher Name', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('headteacher_name', null, ['class' => 'form-control col-sm-10 bin', 'placeholder' => 'Head Teacher Name']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('contact_person_name', 'Contact Person', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('contact_person_name', null, ['class' => 'form-control col-sm-10 bin', 'placeholder' =>'Contact Person Name']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('contact_person_number', 'Contact Number', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('contact_person_number', null, ['class' => 'form-control col-sm-10 bin', 'placeholder'=>'Contact Person Number']) !!}
                </div>
            </div>

            <div class="form-group row required" id="bin">
                {!! Form::label('bin', 'House Number / BIN', ['class' => 'col-sm-3 control-label ']) !!}
                <div class="col-sm-4">
                    {!! Form::select('bin', $bin, null, [
                        'class' => 'form-control col-sm-10',
                        'placeholder' => 'House Number / BIN',
                        'id' => 'bin_select',
                    ]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('ward_no', 'Ward Number', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::number('ward_no', null, ['class' => 'form-control col-sm-10 bin', 'min' => 1, 'Placeholder'=>'Ward']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('location_name', 'Location', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('location_name', null, ['class' => 'form-control col-sm-10 bin','placeholder'=>'Loaction']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('main_building_structure_type', 'Main Building Structure Type', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::text('main_building_structure_type', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Enter Structure Type']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('main_building_floors', 'Main Building Floors', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::number('main_building_floors', null, ['class' => 'form-control col-sm-10', 'min' => 0, 'placeholder' => 'Enter Number of Floors']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('associate_buildings_count', 'Number of Associated Buildings', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-4">
                    {!! Form::number('associate_buildings_count', null, ['class' => 'form-control col-sm-10', 'min' => 0, 'placeholder' => 'Enter Number']) !!}
                </div>
            </div>

            <hr>
            <h5>School Types</h5>
            <div class="form-check">
                {!! Form::checkbox('school_type_pre_primary', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('school_type_pre_primary', 'Pre-Primary') !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('school_type_basic_1_5', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('school_type_basic_1_5', 'Basic 1-5') !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('school_type_basic_6_8', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('school_type_basic_6_8', 'Basic 6-8') !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('school_type_secondary_9_10', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('school_type_secondary_9_10', 'Secondary 9-10') !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('school_type_secondary_9_12', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('school_type_secondary_9_12', 'Secondary 9-12') !!}
            </div>

            <hr>
            <h5>No. of Students</h5>

            <div class="form-group">
                <label>Pre-primary</label>
                <div class="form-row">
                    <div class="col">
                {!! Form::label('pre_primary_girls_count', 'Female', ['class' => 'form-label']) !!}
                {!! Form::number('pre_primary_girls_count', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
            </div>
            <div class="col">
                {!! Form::label('pre_primary_boys_count', 'Male', ['class' => 'form-label']) !!}
                {!! Form::number('pre_primary_boys_count', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
            </div>
            <div class="col">
                {!! Form::label('pre_primary_other_count', 'Other', ['class' => 'form-label']) !!}
                {!! Form::number('pre_primary_other_count', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
            </div>
            <div class="col">
                {!! Form::label('pre_primary_total_count', 'Total', ['class' => 'form-label']) !!}
                {!! Form::number('pre_primary_total_count', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
            </div>
                </div>
            </div>

            <div class="form-group">
                <label>Basic (1 - 5)</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('basic_1_5_girls_count', 0, ['class' => 'form-control', 'placeholder' => 'Girls']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_1_5_boys_count', 0, ['class' => 'form-control', 'placeholder' => 'Boys']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_1_5_other_count', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_1_5_total_count', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Basic (6 - 8)</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('basic_6_8_girls_count', 0, ['class' => 'form-control', 'placeholder' => 'Girls']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_6_8_boys_count', 0, ['class' => 'form-control', 'placeholder' => 'Boys']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_6_8_other_count', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('basic_6_8_total_count', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Secondary (9 - 10)</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('secondary_9_10_girls_count', 0, ['class' => 'form-control', 'placeholder' => 'Girls']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_10_boys_count', 0, ['class' => 'form-control', 'placeholder' => 'Boys']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_10_other_count', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_10_total_count', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Secondary (9 - 12)</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('secondary_9_12_girls_count', 0, ['class' => 'form-control', 'placeholder' => 'Girls']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_12_boys_count', 0, ['class' => 'form-control', 'placeholder' => 'Boys']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_12_other_count', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('secondary_9_12_total_count', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Total Students</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('total_girls', 0, ['class' => 'form-control', 'placeholder' => 'Total Girls']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('total_boys', 0, ['class' => 'form-control', 'placeholder' => 'Total Boys']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('total_other', 0, ['class' => 'form-control', 'placeholder' => 'Total Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('total_students', 0, ['class' => 'form-control', 'placeholder' => 'Grand Total']) !!}
                    </div>
                </div>
            </div>

            <hr>
            <h5>Staff</h5>
            <div class="form-row">
                <div class="form-group col-md-3">
                    {!! Form::label('teachers_male', 'Teachers Male') !!}
                    {!! Form::number('teachers_male', 0, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('teachers_female', 'Teachers Female') !!}
                    {!! Form::number('teachers_female', 0, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('teachers_other', 'Teachers Other') !!}
                    {!! Form::number('teachers_other', 0, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('teachers_total', 'Teachers Total') !!}
                    {!! Form::number('teachers_total', 0, ['class' => 'form-control']) !!}
                </div>
            </div>



        <hr>
        <h5>Number of Toilets / Seats</h5>

        {{--  Toilets --}}
            <h6>Toilets</h6>
            <div class="form-group">
                <label>Teacher Staff</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('toilet_teacher_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_teacher_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_teacher_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_teacher_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Students</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('toilet_student_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_student_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_student_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('toilet_student_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            {{-- 15b Urinals --}}
            <h6>Urinals</h6>
            <div class="form-group">
                <label>Teacher Staff</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('urinal_teacher_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_teacher_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_teacher_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_teacher_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Students</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('urinal_student_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_student_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_student_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('urinal_student_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            {{-- 15c Hand Washing --}}
            <h6>Hand Washing Units</h6>
            <div class="form-group">
                <label>Teacher Staff</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('handwash_teacher_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_teacher_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_teacher_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_teacher_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Students</label>
                <div class="form-row">
                    <div class="col">
                        {!! Form::number('handwash_student_male', 0, ['class' => 'form-control', 'placeholder' => 'Male']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_student_female', 0, ['class' => 'form-control', 'placeholder' => 'Female']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_student_other', 0, ['class' => 'form-control', 'placeholder' => 'Other']) !!}
                    </div>
                    <div class="col">
                        {!! Form::number('handwash_student_total', 0, ['class' => 'form-control', 'placeholder' => 'Total']) !!}
                    </div>
                </div>
            </div>

            <hr>
            <h5>No. of Universal Design Toilets</h5>
            <div class="form-group">
                {!! Form::number('universal_design_toilet_count', 0, ['class' => 'form-control', 'placeholder' => 'Number of Universal Design Toilets']) !!}
            </div>

            <hr>
            <h5>What is the main type of toilet?</h5>
            <div class="form-group">
                {!! Form::select('main_toilet_type', [
                    'Flush / Pour-flush Toilet' => 'Flush / Pour-flush Toilet',
                    'Pit Latrine' => 'Pit Latrine',
                    'Composting Toilet' => 'Composting Toilet',
                    'Other' => 'Other'
                ], null, ['class' => 'form-control', 'placeholder' => 'Select Main Toilet Type']) !!}
            </div>

            <hr>
            <h5>Where is the toilet connected?</h5>
            <div class="form-group">
                {!! Form::select('toilet_connection', [
                    'Septic Tank' => 'Septic Tank',
                    'Sewer Network' => 'Sewer Network',
                    'Drain Network' => 'Drain Network',
                    'Onsite Treatment (biogas)' => 'Onsite Treatment (biogas)',
                    'Pit / Holding' => 'Pit / Holding'
                ], null, ['class' => 'form-control', 'placeholder' => 'Select Toilet Connection']) !!}
            </div>

            <hr>
            <h5>If the toilet connection is septic, what is the outlet connected to?</h5>
            <div class="form-group">
                {!! Form::select('septic_outlet', [
                    'Without Outlet Connection' => 'Without Outlet Connection',
                    'Connected to Sewer Network' => 'Connected to Sewer Network',
                    'Connected to Drain Network' => 'Connected to Drain Network',
                    'Connected to Soak Pit' => 'Connected to Soak Pit',
                    'Connected to Water Body' => 'Connected to Water Body'
                ], null, ['class' => 'form-control', 'placeholder' => 'Select Septic Outlet Connection']) !!}
            </div>

            <hr>
            <h5>If the toilet connection is pit or holding, what is the outlet connected to?</h5>
            <div class="form-group">
                {!! Form::select('pit_outlet', [
                    'Without Outlet Connection' => 'Without Outlet Connection',
                    'Connected to Sewer Network' => 'Connected to Sewer Network',
                    'Connected to Drain Network' => 'Connected to Drain Network',
                    'Connected to Soak Pit' => 'Connected to Soak Pit',
                    'Connected to Water Body' => 'Connected to Water Body'
                ], null, ['class' => 'form-control', 'placeholder' => 'Select Pit Outlet Connection']) !!}
            </div>

            <hr>
            <h5>Are both soap and water currently available for handwashing?</h5>
            <div class="form-group">
                {!! Form::select('soap_and_water_available', [1 => 'Yes', 0 => 'No'], null, ['class' => 'form-control', 'placeholder' => 'Select']) !!}
            </div>

            <hr>
                <h5>What is the main water source for drinking?</h5>
                <div class="form-group">
                    {!! Form::select('main_drinking_water_source', [
                        'Piped / Municipal / Community Water' => 'Piped / Municipal / Community Water',
                        'Jar Water' => 'Jar Water',
                        'Tanker Water' => 'Tanker Water',
                        'Deep Boring' => 'Deep Boring',
                        'Protected Dug Well' => 'Protected Dug Well',
                        'Other' => 'Other'
                    ], null, ['class' => 'form-control', 'placeholder' => 'Select Main Water Source']) !!}
                </div>

            <hr>
            <h5>Last Registration Renewal Date</h5>
            <div class="form-group">
                {!! Form::date('last_registration_renewal_date', null, ['class' => 'form-control']) !!}
            </div>

            <hr>
                <h5>Picture of Certificate</h5>
            <div class="form-group">
                {!! Form::file('certificate_picture_url', ['class' => 'form-control']) !!}
            </div>



        <div class="card-footer">
            {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop

@push('scripts')
<script>
    $(function () {
        $('#bin_select').select2({
            placeholder: 'House Number / BIN',
            allowClear: true,
            width: '85%'   // or '100%' if you want full width
        });
    });
</script>
@endpush
