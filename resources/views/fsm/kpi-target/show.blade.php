@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
  
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('indicator_id','Indicator',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label('indicator_id',$indicators, ['class' => 'form-control']) !!}
                </div>
            </div>
           
            <div class="form-group row">
                {!! Form::label(null,'Year',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$kpi->year,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(null,'Target (%)',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$kpi->target,['class' => 'form-control']) !!}
                </div>
            </div>
         
        </div><!-- /.card-body -->
    </div>
    <div class="card-footer">
    <a href="{{ action('Fsm\KpiTargetController@index') }}" class="btn btn-info">Back to List</a>
</div>
</div><!-- /.card -->


@stop

