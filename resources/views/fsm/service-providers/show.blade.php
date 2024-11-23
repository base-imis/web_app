<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ action('Fsm\ServiceProviderController@index') }}" class="btn btn-info">Back to List</a>
		{{-- <a href="{{ action('Fsm\ServiceProviderController@create') }}" class="btn btn-info">Create new Service Provider</a> --}}
	</div><!-- /.card-header -->
	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('company_name','Company Name',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->company_name,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
			{!! Form::label('email','Email',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->email,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
			{!! Form::label('ward','Ward Number',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->ward,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('company_location','Address',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->company_location,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('contact_person','Contact Person Name',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->contact_person,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('contact_gender','Contact Person Gender',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
				{!! Form::label(null,$serviceProvider->contact_gender,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('contact_number','Contact Person Number',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$serviceProvider->contact_number,['class' => 'form-control']) !!}
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

