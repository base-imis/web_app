<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<h1 style="padding: 15px 0 15px 0;font-size: 24px;">Road</h1>
<div class="row">
    <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
        @include('dashboard.countBox._sumRoadsCountBox')
    </div> <!-- main col div -->
    <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Road Length by Surface Type (m)</h1>
        <div class="row">
            <div class="col-lg-3 d-flex">
                @include('dashboard.countBox._sumRoadSurfaceTypeCountBox')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumSurfaceType3CountBox')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumSurfaceType2CountBox')
            </div> <!--sub col div -->

            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumSurfaceType1CountBox')
            </div> <!--sub col div -->
        </div> <!-- sub row -->

        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Road Length by Width (m) </h1>
        <div class="row">
            <div class="col-md-2  d-flex">
                @include('dashboard.countBox._sumRoadWidth3')
            </div> <!--sub col div -->
            <div class="col-md-2  d-flex">
                @include('dashboard.countBox._sumRoadWidth2')
            </div> <!--sub col div -->
            <div class="col-md-2 d-flex ">
                @include('dashboard.countBox._sumRoadWidth1')
            </div>
            <div class="col-md-2  d-flex">
                @include('dashboard.countBox._sumRoadWidth')
            </div> <!--sub col div -->
            <div class="col-md-2  d-flex">
                @include('dashboard.countBox._sumRoadWidth4')
            </div> <!--sub col div -->
        </div>
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Road Length by Hierarchy</h1>
        <div class="row">
            <div class="col-lg-4 d-flex ">
                @include('dashboard.countBox._sumHierarchy1CountBox')
            </div>
            <div class="col-lg-4  d-flex">
                @include('dashboard.countBox._sumHierarchy2CountBox')
            </div> <!--sub col div -->

            <div class="col-lg-4  d-flex">
                @include('dashboard.countBox._sumHierarchyCountBox')
            </div> <!--sub col div -->
        </div> <!--sub row -->
    </div> <!-- col div -->
</div> <!-- row div -->
<div class="row">
    <div class="col-md-6">
        @include('dashboard.charts._roadLengthPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._roadsSurfaceTypePerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._roadsHierarchyPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._roadsWidthPerWardChart')
    </div>
</div>
<h1 style="padding: 15px 0 15px 0;font-size: 24px;">Sewer</h1>
<div class="row">
    <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
        @include('dashboard.countBox._sumSewersCountBox')
    </div> <!-- main col div -->
    <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">


        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Sewer Length by Diameter (mm)</h1>
        <div class="row">
            <div class="col-md-3  d-flex">
                @include('dashboard.countBox._sumSewerWidth3CountBox')
            </div> <!--sub col div -->
            <div class="col-md-3 d-flex ">
                @include('dashboard.countBox._sumSewerWidth1CountBox')
            </div>
            <div class="col-md-3  d-flex">
                @include('dashboard.countBox._sumSewerWidthCountBox')
            </div> <!--sub col div -->

            <div class="col-md-3  d-flex">
                @include('dashboard.countBox._sumSewerWidth2CountBox')
            </div> <!--sub col div -->

        </div>


    </div> <!-- col div -->
</div> <!-- row div -->
<div class="row">
    <div class="col-md-6">
        @include('dashboard.sewer._sewerLengthPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._sewerWidthPerWardChart')
    </div>
</div>
<h1 style="padding: 15px 0 15px 0;font-size: 24px;">Drain</h1>
<div class="row">
    <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
        @include('dashboard.countBox._sumDrainsCountBox')
    </div> <!-- main col div -->
    <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Drain Length by Diameter (mm) </h1>
        <div class="row">
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumDrainWidth3')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumDrainWidth1')
            </div> <!--sub col div -->

            <div class="col-lg-3 d-flex">
                @include('dashboard.countBox._sumDrainWidth')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumDrainWidth2')
            </div> <!--sub col div -->
        </div> <!-- sub row -->
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Drain Length by Type</h1>
        <div class="row">
            <div class="col-lg-4  d-flex">
                @include('dashboard.countBox._sumDrainCoverType')
            </div> <!--sub col div -->
            <div class="col-lg-4 d-flex ">
                @include('dashboard.countBox._sumDrainCoverType1')
            </div>
        </div> <!--sub row -->
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Drain Length by Surface Type (m)</h1>
        <div class="row">
            <div class="col-lg-4  d-flex">
                @include('dashboard.countBox._sumDrainSurfaceType')
            </div> <!--sub col div -->
            <div class="col-lg-4 d-flex ">
                @include('dashboard.countBox._sumDrainSurfaceType1')
            </div>



        </div> <!--sub row -->

    </div> <!-- col div -->
</div> <!-- row div -->
<div class="row">
    <div class="col-md-6">
        @include('dashboard.charts._drainLengthPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._drainsTypePerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._drainWidthPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._drainsSurfaceTypePerWardChart')
    </div>
 
</div>

<h1 style="padding: 15px 0 15px 0;font-size: 24px;">Water Supply</h1>
<div class="row">
    <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
        @include('dashboard.countBox._sumWatersupplyCountBox')
    </div> <!-- main col div -->
    <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">
        <h1 style="padding: 15px 0 15px 0;font-size: 18px;">Water Supply Length by Diameter (mm) </h1>
        <div class="row">
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumWaterSupply3')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumWaterSupply1')
            </div> <!--sub col div -->

            <div class="col-lg-3 d-flex">
                @include('dashboard.countBox._sumWaterSupply')
            </div> <!--sub col div -->
            <div class="col-lg-3  d-flex">
                @include('dashboard.countBox._sumWaterSupply2')
            </div> <!--sub col div -->
        </div> <!-- sub row -->



    </div> <!-- col div -->
</div> <!-- row div -->
<div class="row">
    <div class="col-md-6">
        @include('dashboard.charts._watersupplyLengthPerWardChart')
    </div>
    <div class="col-md-6">
        @include('dashboard.charts._watersupplyDiameterPerWardChart')
    </div>
</div>

@stop