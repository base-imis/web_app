<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
@if(!$employeeInfos)
        @if(!Auth::user()->service_provider_id)
        <div class="form-group row">
            {!! Form::label('service_provider_id','Service Provider Name',['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('service_provider_id', $service_provider_id, null, ['class' => 'form-control', 'placeholder' => 'Service Provider Name']) !!}
            </div>
        </div>
        @else
        <div class="form-group row required">
            {!! Form::label('service_provider_id','Service Provider Name',['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('service_providers', $service_providers, $service_provider_id, ['class' => 'form-control ','disabled' => 'true','placeholder' => 'Service Provider Name']) !!}
                {!! Form::text('service_provider_id', $service_provider_id, ['class' => 'form-control ', 'hidden' => 'hidden', 'placeholder' => 'Employee Type']) !!}

            </div>
        </div>
        @endif
    @else
    <div class="form-group row">
        {!! Form::label('service_provider_id','Service Provider Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $service_provider_id, null, ['class' => 'form-control', 'placeholder' => 'Service Provider Name']) !!}
        </div>
    </div>
    @endif

    <div class="form-group row required">
        {!! Form::label('name','Employee Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Employee Name']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('gender','Employee Gender',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('gender', ['Male' => 'Male', 'Female' => 'Female' , 'Others' => 'Others'], null, ['class' => 'form-control', 'placeholder' => 'Employee Gender']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('contact_number','Employee Contact Number',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::tel('contact_number',null,['class' => 'form-control', 'placeholder' => 'Employee Contact Number']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('dob','Date of Birth',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::date('dob',null,['class' => 'form-control date','id'=>'dob', 'placeholder' => 'Date of birth']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('address','Address',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('address',null,['class' => 'form-control', 'placeholder' => 'Address']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('employee_type', 'Designation',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('employee_type', [ 'Management' => 'Management', 'Driver' => 'Driver', 'Cleaner/Emptier' => 'Cleaner/Emptier'], null, ['class' => 'form-control', 'id' => 'employee_type' ,'placeholder' => 'Designation']) !!}

        </div>
    </div>

    <div  class="form-group row" >
    {!! Form::label('Working Experience (Years)',null,['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('year_of_experience',null,['class' => 'form-control year_of_experience', 'placeholder' => 'Working Experience (Years)']) !!}
    </div>
</div>

    <div class="form-group row">
        {!! Form::label('wage','Monthly Remuneration',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('wage',null,['class' => 'form-control', 'placeholder' => 'Monthly Remuneration']) !!}
        </div>
    </div>
    <div id="license_number" class="form-group row required" style="display: none;">
        {!! Form::label('Driving License Number',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('license_number',null,['class' => 'form-control license_number',  'placeholder' => 'Driving License Number']) !!}
        </div>
    </div>
    <div id="license_issue_date" class="form-group row required" style="display: none;">
        {!! Form::label('License Issue Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::date('license_issue_date',null,['class' => 'form-control license_issue_date',  'placeholder' => 'License Issue Date']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('Training Received (if any)',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('training_status',null,['class' => 'form-control', 'placeholder' => 'Training Received']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control chosen-select', 'id' => 'status' ,'placeholder' => 'Status']) !!}
        </div>
    </div>
    @if(isset($start))
    <div class="form-group row  required">

        {!! Form::label('Job Start Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 ">

            {!! Form::text('employment_start', $start,  ['class' => 'form-control date ',  'id'=>'employment_start', 'placeholder' => 'Job Start Date']) !!}
        </div>
    </div>
@else
    <div class="form-group row  required">
        {!! Form::label('Job Start Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 ">

            {!! Form::text('employment_start', null, ['class' => 'form-control date ',  'id'=>'employment_start', 'placeholder' => 'Job Start Date']) !!}
        </div>
    </div>
@endif


 
    @if(isset($end))
    <div id="employment" class="form-group row" style="display: none;">
        {!! Form::label('Job End Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('employment_end', $end,['class' => 'form-control date ', 'id'=>'employment_end', 'placeholder' => 'Job End Date']) !!}
        </div>
    </div>
    @else
    <div  id="employment" class="form-group row  required"  style="display: none;">
    {!! Form::label('Job End Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 ">
            {!! Form::text('employment_end', null, ['class' => 'form-control date ',  'id'=>'employment_end', 'placeholder' => 'Job End Date']) !!}
        </div>
    </div>
@endif
</div>
<!-- /.card-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\EmployeeInfoController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
@push('scripts')
    <script>

$(document).ready(function() {
    $('#employee_type').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'Driver') {
            $('#license_number').show();
            $('#license_issue_date').show();
        } else {
            $('#license_number').hide();
            $('.license_number').val('');

            $('#license_issue_date').hide();
            $('.license_issue_date').val('');
        }
    }).trigger('change');

    $('#status').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === '0') {
            $('#employment').show();

        } else {
            $('#employment_end').val('');
            $('#employment').hide();


        }
    }).trigger('change');

    $('.date').focus(function(){
        $(this).blur();


        });

        });

    </script>
@endpush
