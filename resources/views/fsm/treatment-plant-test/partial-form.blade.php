<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<style>
    #map {
        width: 100px;
        height: 50px;
        /* 100% of the viewport height - navbar height */
    }

    #olmap {
        border: 1px solid #000000;
        margin-top: 20px;
    }

    a.skiplink {
        position: absolute;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0;
        border: 0;
        height: 1px;
        width: 1px;
        overflow: hidden;
    }

    a.skiplink:focus {
        clip: auto;
        height: auto;
        width: auto;
        background-color: #fff;
        padding: 0.3em;
    }

    #map:focus {
        outline: #4A74A8 solid 0.15em;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css" />
<style>
    .layer-switcher {
        top: 0.5em;
    }

    .layer-switcher button {
        width: 25px;
        height: 25px;
        background-position: unset;
        background-size: contain;
    }
</style>

<div class="card-body">
    <div class="form-group row required" id="treatment_plant_id">
        {!! Form::label('treatment_plant_id', 'Treatment Plant', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('treatment_plant_id', $trtName, null, [
                'class' => 'form-control ',
                'placeholder' => 'Treatment Plant',
            ]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('date', 'Sample Date', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('date', null, ['class' => 'form-control', 'placeholder' => 'Choose Date', 'type' => 'text']) !!}
        </div>
    </div>


    <div class="form-group row required">
        {!! Form::label('temperature', 'Temperature °C', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 input-group">
            {!! Form::number('temperature', null, ['class' => 'form-control', 'placeholder' => 'Temperature in °C', 'step' => 'any']) !!}

        </div>
    </div>


    <div class="form-group row required">
        {!! Form::label('ph', 'pH ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('ph', null, ['class' => 'form-control', 'placeholder' => 'pH', 'step' => 'any']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('cod', 'COD (mg/l)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('cod', null, ['class' => 'form-control', 'placeholder' => 'COD (mg/l)', 'step' => 'any']) !!}
        </div>
    </div>

    <div class="form-group row required" >
        {!! Form::label('bod', 'BOD (mg/l)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('bod', null, ['class' => 'form-control', 'placeholder' => 'BOD (mg/l)', 'step' => 'any']) !!}
        </div>
    </div>

    <div class="form-group row required ">
        {!! Form::label('sample_location', 'Sample Location', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('sample_location', ['Influent' => 'Influent', 'Effluent' => 'Effluent'], null, ['class' => 'form-control', 'placeholder' => 'Sample Location']) !!}
        </div>
    </div>


    <div class="form-group row required">
        {!! Form::label('tss', 'TSS (mg/l)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('tss', null, ['class' => 'form-control', 'placeholder' => 'TSS (mg/l)', 'step' => 'any']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('ecoli', 'Ecoli', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('ecoli', null, ['class' => 'form-control', 'placeholder' => 'Ecoli']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('remarks', 'Remark', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('remarks', null, ['class' => 'form-control', 'placeholder' => 'Remark']) !!}
        </div>
    </div>


</div>


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\TreatmentPlantTestController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->
