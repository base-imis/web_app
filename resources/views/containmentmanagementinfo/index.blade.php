@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

<div class="row">
    <div class="col-lg-3 d-flex">
        @include('containmentmanagementinfo.countBox._totalBuildingPermitApplication')
    </div>
    <div class="col-lg-3 d-flex">
        @include('containmentmanagementinfo.countBox._plinthLevelApproval')
    </div>
    <div class="col-lg-3 d-flex">
        @include('containmentmanagementinfo.countBox._abovePlinthLevelApproval')
    </div>
    <div class="col-lg-3 d-flex">
        @include('containmentmanagementinfo.countBox._complitionApproval')
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-xs-6  d-flex" >
        @include('containmentmanagementinfo.countBox._totalInspectionRequests')
    </div>
    <div class="col-lg-4 col-xs-6  d-flex" >
        @include('containmentmanagementinfo.countBox._septicTankInspectionRequested')
    </div>
    <div class="col-lg-4 col-xs-6  d-flex" >
        @include('containmentmanagementinfo.countBox._septicTankInsecptionCompleted')
    </div>
</div>

@endsection
