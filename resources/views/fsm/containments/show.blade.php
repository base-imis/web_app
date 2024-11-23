@extends('layouts.dashboard')
@section('title',  $page_title)
@section('content')
<div class="card card-info">


    <div class="card-header bg-transparent ">
        <a href="{{ action('Fsm\ContainmentController@index') }}" class="btn btn-info">Back to List</a>
    </div>

	<div class="form-horizontal">
		<div class="card-body">

		<div class="form-group row">
            {!! Form::label('type_id','Containment Type',array('class'=>'col-sm-2 control-label ')) !!}
            <div class="col-sm-3">
            {!! Form::text(null,$containment->type_id ? $containment->containmentType->type : '',['class' => 'form-control col-sm-10', 'disabled' => 'true']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('size','Containment Volume (m³)',array('class'=>'col-sm-2 control-label ')) !!}
            <div class="col-sm-3">
            {!! Form::label(null,$containment->size,['class' => 'form-control col-sm-10']) !!}

            </div>
        </div>
        <div id = "pit-size">
            {{-- <div class="form-group row">
                {!! Form::label('pit_number','Number of Pits',array('class'=>'col-sm-2 control-label')) !!}
                <div class="col-sm-3">
                {!! Form::label($containment->pit_number,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Number of Pits']) !!}
                </div>
            </div> --}}
            <div class="form-group row">
                {!! Form::label('pit_diameter','Pit Diameter (m)',array('class'=>'col-sm-2 control-label')) !!}
                <div class="col-sm-3">
                {!! Form::label($containment->pit_diameter,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Pit Diameter']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('depth','Depth (m)',array('class'=>'col-sm-2 control-label')) !!}
                <div class="col-sm-3">
                {!! Form::label($containment->depth,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Depth']) !!}
                </div>
            </div>
        </div>
        <div id = "tank-size">
            <div class="form-group row">
                {!! Form::label('tank_length','Tank Length (m)',array('class'=>'col-sm-2 control-label')) !!}
                <div class="col-sm-3">
                {!! Form::label($containment->tank_length,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Tank Length']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('tank_width','Tank Width (m)',array('class'=>'col-sm-2 control-label')) !!}
                <div class="col-sm-3">
                {!! Form::label($containment->tank_width,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Tank Width']) !!}
                </div>
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('location','Containment Location',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->location,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Location']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('septic_criteria','Septic Tank Standard Compliance',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($septic_criteria, null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Septic Tank Standard Compliance']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('construction_date','Construction Date',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->construction_date,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Date']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('emptied_status','Emptied Status ',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->emptied_status?'Yes':'No',null,['class' => 'form-control col-sm-10', 'placeholder' => 'Emptied Status']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('last_emptied_date','Last Emptied Date ',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->last_emptied_date,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Last Emptied Date']) !!}
            </div>
        </div>


        <div class="form-group row">
            {!! Form::label('next_emptying_date','Next Empting Date ',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->next_emptying_date,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Next Empting  Date']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('no_of_times_emptied','Number of Times Emptied',array('class'=>'col-sm-2 control-label')) !!}
            <div class="col-sm-3">
            {!! Form::label($containment->no_of_times_emptied,null,['class' => 'form-control col-sm-10', 'placeholder' => 'Number of Times Emptied']) !!}
            </div>
        </div>


		</div><!-- /.card-body -->
	</div>

</div><!-- /.box -->
@stop

