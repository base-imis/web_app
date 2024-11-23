<!-- Last Modified Date: 21-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@php
        $currentYear = date('Y');
        $shownYear = $currentYear - 2;
        $nextYear = $currentYear + 1;
        $lastYear = $currentYear - 1;

@endphp


@if ($hasInvalidValues)
<!-- Bootstrap Modal -->
<div class="modal fade" id="invalidDataModal" tabindex="-1" role="dialog" aria-labelledby="invalidDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        
            <div class="modal-body text-center">
                <p>
                    Some indicators have been calculated as <strong>NaN</strong> (Not a Number) or <strong>NA</strong> (Not Available).
                </p>
                <p>
                    This happens due to one or more of the following reasons:
                </p>
                <ul class="text-left">
                    <li><strong>NA:</strong> Occurs when the numerator or denominator is missing.</li>
                    <li><strong>NaN:</strong> Happens when:
                        <ul>
                            <li>The denominator is zero (division by zero).</li>
                            <li>Both the numerator and denominator are zero (undefined result).</li>
                        </ul>
                    </li>
                </ul>
             
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               
            </div>
        </div>
    </div>
</div>
@endif

@if ($hasInvalidValues)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#invalidDataModal').modal('show');
    });
</script>
@endif



<div class="card card-info">
    <div class="card-header bg-white">
        <form method="GET" action="{{ url('cwis/cwis-df-mne/export-mne-csv')}}" class="form-inline ml-auto">

            @if($show_add_cwis_button)
                <a href="{{ action('Cwis\CwisMneController@createIndex', ['year' =>  $shownYear, 'placeholder' => 'Enter value in percent','displayText' => 'quantitative']) }}" class="btn btn-info ml-2" id="addCwisData">Add CWIS Data</a>
            @else
          

            <!-- <a href="#" class="btn btn-info ml-2" id="edit" data-toggle="tooltip" title="Edit CWIS Data">Edit CWIS Data</a> -->

            @if($pickyear[0] == $currentYear && !Auth::user()->hasRole('Municipality - Executive'))
                {{-- No action needed, nothing will be shown --}}
         @elseif($pickyear[0] == $lastYear && !Auth::user()->hasRole('Municipality - Executive'))
         <a href="#" class="btn  ml-2" id="addCwisData" style="background-color:#C9D7EA" data-toggle="tooltip" title="This will be enabled in  {{ $nextYear }} from January.">Add CWIS Data</a>
        
            @elseif($pickyear[0] == $shownYear && !Auth::user()->hasRole('Municipality - Executive'))
                <a href="{{ action('Cwis\CwisMneController@createIndex', ['year' => $slugyear[0], 'placeholder' => 'Enter value in percent','displayText' => 'quantitative']) }}" class="btn btn-info ml-2" id="addCwisData" >Add CWIS Data</a>
            @endif

            @can('Export CWIS MnE to Excel')
            <button type="submit" id="export" class="btn btn-info" style="margin-left: 1%" >Export to Excel</button>
            @endcan

            <a href="{{ action('Cwis\CwisMneController@index') }}" class="btn btn-info float-left" style="display: none; margin-left:1%;" id="back">Back to List</a>

            <div class="form-group float-right text-right ml-auto">
                <label for="year_select">Year</label>
                <select class="form-control" id="year_select" name="year_select">
                    @foreach($pickyear as $key => $unique)
                    <option value="{{$unique}}" @if($unique ==  $year) selected @endif> {{$unique}} </option>
                    @endforeach
                </select>
            </div>
        @endif

        </form>
    </div><!-- /.card-header -->
@if($show_add_cwis_button)
    @elseif(!Auth::user()->hasRole('Municipality - Executive'))
        @include('errors.list')
        {!! Form::open(['url' => 'cwis/cwis/cwis-df-mne', 'class' => 'form-horizontal']) !!}
            @include('cwis/cwis-df-mne.partial-form', ['submitButtonText' => 'Save'])
        {!! Form::close() !!}
    @else
        @include('errors.list')
        {!! Form::open(['url' => 'cwis/cwis/cwis-df-mne', 'class' => 'form-horizontal']) !!}
            @include('cwis/cwis-df-mne.partial-form', ['submitButtonText' => 'Save'])
        {!! Form::close() !!}
    @endif

</div><!-- /.card -->
@stop





