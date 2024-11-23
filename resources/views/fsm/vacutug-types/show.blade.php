<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\VacutugTypeController@index') }}" class="btn btn-info">Back to List</a>

    </div><!-- /.card-header -->
    <div class="form-horizontal">

        <div class="card-body">
        <div class="form-group row ">
            {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
            {!! Form::label(null,$serviceProviders,['class' => 'form-control']) !!}
            </div>
        </div>

    <div class="form-group row ">
        {!! Form::label('license_plate_number','Vehicle License Plate Number ',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$vacutugType->license_plate_number,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('capacity','Capacity (m³)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$vacutugType->capacity,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('width','Width (m)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$vacutugType->width,['class' => 'form-control']) !!}

        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('comply_with_maintainance_standards','Comply with Maintenance Standards',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$vacutugComplyMaintainStandard,['class' => 'form-control']) !!}

        </div>
    </div>
  
    <div class="form-group row">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$status,['class' => 'form-control']) !!}

        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('description','Description',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::label(null,$vacutugType->description,['class' => 'form-control']) !!}

        </div>
    </div>
</div><!-- /.card-body -->


@stop

