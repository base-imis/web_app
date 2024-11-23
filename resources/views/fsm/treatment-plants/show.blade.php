<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\TreatmentPlantController@index') }}" class="btn btn-info">Back to List</a>
    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('name',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->name,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('location',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->location,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('type','Treatment Plant Type',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$type,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('capacity_per_day (m³)',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->capacity_per_day,['class' => 'form-control']) !!}
                </div>
            </div>

           

            <div class="form-group row">
                {!! Form::label('caretaker_name','Caretaker Name',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->caretaker_name,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('caretaker_gender','Caretaker Gender',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->caretaker_gender,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('caretaker_number','Caretaker  Number',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->caretaker_number,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                        {!! Form::label(null,$status,['class' => 'form-control']) !!}
                </div>
            </div>
        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop

