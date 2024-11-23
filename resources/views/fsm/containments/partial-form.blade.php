<!-- Containment ID -->

<div id="containment-info" style="display: none;margin:12px">
    <h2 class=""> Containment Information </h2>




    <div class="form-group row required" id='containment-type'>
        {!! Form::label('type_id', 'Containment Type', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('type_id', $containment_type, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Containment Type',
            ]) !!}
        </div>
    </div>

    @if (!empty($containment_building->sewer_code) && !empty($containment_building))
        <div class="form-group row required " id="sewer-code">
            {!! Form::label('sewer_code', 'Sewer Code', ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('sewer_code', $sewer_code, $containment_building->sewer_code, [
                    'class' => 'form-control col-sm-10 sewer_code',
                    'placeholder' => 'Sewer Code',
                    'id' => 'sewer_code',
                ]) !!}
            </div>
        </div>
    @elseif(empty($containment_building->sewer_code) && !empty($containment_building))
        <div class="form-group row required" id="sewer-code">
            {!! Form::label('sewer_code', 'Sewer Code', ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('sewer_code', $sewer_code, $containment_building->sewer_code, [
                    'class' => 'form-control col-sm-10 sewer_code',
                    'placeholder' => 'Sewer Code',
                    'id' => 'sewer_code',
                ]) !!}
            </div>
        </div>
    @endif

    @if (!empty($containment_building->drain_code) && !empty($containment_building))
        <div class="form-group row required" id="drain-code">
            {!! Form::label('drain_code', 'Drain Code', ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('drain_code', $drain_code, $containment_building->drain_code, [
                    'class' => 'form-control col-sm-10 drain_code',
                    'placeholder' => 'Drain Code',
                    'id' => 'drain_code',
                ]) !!}
            </div>
        </div>
    @elseif(empty($containment_building->drain_code) && !empty($containment_building))
        <div class="form-group row required" id="drain-code">
            {!! Form::label('drain_code', 'Drain Code', ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('drain_code', $drain_code, $containment_building->drain_code, [
                    'class' => 'form-control col-sm-10 drain_code',
                    'placeholder' => 'Drain Code',
                    'id' => 'drain_code',
                ]) !!}
            </div>
        </div>
    @endif

    <div class="form-group row " id="pit-shape" style="display:none">
        {!! Form::label('pit_shape', 'Pit Shape', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select(
                'pit_shape',
                [
                    'Cylindrical' => 'Cylindrical',
                    'Rectangular' => 'Rectangular',
                ],
                null,
                ['class' => 'form-control col-sm-10', 'placeholder' => 'Pit Shape'],
            ) !!}
        </div>
    </div>
    <div id="pit-size" style="display: none">
        <div class="form-group row ">
            {!! Form::label('pit_diameter', 'Pit Diameter (m)', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('pit_diameter', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Pit Diameter (m)']) !!}
            </div>
        </div>
        <div class="form-group row " id="pit-depth" style="display: none">
            {!! Form::label('pit_depth', 'Pit Depth (m)', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('pit_depth', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Pit Depth (m)',
                    'step' => 'any',
                ]) !!}
            </div>
        </div>
    </div>
    <div id="tank-size">
        <div class="form-group row" id ="tank-length">
            {!! Form::label('tank_length', 'Tank Length (m)', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('tank_length', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Tank Length (m)',
                    'step' => 'any',
                ]) !!}
            </div>
        </div>
        <div class="form-group row " id ="tank-width">
            {!! Form::label('tank_width', 'Tank Width (m)', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('tank_width', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Tank Width (m)',
                    'step' => 'any',
                ]) !!}
            </div>
        </div>
        <div class="form-group row " id ="tank-depth">
            {!! Form::label('depth', 'Tank Depth (m)', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::number('depth', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Tank Depth (m)',
                    'step' => 'any',
                ]) !!}
            </div>
        </div>
    </div>
    <div class="form-group row required" id="size">
        {!! Form::label('size', 'Containment Volume (m³)', ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::number('size', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Containment Volume (m³)(Enter Dimensions to auto calculate)',
                'step' => 'any',
            ]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('location', 'Containment Location', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select(
                'location',
                [
                    'Inside the house' => 'Inside the house',
                    'Outside the house' => 'Outside the house',
                ],
                null,
                ['class' => 'form-control col-sm-10', 'placeholder' => 'Containment Location'],
            ) !!}
        </div>
    </div>



    <div id="septic-tank">
        <div class="form-group row">
            {!! Form::label('septic_criteria', 'Septic Tank Standard Compliance', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('septic_criteria', [true => 'Yes', false => 'No'], null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Septic Tank Standard Compliance',
                ]) !!}
            </div>
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('construction_date', ' Containment Construction Date', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::date('construction_date', null, [
                'class' => 'form-control col-sm-10',
                'autocomplete' => 'off',
                'placeholder' => 'Containment Construction Date',
                'max' => now()->format('Y-m-d'),
            ]) !!}
        </div>
    </div>





</div> <!-- containmend id -->
