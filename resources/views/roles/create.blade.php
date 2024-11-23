@extends('layouts.dashboard')
@section('title', 'Create Role')
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
	</div><!-- /.box-header -->
    <div class="card">
      {{ Form::open([ 'action' => 'Auth\RoleController@store','class' => 'form-horizontal' ]) }}
      <div class="card-body">
        @include('roles.form')
      </div>
      <div class="card-footer">
        <a href="{{ action('Auth\RoleController@index') }}" class="btn btn-info">Back to List</a>
        <button class="btn btn-info" type="submit">Save</button>
      </div>
      {{ Form::close() }}
    </div>
</div>
@endsection
