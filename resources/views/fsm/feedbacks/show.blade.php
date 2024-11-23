@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

<div class="card card-info">
    <div class="card-footer">
        <a href="{{ action('Fsm\FeedbackController@index') }}" class="btn btn-info" >Back to List</a>
    </div>
    <div class="form-horizontal">

        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('application_id','Application ID',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->application_id,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('customer_name','Applicant  Name',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->customer_name,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('customer_gender','Applicant  Gender',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->customer_gender=='Male'?'Male':($feedback->customer_gender=='Female'?'Female':''),['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('phone_number','Applicant Contact Number',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->customer_number,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('fsm_service_quality','Are you satisfied with the Service Quality?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    @if($feedback->fsm_service_quality)
                <label class="radio-inline">
                    {{ Form::radio('fsm_service_quality',true,true,['disabled']) }}  Yes
                </label>
                <label class="radio-inline">
                    {{ Form::radio('fsm_service_quality',false,false,['disabled']) }}  No
                </label>
                    @else
                     <label class="radio-inline">
                    {{ Form::radio('fsm_service_quality',true,false,['disabled']) }}  Yes
                </label>
                <label class="radio-inline">
                    {{ Form::radio('fsm_service_quality',false,true,['disabled']) }}  No
                </label>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('wear_ppe','Did the sanitation workers wear PPE during desludging?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    @if($feedback->wear_ppe)
                        <label class="radio-inline">
                            {{ Form::radio('wear_ppe',true,true,['disabled']) }}  Yes
                        </label>
                        <label class="radio-inline">
                            {{ Form::radio('wear_ppe',false,false,['disabled']) }}  No
                        </label>
                    @else
                        <label class="radio-inline">
                            {{ Form::radio('wear_ppe',true,false,['disabled']) }}  Yes
                        </label>
                        <label class="radio-inline">
                            {{ Form::radio('wear_ppe',false,true,['disabled']) }}  No
                        </label>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('comments',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->comments,['class' => 'form-control']) !!}
                </div>
            </div>

        </div><!-- /.card-body -->


    </div>
</div><!-- /.card -->
@stop
@push('scripts')
<script>
$(function() {
   /* $('#application_date, #payment_date').datepicker({
        format: "yyyy-mm-dd"
    });

    $('#service_fees').on("keyup change", function(){
        var fees = Number($(this).val());
        var vat = Number((fees * 0.15).toFixed(2));
        var total = fees + vat;
        $('#vat').val(vat);
        $('#total_amount').val(total);
    });*/
    //    $('.date').datepicker({

    //    format: 'yyyy-mm-dd',
    //    todayHighlight: true

    //  });
    //    $('.timepicker').datetimepicker({
    //     format: 'hh:mm A'
    // });

    // $('.chosen-select').chosen();
    // $('.date').focus(function(){
    //     $(this).blur();
    // });
});
</script>
@endpush
