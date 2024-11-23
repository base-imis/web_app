{{-- Last Modified Date: 14-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   --}}
@extends('layouts.dashboard')

@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent ">
    <a href="{{ action('Fsm\EmployeeInfoController@index') }}" class="btn btn-info">Back to List</a>
    </div><!-- /.box-header -->
    <div class="form-horizontal">
        <div class="card-body">
        <div class="form-group row">
    {!! Form::label('service_provider_id', 'Service Provider Name', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $service_provider_id, ['class' => 'form-control']) !!}
    </div>
</div>

    <div class="form-group row ">
        {!! Form::label('name','Employee Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$employeeInfos->name,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('gender','Employee Gender',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->gender,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('contact_number','Employee Contact Number',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->contact_number,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('dob','Date of Birth',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->dob,['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('Address',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->address,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('employee_type', 'Designation',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->employee_type,['class' => 'form-control']) !!}

        </div>
    </div>

    <div  class="form-group row" >
    {!! Form::label('year_of_experience','Working Experience (Years)',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
    {!! Form::label(null,$employeeInfos->year_of_experience,['class' => 'form-control']) !!}
        </div>
        </div>

    <div class="form-group row ">
        {!! Form::label('wage','Monthly Remuneration',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->wage,['class' => 'form-control']) !!}
        </div>
    </div>

    <div id="license_number" class="form-group row ">
        {!! Form::label('Driving License Number',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->license_number,['class' => 'form-control']) !!}
        </div>
    </div>
    <div id="license_issue_date" class="form-group row " >
        {!! Form::label('License Issue Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->license_issue_date,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('Training Received (if any)',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->training_status,['class' => 'form-control']) !!}

        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$status,['class' => 'form-control']) !!}

    </div>
    </div>
    <div class="form-group row  ">
        {!! Form::label('Job Start Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 ">
        {!! Form::label(null,$employeeInfos->employment_start,['class' => 'form-control']) !!}

        </div>
    </div>
  
    <div id="employment_end" class="form-group row">
        {!! Form::label('Job End Date',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$employeeInfos->employment_end,['class' => 'form-control']) !!}

            </div>
    </div>

        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop
