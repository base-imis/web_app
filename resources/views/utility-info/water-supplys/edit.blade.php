@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($waterSupplys, ['method' => 'PATCH', 'action' => ['UtilityInfo\WaterSupplysController@update', $waterSupplys->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/water-supplys.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
