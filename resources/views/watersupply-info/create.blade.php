@extends('layouts.dashboard')
@section('title', 'Import Water Supply Information Support System')
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">

	{!! Form::open(['url' => 'watersupply-payment','files'=>true, 'class' => 'form-horizontal']) !!}

        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('Upload Water Supply Information Support System File',null,['class' => 'col-sm-3 control-label', 'style'=>'padding-top:3px;']) !!}
                <div class="col-sm-3">
                    {!! Form::file('excelfile') !!}
                </div>
            </div>
        </div><!-- /.card-body -->
        <div class="card-footer">
            <a href="{{ route('watersupply-payment.index') }}" class="btn btn-info">Back to List</a>
            {!! Form::submit('Upload', ['class' => 'btn btn-info']) !!}
        </div><!-- /.card-footer -->
    {!! Form::close() !!}

</div><!-- /.card -->
@stop





