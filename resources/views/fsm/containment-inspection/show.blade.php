@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

@php
    // Safe fallbacks (in case controller doesn't pass these separately)
    $septic_tank_outlet_pipe = $septic_tank_outlet_pipe ?? ($info->septic_tank_outlet_pipe ?? '-');
    $outlet_connection_design = $outlet_connection_design ?? ($info->outlet_connection_design ?? '-');
    $outlet_connection_field = $outlet_connection_field ?? ($info->outlet_connection_field ?? '-');
    $created_at = $created_at ?? ($info->created_at ?? null);
@endphp

<div class="card card-info">
    <div class="form-horizontal">
        <div class="card-body">

            <div class="form-group row">
                {!! Form::label('septic_tank_sealed','Is the septic tank sealed?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('septic_tank_sealed', true, (bool)$info->septic_tank_sealed, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('septic_tank_sealed', false, !(bool)$info->septic_tank_sealed, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('septic_compartments','Does the septic have at least two compartments?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('septic_compartments', true, (bool)$info->septic_compartments, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('septic_compartments', false, !(bool)$info->septic_compartments, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('depth_of_septic_tank','Is the depth of septic tank within 1.2m and 2.2m?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('depth_of_septic_tank', true, (bool)$info->depth_of_septic_tank, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('depth_of_septic_tank', false, !(bool)$info->depth_of_septic_tank, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('length_in_design','Is Length greater than or equal to the design?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('length_in_design', true, (bool)$info->length_in_design, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('length_in_design', false, !(bool)$info->length_in_design, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('width_in_design','Is Width greater than or equal to the design?', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('width_in_design', true, (bool)$info->width_in_design, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('width_in_design', false, !(bool)$info->width_in_design, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('range_of_septic_tank','Is length of septic tank within the range: (L >= 2W) and (L < 5W)?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('range_of_septic_tank', true, (bool)$info->range_of_septic_tank, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('range_of_septic_tank', false, !(bool)$info->range_of_septic_tank, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('septic_tank_chamber_requirement','Does the septic tank fulfill the chamber length requirements?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('septic_tank_chamber_requirement', true, (bool)$info->septic_tank_chamber_requirement, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('septic_tank_chamber_requirement', false, !(bool)$info->septic_tank_chamber_requirement, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('holes_in_partition_wall','Are the holes in the partition wall provided within 10 cm to 15 cm and at half of liquid depth?',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    <label class="radio-inline">
                        {{ Form::radio('holes_in_partition_wall', true, (bool)$info->holes_in_partition_wall, ['disabled' => true]) }} Yes
                    </label>
                    <label class="radio-inline">
                        {{ Form::radio('holes_in_partition_wall', false, !(bool)$info->holes_in_partition_wall, ['disabled' => true]) }} No
                    </label>
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('septic_tank_outlet_pipe','Is level difference between inlet and outlet pipe within 10 cm to 15 cm?', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('septic_tank_outlet_pipe', $septic_tank_outlet_pipe, ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('septic_tank_location','Where is the Septic Tank located?', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('septic_tank_location', $info->septic_tank_location, ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('septic_tank_manhole','Is the septic tank provided with manhole access at inlet and outlet section?', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('septic_tank_manhole', $info->septic_tank_manhole, ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('outlet_connection_design','Outlet Connection as per design', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('outlet_connection_design', $outlet_connection_design, ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('outlet_connection_field','Outlet Connection as per field inspection', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('outlet_connection_field', $outlet_connection_field, ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('compliance_status', 'Compliance Status', ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('compliance_status', ($info->compliance_status ? 'Compliant' : 'Not Compliant'), ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('created_at','Construction Date', ['class'=>'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::date('created_at', optional($created_at)->format('Y-m-d'), ['class' => 'form-control col-sm-10', 'disabled' => true]) !!}
                </div>
            </div>

        </div><!-- /.card-body -->

        <div class="card-footer">
            <a href="{{ url('fsm/containment-inspection') }}" class="btn btn-info">Back to List</a>
        </div>
    </div>
</div><!-- /.card -->

@stop
