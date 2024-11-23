{{-- Last Modified Date: 14-04-2024
 Developed By: Innovative Solution Pvt. Ltd. (ISPL)   --}}
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\HotspotController@index') }}" class="btn btn-info">Back to List</a>

    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('name','Hotspot Location',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->hotspot_location,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('disease','Infected Disease',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,ucwords($diseaseName),['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(null,'Date',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->date,['class' => 'form-control']) !!}
                </div>
            </div>
            <h3>No. of Cases</h3>
           <div class="form-group row">
                {!! Form::label(null,'Male',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->male_cases,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(null,'Female',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->female_cases,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(null,'Other',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->other_cases,['class' => 'form-control']) !!}
                </div>
            </div>

            <h3>No. of Fatalities</h3>
            <div class="form-group row">
                 {!! Form::label(null,'Male',['class' => 'col-sm-3 control-label']) !!}
                 <div class="col-sm-6">
                     {!! Form::label(null,$Hotspots->male_fatalities,['class' => 'form-control']) !!}
                 </div>
             </div>

             <div class="form-group row">
                 {!! Form::label(null,'Female',['class' => 'col-sm-3 control-label']) !!}
                 <div class="col-sm-6">
                     {!! Form::label(null,$Hotspots->female_fatalities,['class' => 'form-control']) !!}
                 </div>
             </div>

             <div class="form-group row">
                 {!! Form::label(null,'Other',['class' => 'col-sm-3 control-label']) !!}
                 <div class="col-sm-6">
                     {!! Form::label(null,$Hotspots->other_fatalities,['class' => 'form-control']) !!}
                 </div>
             </div>



            <div class="form-group row">
                {!! Form::label(null,'Hotspot Area',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <button type="button" class="btn btn-outline-dark btn-icon" data-toggle="modal" data-target="#hotspot-viewer" onclick="null"><i class="fa fa-eye" style="color: grey"></i></button>
                </div>
                @include('fsm.hotspots.hotspotViewer',[$Hotspots,$geom,$lat,$long])
            </div>
            <div class="form-group row">
                {!! Form::label(null,'Notes',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$Hotspots->notes,['class' => 'form-control']) !!}
                </div>
            </div>


        </div><!-- /.card-body -->
    </div>
</div><!-- /.card -->
@stop

