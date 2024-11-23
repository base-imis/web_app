<!-- Last Modified Date: 10-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
    @can('FSM Related Chart')
        <div class="row" style="padding: 15px 0 15px 0;font-size: 24px;">
            <div class="col-lg-8 col-xs-8">
                <form class="form-inline" id="filter-form" action="{{ action('Fsm\FsmDashboardController@index') }}" method="get">
                    <div class="form-group">
                        <label for="year_select">Year</label>
                        <select class="form-control" id="year_select" name="year">
                            <option value="">All Years</option>
                            @for ($year = $maxDate; $year >= $minDate; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>

                        <button type="submit" class="ml-1 btn btn-info">Filter</button>
                        <a href="{{ action('Fsm\FsmDashboardController@index') }}" class="ml-1 btn btn-info reset">Reset</a>
                      </div>
          </form>
        </div>
  </div>

        <div class="row">
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._serviceProvidersCountBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._desludgingVehicleCountBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._treatmentPlantCountBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._applicationCountBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._emptyingServicesCountBox')
            </div>

            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._uniqueContainCodeEmptiedCountBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._sludgeCollectionsEmptyingServicesBox')
            </div>
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._sludgeCollectionsCountBox')
            </div>

            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._costPaidByOwnerWithReceiptBox')
            </div>

        </div>
        <!-- /.row -->
    @endcan

    <div class="row">
        @can('Proposed Emptying Date for Next 4W Chart')
            <div class="col-md-6">
                @include('dashboard.containments._proposedEmptyingDateNextFourWeeksChart')
            </div>
        @endcan
        @can('Proposed Emptying Date by wards for next 4W Chart')
            <div class="col-md-6">
                @include('dashboard.containments._proposedEmptyingDateContainmentsByWardChart')
            </div>
        @endcan
    </div>
     @can('FSM Feedback Chart')
        <div class="row">
            <div class="col-md-6">
                @include('dashboard.fsm-feedback-charts._fsmServiceQualityChart')
            </div>

            <div class="col-md-6">
                @include('dashboard.fsm-feedback-charts._ppeChart')
            </div>
        </div>
    @endcan

    @can('Applications, Emptying services, Feedback details by Wards Chart')
    <div class="row">
    <div class="col-md-12">
        @include('dashboard.fsmCharts._emptyingServicePerWardsAssessmentFeedbackChart')
    </div>
    </div>
    @endcan
    <div class="row">
        @can('Monthly Requests By Operators/Service Providers')
            <div class="col-md-6">
                @include('dashboard.charts._monthlyRequestByOperators')
            </div>
        @endcan
        @can('Emptying Requests By Low Income Communities and Other Communities')
            <div class= "col-md-6">
                @include('dashboard.charts._numberOfEmptyingByMonthsChart')
            </div>
        @endcan
    </div>
    <div class="row">
    @can('Emptying Requests By Structure Types')
        <div class="col-md-6">
              @include('dashboard.charts._emptyingByStructureTypes')
            </div>
        @endcan
        <div class="col-md-6">
            @include('dashboard.containments._containmentTypesByLanduseChart')
        </div>
    </div>
    <div class="row">
        @can('Sludge Collections by Treatment Plants Chart')
            <div class="col-md-6">
                @include('dashboard.fsmCharts._sludgeCollectionByTreatmentPlant')
            </div>
        @endcan
        @can('Emptying service by Year Chart')
            <div class="col-md-6">
                @include('dashboard.fsmCharts._emptyingServiceByTypeYearChart')
            </div>
        @endcan
    </div>



    @hasanyrole('Super Admin|Admin')
        <div class="row">
            <div class="col-md-6">
                @include('dashboard.containments._containTypeChart')

            </div>
            <div class="col-md-6">

                @include('dashboard.containments._containmentTypesPerWardChart')
            </div>

        </div>
    @endhasanyrole
    @can('Containment type Chart')
        <div class="row">
            {{-- <div class="col-md-6">
                @include('dashboard.containments._containmentTypesByStructypesChart')
            </div> --}}
            <div class="col-md-6">
                @include('dashboard.containments._containmentTypesByBldgUsesResidentialsChart')
            </div>
            <div class="col-md-6">
                @include('dashboard.containments._containmentTypesByBldgusesChart')
            </div>
        </div>

        {{-- <div class="row">
            {{-- <div class="col-md-6">
                @include('dashboard.containments._containmentTypesByBldgusesChart')
            </div> --}}
            {{-- <div class="col-md-6">
                @include('dashboard.containments._containmentTypesByLanduseChart')
            </div>
        </div> --}}
    @endcan





@stop
@push('scripts')
    <script>
        $('[id="year_select"]').change(function(e) {
            // e.preventDefault();
            var year_select = $(this).val();
            localStorage.setItem('year_select', year_select);
        })
    </script>
    <script>
        $(document).ready(function() {
            year_sel = localStorage.getItem('year_select');
            if (year_sel) {
                $("#year_select").val(year_sel);
            }
            $('.reset').click(function(e) {
                localStorage.removeItem("year_select");
                $("#year_select").val();
            })
        })
    </script>
@endpush
