@extends('layouts.dashboard')
@section('title', 'Road Details')
@section('content')
<div class="card card-info">
    
    <div class="card-header bg-transparent ">
        <a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="btn btn-info">Back to List</a>
		{{-- <a href="{{ action('UtilityInfo\RoadlineController@create') }}" class="btn btn-info">Create new Roadline</a> --}}
    </div>
	
	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('roadcd','Code',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->roadcd,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('roadnam','Road Name',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->roadnam,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('roadhier',' Hierarchy',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->roadhier,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('rdsurf','Surface Type',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->rdsurf,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('rdlen','Length (m)',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->rdlen,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('rdwidth','Width (m)',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->rdwidth,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('rdcarwdth','Carrying Width  (m)',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->rdcarwdth,['class' => 'form-control']) !!}
				</div>
			</div>
		</div><!-- /.box-body -->
	</div>
<div class="card-footer">
    
</div>
</div><!-- /.box -->
@stop

