<div class="card-body">

    <div class="form-group row required">
        {!! Form::label('indicator_id','Indicator',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('indicator_id', $indicators, null, ['class' => 'form-control chosen-select', 'placeholder' => 'Indicator']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('year','Year',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('year',null,['class' => 'form-control', 'placeholder' => 'Year']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('target','Target (%)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('target',null,['class' => 'form-control', 'placeholder' => 'Target (%)']) !!}
        </div>
    </div>


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\KpiTargetController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->
