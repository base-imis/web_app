@extends('layouts.app')

@section('content')
    <div class="alert alert-warning mb-3" role="alert">
        {{ __('Too many attempts. Please wait :seconds seconds and try again.', ['seconds' => $retryAfter]) }}
    </div>

    <a class="btn btn-primary btn-block" href="{{ url()->previous() }}">
        {{ __('Go Back') }}
    </a>
@endsection
