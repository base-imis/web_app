<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
An Edit Layout for all forms
--}}
{{--Extend the main layout--}}
@extends('layouts.dashboard')
{{--Add sections for the main layout--}}
@section('title', $page_title)
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
    <div class="card card-info">
        @include('layouts.components.error-list')
        @include('layouts.components.success-alert')
        @include('layouts.components.error-alert')
        <div class="card-body">
        {!! Form::open(['url' => $formAction, 'class' => 'form-horizontal','method'=>'PATCH','files'=>true]) !!}
            @include('layouts.partial-form', ['submitButtonText' => 'Save'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection
