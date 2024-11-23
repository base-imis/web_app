<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', 'Create User')
{{--Include the layout inside the main content section--}}
@section('content')
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'auth/users', 'class' => 'form-horizontal']) !!}
	@hasanyrole('Super Admin|Municipality - Admin|Municipality - Sanitation Department|Municipality - IT Admin')
		@include('users.partial-form', ['submitButtomText' => 'Save'])
	@endhasanyrole
	@hasrole('Service Provider - Admin')
		@include('users.partial-form-service-provider', ['submitButtomText' => 'Save'])
	@endhasrole
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
