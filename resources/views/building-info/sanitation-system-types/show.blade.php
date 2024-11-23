@extends('dashboard')
@section('title', $page_title)
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <a href="{{ action('TreatmentPlantController@index') }}" class="btn btn-info">Back to List</a>
        <a href="{{ action('TreatmentPlantController@create') }}" class="btn btn-info">Create new Treatment Plant</a>
    </div><!-- /.box-header -->
    <div class="form-horizontal">
        <div class="box-body">
            <div class="form-group">
                {!! Form::label(null,'Treatment Plant ID',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->trtpltcd,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('name',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->name,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('longitude',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->longitude,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('latitude',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->latitude,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('capacity',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->capacity,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('description',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlant->description,['class' => 'form-control']) !!}
                </div>
            </div>
        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop

