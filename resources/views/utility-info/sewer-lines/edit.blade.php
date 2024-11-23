@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($sewerLine, ['method' => 'PATCH', 'action' => ['UtilityInfo\SewerLineController@update', $sewerLine->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/sewer-lines.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
