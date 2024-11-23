<!-- Last Modified Date: 15-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

    <h1 style="padding-bottom: 15px;font-size: 24px;">Buildings</h1>
    <div class="row">
        <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
            @include('dashboard.countBox._buildCountBox')
        </div> <!-- main col div -->
        <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">
            <div class="row">
                <div class="col-lg-4 d-flex">
                    @include('dashboard.countBox._residentialBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._commercialBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._industrialBuildCountBox')
                </div> <!--sub col div -->
            </div> <!-- sub row -->
            <div class="row">
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._mixedBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._institutionCountbox')
                </div>
                {{-- <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._healthBuildCountBox')
                </div> <!--sub col div --> --}}
                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._educationBuildCountBox')
                </div>

            </div> <!--sub row -->


            <div class="row">

                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._othersBuildCountBox')
                </div>

            </div>

        </div> <!-- col div -->
    </div> <!-- row div -->


    <h1 style="padding: 15px 0 15px 0;font-size: 24px;">Sanitation Systems</h1>
    <div class="row">
        @foreach ($sanitationSystems as $sanitationSystem)
            <div class="col-lg-3 col-xs-6">
                <div class="info-box sanitation-system-info">
                    <span class="info-box-icon bg-info">
                        @if (
                            $sanitationSystem->icon_name &&
                                $sanitationSystem->icon_name != 'no_icon' &&
                                $sanitationSystem->icon_name != 'others.svg')
                            <img src="{{ asset('img/svg/imis-icons/' . $sanitationSystem->icon_name) }}"
                                alt="{{ $sanitationSystem->sanitation_system }}">
                        @else
                            <i class="fa fa-building"></i>
                        @endif
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            <h3> {{ $sanitationSystem->bin_count }}</h3>
                        </span>
                        <span class="info-box-number">{{ $sanitationSystem->sanitation_system }}</span>
                        <!--<i class="fa fa-info-circle sanitation-system-info-icon" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Single Pit, <br>Cesspool/ Holding Tank, <br>Septic Tank without Soak Away Pit, <br>Septic Tank connected to Sewerage Network"></i>-->
                    </div>
                </div>
            </div> <!--sub col div -->
        @endforeach


        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._sanitationOffsiteContainmentCountBox')
        </div> <!--sub col div -->
        {{-- <div class="col-lg-3 col-xs-6">
      @include('dashboard.countBox._sanitationOnsiteDisposalCountBox')
    </div> <!--sub col div --> --}}
        {{-- <div class="col-lg-3 col-xs-6">
      @include('dashboard.countBox._sanitationOnsiteTreatmentCountBox')
    </div> <!--sub col div --> --}}
        {{-- <div class="col-lg-3 col-xs-6">
      @include('dashboard.countBox._sanitationSewerageNetworkCountBox')
    </div> <!--sub col div --> - --}}
        {{-- <div class="col-lg-3 col-xs-6">

    </div>  --}}
        <!--sub col div -->

    </div> <!-- row div -->

    <h1 style="padding: 15px 0 15px 0;font-size: 24px;">Utility</h1>
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._sumRoadsCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._sumSewersCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._sumDrainsCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._sumWatersupplyCountBox')
        </div> <!--sub col div -->
    </div> <!-- row div -->
    @can('FSM Related Chart')
        <h1 style="padding: 15px 0 15px 0;font-size: 24px;">FSM Services</h1>
        <div class="row">
            <!-- ./col -->
            {{-- <div class="col-lg-4 col-xs-6">
      @include('dashboard.fsmCharts._applicationCountBox')
    </div> --}}
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
                @include('dashboard.fsmCharts._emptyingServicesCountBox')
            </div>

            {{-- <div class="col-lg-4 col-xs-6">
      @include('dashboard.fsmCharts._uniqueContainCodeEmptiedCountBox')
    </div> --}}




            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._sludgeCollectionsEmptyingServicesBox')
            </div>
            {{-- div class="col-lg-4 col-xs-6">
      @include('dashboard.fsmCharts._sludgeCollectionsCountBox')
    </div> --}}
            <!-- ./col -->
            <div class="col-lg-4 col-xs-6">
                @include('dashboard.fsmCharts._costPaidByOwnerWithReceiptBox')
            </div>

        </div>
        <!-- /.row -->
    @endcan
    <h1 style="padding: 15px 0 15px 0;font-size: 24px;">PT/CT</h1>
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._pTCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._cTCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._totalPtUserCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._totalCtUserCountBox')
        </div> <!--sub col div -->
    </div> <!-- row div -->
    {{-- <div class="row">
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
</div> --}}


    {{-- HIDDEN --}}
    {{--
@hasanyrole('Super Admin|Admin')
<div class="row">
@can('Emptying service by Year Chart')
<div class="col-md-6">
    @include('dashboard.containments._containmentEmptiedByWardChart')
</div>
@endcan
    <div class="col-md-6">
      @include('dashboard.containments._nextEmptyingContainmentsChart')
    </div>
</div>
@endhasanyrole
--}}
    <h1 style="padding: 15px 0 15px 0;font-size: 24px;">Public Health</h1>
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._totalHotspotCountBox')
        </div> <!--sub col div -->
        <div class="col-lg-3 col-xs-6">
            @include('dashboard.countBox._totalWaterBorneCasesCountBox')
        </div> <!--sub col div -->
    </div> <!-- row div -->
    <div class="row">
        @can('Building Structures by building use Chart')
            <div class="col-md-6">
                @include('dashboard.buildings._buildingsPerWardChart')
            </div>
        @endcan
        @can('Building Structures per Ward Chart')
            <div class="col-md-6">
                @include('dashboard.buildings._buildingUseChart')
            </div>
        @endcan

    </div>
    @can('Building sanitation systems chart')
        <div class="row">

            <div class="col-md-12">
                @include('dashboard.buildings._sanitationSystemsChart')
            </div>

        </div>
    @endcan
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


    <div class="row">
        @can('Emptying service by Year Chart')
            <div class="col-md-6">
                @include('dashboard.fsmCharts._emptyingServiceByTypeYearChart')
            </div>
        @endcan
        @can('Sludge Collections by Treatment Plants Chart')
            <div class="col-md-6">
                @include('dashboard.fsmCharts._sludgeCollectionByTreatmentPlant')
            </div>
        @endcan

    </div>
    @can('Cost Paid for Emptying Services Chart')
        <div class="row">
            <div class="col-md-12">
                @include('dashboard.cost-paid-emptying._costPaidByContainmentOwnerPerwardChart')
            </div>
        </div>
    @endcan

    <div class="row">
        @can('Tax Revenue Chart')
            <div class="col-md-6">
                @include('dashboard.tax-revenue._taxRevenueChart')
            </div>
        @endcan
        @can('Water Supply Payment Chart')
            <div class="col-md-6">
                @include('dashboard.water-supply._waterSupplyPaymentChart')
            </div>
        @endcan
    </div>

    <div class="row">
    <div class="col-md-6">
            @include('dashboard.buildings._waterSupplyPipeCodePresencebyWardChart')
        </div>
        <div class="col-md-6">
            @include('dashboard.swm.swm_chart')
        </div>

    </div>



    <div class="row">
        <div class="col-md-6">
            @include('dashboard.buildings._swmPresencebyWardChart')
        </div>
       
    </div>
    {{-- @can('Containment type Chart')
<div class="row">
    <div class="col-md-6">
      @include('dashboard.containments._containmentTypesByStructypesChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.containments._containmentTypesByBldgUsesResidentialsChart')
    </div>
</div>

<div class="row">
    <div class="col-md-6">
      @include('dashboard.containments._containmentTypesByBldgusesChart')
    </div>
    <div class="col-md-6">
      @include('dashboard.containments._containmentTypesByLanduseChart')
    </div>
</div>
@endcan --}}
    <div class="row">
        @can('Sewer Length Per Ward Chart')
            <div class="col-md-6">
                @include('dashboard.charts._roadLengthPerWardChart')
            </div>
        @endcan
        @can('Sewer Length Per Ward Chart')
            <div class="col-md-6">
                @include('dashboard.sewer._sewerLengthPerWardChart')
            </div>
        @endcan
        {{-- @can('Hotspots Per Ward Chart')
        <div class="col-md-6">
            @include('dashboard.hotspots._hotspotsPerWardChart')
        </div>
    @endcan --}}
    </div>
    <div class="row">

        <div class="col-md-6">
            @include('dashboard.charts._waterborneCasesChart')
        </div>
        <div class="col-md-6">
            @include('dashboard.fsm-campaign._treatmentPlantTestbyYearChart')
        </div>

    </div>

    {{-- <div class="row">
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
</div> --}}
@stop

@push('scripts')
    <script>
        $('[id="year_select"]').change(function(e) {
            // e.preventDefault();
            var year_select = $(this).val();
            localStorage.setItem('year_select', year_select);
        });

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
        $(function() {
            $('[data-toggle="tooltip"]').tooltip({
                html: true
            });
        });
    </script>
@endpush
