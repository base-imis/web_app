<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent ">
        <a href="{{ action('Fsm\SludgeCollectionController@index') }}" class="btn btn-info">Back to List</a>
        
    </div>
  
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('Application ID',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->application_id,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('Treatment Plant Name',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->treatment_plant_id ? $sludgeCollection->treatmentplants()->withTrashed()->first()->name : null,['class' => 'form-control']) !!}
                </div>
            </div>
            <!-- <div class="form-group row">
                {!! Form::label('application_ID',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->application_id ? $sludgeCollection->application_id : null,['class' => 'form-control']) !!}
                </div>
            </div> -->
            <div class="form-group row">
                {!! Form::label('volume_of_sludge', 'Sludge Volume (m³)',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                {!! Form::label('volume_of_sludge',  $sludgeCollection->emptying->volume_of_sludge, [ 'class' => 'form-control']) !!}
                </div>
            </div>
             <div class="form-group row ">
                {!! Form::label('date','Date',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->date,['class' => 'form-control']) !!}
                </div>
            </div>
             <div class="form-group row ">
                {!! Form::label('date','Entry Time',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->entry_time? date('h:i A', strtotime($sludgeCollection->entry_time)) : null,['class' => 'form-control']) !!}
                </div>
            </div>
           
            <div class="form-group row ">
                {!! Form::label('date','Exit Time',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$sludgeCollection->exit_time? date('h:i A', strtotime($sludgeCollection->exit_time)) : null,['class' => 'form-control']) !!}
                </div>
            </div>
            

            

            <div class="form-group row">
                {!! Form::label('vacutug_id', 'Desludging Vehicle Number Plate',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label('vacutug_id',  $sludgeCollection->emptying->vacutug()->withTrashed()->first()->license_plate_number, [ 'class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('service_provider_id', 'Service Provider Name',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label('service_provider_id',  $sludgeCollection->applications->service_provider()->withTrashed()->first()->company_name??'Not Assigned', [ 'class' => 'form-control']) !!}
                </div>
            </div>


        </div><!-- /.card-body -->
          
        
    </div>
</div><!-- /.card -->
@stop

