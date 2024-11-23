<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent ">
		<a href="{{ action('UtilityInfo\WaterSupplysController@index') }}" class="btn btn-info">Back to List</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('code','Code',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->code,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('road_code','Road Code',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->road_code,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('project_name','Project Name',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->project_name,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('type','Type',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->type,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('material_type','Material Type',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->material_type,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('diameter','Diameter (mm)',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->diameter,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('length','Length (m)',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSupplys->length,['class' => 'form-control']) !!}
				</div>
			</div>	
		</div><!-- /.card-body -->
	</div>
	<div class="card-footer">
	</div>
</div><!-- /.box -->
@stop