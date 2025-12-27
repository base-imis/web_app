@extends('layouts.dashboard')

@section('title', $page_title ?? 'Standard Septic Tank Monitoring Dashboard')

@section('content')
@php
    /**
     * Index drop-in for Septic Tank Dashboard
     * Keeps layout same, ensures variables exist for countBox partials.
     */

    // Support both old and refactored controller variable names
    $Building_data = $Building_data ?? ($buildingData ?? []);
    $matchingRecords = $matchingRecords ?? ($inspectionCompleted ?? 0);
    $criteria_count = $criteria_count ?? ($complianceYesCount ?? 0);

    // Totals (ensure defined)
    $totalRequestCount = $totalRequestCount ?? 0;

    $totalInspectionRequestCount = $totalInspectionRequestCount
        ?? ((int)$totalRequestCount + (int)$matchingRecords);

    $result = $result ?? (
        (int)($Building_data['TotalPlinthLevelApproval'] ?? 0)
      + (int)($Building_data['TotalAbovePlinthLevelApproval'] ?? 0)
      + (int)($Building_data['TotalCompletionApproval'] ?? 0)
    );
@endphp

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
    <div class="col-lg-4 col-xs-6 d-flex">
        @include('containmentmanagementinfo.countBox._totalInspectionRequests')
    </div>
    <div class="col-lg-4 col-xs-6 d-flex">
        @include('containmentmanagementinfo.countBox._septicTankInspectionRequested')
    </div>
    <div class="col-lg-4 col-xs-6 d-flex">
        @include('containmentmanagementinfo.countBox._septicTankInsecptionCompleted')
    </div>
</div>

@endsection
