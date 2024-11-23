@extends('dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    @include('errors.list')
    {!! Form::open(['url' => 'export-shp-kml', 'class' => 'form-horizontal']) !!}

        <div class="card-body">
            <div class="form-group">
                {!! Form::label('Select Layer',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    <select name="tablename" class = 'form-control'>
                        <option value="">Select</option>
                        <option value="wards">Wards</option>
                        <option value="containments">Containments</option>
                        <option value="buildings">Buildings</option>
                        <option value="holding_taxes">Holding Taxes</option>
                        <option value="drains">Drains</option>
                        <option value="settlement_areas">Settlement Areas</option>
                        <option value="roadlines">Roadlines</option>
                        <option value="service_providers">Service Providers</option>
                        <option value="transfer_stations">Transfer Stations</option>
                        <option value="hotspot_identifications">Transfer Stations</option>
                        <option value="fsm_campaigns">Transfer Stations</option>
                        <option value="cwis_general_info">Transfer Stations</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('Choose Export Format',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::file('excelfile') !!}
                </div>
            </div>
        </div><!-- /.card-body -->
        <div class="card-footer">
            <a href="{{ action('ImportExcelController@index') }}" class="btn btn-info">Back to List</a>
            {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
        </div><!-- /.card-footer -->
    {!! Form::close() !!}
</div><!-- /.card -->
@stop
@push('scripts')
@endpush