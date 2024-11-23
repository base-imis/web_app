@extends('layouts.dashboard')
@section('title',$page_title)
@section('content')
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($drain, ['method' => 'PATCH', 'action' => ['UtilityInfo\DrainController@update', $drain->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/drains.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
