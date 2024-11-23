@extends('layouts.dashboard')
@section('title', "CWIS MnE Dashboard")
<style>
    @keyframes fadein {
        0% {
            opacity: 0;
        }
        40% {
            opacity: 0;
        }
        80% {
            opacity: 1;
        }
        100% {
            opacity: 1;
        }
    }

    .chart-row {
        display: flex;
        align-content: center;
        justify-content: center;
        flex-wrap: wrap;
    }

    .row-header {
        display: flex;
        align-content: center;
    }

    .single-valued-chart {
        position: relative;
        margin: auto;
        font-weight: 900;
    }

    .single-valued-chart-figure {
        display: flex;
        align-content: center;
        justify-content: center;
        align-items: center;
    }

    @media screen and (max-width: 1350px) {
        .single-valued-chart-figure {
            flex-direction: column;
            height: auto;
        }

        /*.box-container {*/
        /*    min-height: 400px;*/
        /*    max-height: 400px;*/
        /*}*/
    }

    .single-valued-chart-caption {
        display: flex;
        justify-content: center;
        flex-direction: column;

        letter-spacing: 0.4px;

        height: 100%;
        width: 100%;
        font-family: "Barlow Condensed", sans-serif;
        color: #1088b6;
        padding: 1rem;
        border-left: .125em solid rgb(214, 214, 214);
    }

    @media screen and (max-width: 1350px) {
        .single-valued-chart-caption {
            margin: 15px auto auto;
            text-align: center;
            min-width: 160px;
        }
    }

    .single-valued-chart span {
        font-size: 16px;
        line-height: 24px;
        font-family: "Montserrat", sans-serif;
        color: #346;
    }

    .single-valued-chart-value {
        position: absolute;
        animation: fadein 1400ms;
    }

    .single-valued-chart-value p {
        font-size: 2rem;
        margin: auto;
        padding-left: 6px;
        font-family: "Barlow Condensed", sans-serif;
    }

    .single-valued-chart-canvas-div {
        display: flex;
        flex-direction: column;
        align-content: center;
        justify-content: center;
        align-items: center;
    }

    /* .box{
        margin-bottom: 0 !important;
    } */

    .box-container {

        min-height: 250px;
        /*max-height: 350px;*/
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .row-header {
        display: flex;
        align-content: center;
        align-items: center;
        justify-content: space-around;
        font-size: 2rem;
        font-weight: bold;
    }

    .chart-with-header {
        display: flex;
        flex-direction: row;
        align-content: center;
        justify-content: space-around;
        align-items: center;
        width: 100%;
    }

    .chart-div {
        width: 100%;
        padding-top: 2rem;
        /*min-width: 300px;*/
    }

    .gauge-chart {
        max-height: 150px;
        max-width: 150px;
        min-height: 150px;
        min-width: 150px;
    }

    .gauge-container {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        align-content: center;
        flex-direction: column;
        max-height: 250px;
    }

    .gauge-label {
        position: absolute;
        bottom: 40%;
        font-size: 2.5rem;
        color: rgb(255, 99, 132);
    }

    .gauge-description {
        display: flex;
        justify-content: center;
        flex-direction: column;
        margin-left: 1rem;
        font-size: 1.5rem;
        height: 100%;
        font-family: "Barlow Condensed", sans-serif;
        color: rgb(255, 99, 132);
        border-left: .125em solid rgb(214, 214, 214);
        text-align: center;
    }

    .single-valued-num-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .single-valued-num-chart-text, .single-valued-num-chart-number {
        font-size: clamp(2rem, 4vw, 2vh);
        color: rgb(54, 162, 235);
        font-weight: bold;
        line-height: 1;
    }

    .single-valued-num-chart-number {
        font-size: clamp(6rem, 8vw, 2vh);
    }



    .gauge-chart {
        display: flex;
        justify-content: center;
    }

    .content-wrapper {
        min-height: 100% !important;
    }

    .section {
        grid-area: section;

        width: 100%;
        /* margin-bottom: 2rem; */
    }



    .section-header h1 {
        font-size: 2.5rem;
    }

    .grid-row {
        margin-bottom: 2rem;
    }

    .grid-item {
        padding: 2.5rem;
    }

    .btn-box-tool .tooltip {
        font-size: 2.5rem;
        background-color: #c87f0a;
    }

    .modal.modal-rel {
        position: absolute;
        z-index: 51;
        padding: 0 !important;
        width: 100%;
        height: 100%;
    }

    .modal-backdrop.modal-rel-backdrop {
        position: absolute;
        z-index: 50;
    }

    .box-modal-dialog {
        width: 100% !important;
    }

    .modal, .modal-backdrop {
        /*top: 38px !important;*/
    }

    .modal-header {
        padding: 0 !important;
    }

    .close {
        padding: 6px 12px !important;
    }

    .modal-content {
        height: 100%;
    }

    .modal-dialog {
        margin: 0 !important;
    }

    .modal-body {
        padding: 0 !important;
        display: flex;
        justify-content: center;
        height: 80%;
    }

    .flexible {
        display: flex;
        align-items: center;
        align-content: center;
    }

    /*.box{*/
    /*    border: none !important;*/
    /*    box-shadow: none !important;*/
    /*}*/


    .fullscreen {
        width: 100vw !important;
        height: 100vh !important;
        position: absolute;
        top: 0;
        left: 0;
    }

    /* .headings {

        margin-top:5px;
    } */

    .title {
        margin: 5px;
    }

    ::backdrop {
        background-color: white;
    }

    html {
        overflow-y: scroll !important;
    }

    body {
        padding-right: 0 !important;
    }

    .grid {
        position: relative;
    }
    .item {
        display: block;
        position: absolute !important;
        margin-bottom: 2rem;
        z-index: 1;
    }
    .item.muuri-item-dragging {
        z-index: 3;
    }
    .item.muuri-item-releasing {
        z-index: 2;
    }
    .item.muuri-item-hidden {
        z-index: 0;
    }
    .item-content {
        position: relative;
        width: 100%;
        height: 100%;
    }


</style>
<style>
    /* .container{ border: 2px solid green }
.grid-item{ border: 2px solid red; padding: 20px;}
.grid{ border: 1px solid blue} */
    .filters ul {
        display: flex;
        justify-content: space-between;
        list-style: none;
        border-bottom: 2px solid #D9D9D9;
        margin: 30px 15px
    }

    .filters ul li {
        display: inline-block;
        text-align: center;
        margin-right: 12px;
        padding: 0 5px 8px 5px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        position: relative;
        margin-bottom: 1rem;
        color: #777;
        transition: 0.3s;
        text-transform: uppercase;
    }

    .filters ul li:hover {
        color: #EB2D3A;
    }

    .filters ul li.is-checked {
        border-bottom: 2px solid #EB2D3A;
    }

    .filters ul li:last-child {
        margin-right: 0;
    }

    .loading-overlay {
        display: none;
        background: rgba(255, 255, 255, 0.7);
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: 0;
        z-index: 9998;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.is-active {
        display: flex;
        flex-direction: column;
    }


    @media screen and (max-width: 770px) {
        .filters-row {
            margin-top: 5rem;
        }
    }

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/muuri@0.9.5/dist/muuri.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/web-animations-js@2.3.2/web-animations.min.js"></script>
@section('content')
    <div class="loading-overlay">
        <span class="fas fa-spinner fa-3x fa-spin"></span>
        <span>Generating PDF Report</span>
    </div>


{{-- <script>
    function handleFilterChange(selectedFilter) {
        var headings = document.querySelectorAll('.headings');

        if (selectedFilter === 'All') {
            headings.forEach(function (heading) {
                heading.style.display = 'block';
            });
        } else {
            headings.forEach(function (heading) {
                heading.style.display = 'none';
            });
        }
    }
</script> --}}

<div class="row filters-row">
    <div class="col-md-12">
        <div class="filters">
            <ul>
                {{--// FILTERS //--}}
                <div>
                    <li class="is-checked" data-filter="item"
                     {{-- onclick="handleFilterChange('All') --}}
                    >All</li>
                    @foreach($co_cf_labels as $label)
                        <li data-filter="{{str_replace(' ', '_', $label)}}" onclick="handleFilterChange('{{$label}}')">{{$label}}</li>
                    @endforeach
                    <li data-filter="item">
                        <select class="filters-select" id="cwis-year-select" onchange="handleFilterChange(this.value)">
                            @foreach($years as $year)
                                @if($year == $selected_year)
                                    <option selected='selected' value="{{$year}}">{{$year}}</option>
                                @else
                                    <option value="{{$year}}">{{$year}}</option>
                                @endif
                            @endforeach
                        </select>
                    </li>
                </div>
                {{--// BUTTONS //--}}
                <div style="margin-left: auto">
                    <li data-filter="item">
                        <button id="export" class="btn btn-info">Export to Excel</button>
                    </li>
                    <li data-filter="item">
                        @csrf
                        <input type="hidden" name="chartImg" id="chartImg"/>
                        <input type="hidden" name="selected_year" id="selected_year" value=''/>
                        {{-- <button type="submit" id="report" class="btn btn-info">Generate PDF Report</button> --}}
                    </li>
                </div>
            </ul>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12 sections grid">
        @foreach($cwis_mne as $section_name => $section)
            <div class="col-sm-12 col-md-12 col-lg-12 item {{ $section[0]['data'][0]->labels }}" data-category="{{ $section[0]['data'][0]->labels }}" id="section_{{ $section[0]['data'][0]->labels }}" style="display:block">
                <div class="col-lg-12 col-md-12 col-sm-12 item-content">
                    <div class="section">
                       <div class="headings">
                           <h3 class="title">{{ $section[0]['data'][0]->indicator_code}}</h3>
                        </div>
                        @foreach($section as $key => $group)
                            @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "group" => $group,
                                "group_id" => str_replace([" ","&"],"_",$section_name)."_".$key
                            ])
                        @endforeach
                    </div><!--end of div section-->
                </div>
            </div>
        @endforeach
    </div>
</div>



    <script>
        var charts = [];
        var chartsDataURIs = {};
        const grid = new Muuri('.grid', { items: [],dragEnabled: true, });
        const initElements = document.querySelectorAll('.item');
        const initItems = grid.add(initElements, { active: false, layout: false });
        grid.show(initItems);
        // change is-checked class on buttons
        var $buttonGroup = $('.filters');
        $buttonGroup.on('click', 'li', function (event) {
            $buttonGroup.find('.is-checked').removeClass('is-checked');
            var $button = $(event.currentTarget);
            $button.addClass('is-checked');
            var filterValue = $button.attr('data-filter');
            grid.filter("."+filterValue);
        });
        $("#cwis-year-select").on("change", function () {
            var selected_value = $(this).val();
            $("#selected_year").val($("#cwis-year-select").val());
            window.location.replace("{{url('/')}}"+"/cwis/cwis-mne-dashboard?year=" + selected_value);
        });
        $("#export").click(function () {
            window.open("{{url('/')}}"+"/cwis/cwis-df-mne/export-mne-csv?year_select=" + $("#cwis-year-select").val());
        });
        $("#report").click(function (e) {
            e.preventDefault();
            $(".loading-overlay")[0].classList.toggle('is-active');

            var uriMap = {
                "row-1-1" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-0-0"],
                "row-1-2" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-0-1"],
                "row-1-3" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-0-2"],
                "row-2-1" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-1-2"],
                "row-2-2" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-2-2"],
                "row-3-1" : chartsDataURIs["Access-to-Toilets-&-Containment-Type-3-2"],
                "row-4-1" : chartsDataURIs["Desludging-Status-0-0"],
                "row-4-2" : chartsDataURIs["Desludging-Status-0-1"],
                "row-4-3" : chartsDataURIs["Desludging-Status-0-2"],
                "row-5-2" : chartsDataURIs["Desludging-Status-2-0"],
                "row-7-1" : chartsDataURIs["Disposal-and-Treatment-0-0"],
                "row-7-2" : chartsDataURIs["Disposal-and-Treatment-1-0"],
                "row-8-1" : chartsDataURIs["Disposal-and-Treatment-2-0"],
                "row-8-2" : chartsDataURIs["Disposal-and-Treatment-3-0"],
                "row-9-1" : chartsDataURIs["Outcome-Indicators-0-0"],
                "row-10-1" : chartsDataURIs["Outcome-Indicators-1-0"],
                "row-10-2" : chartsDataURIs["Reuse-0-0"],
            };
            var req = new XMLHttpRequest();
            req.open("POST", "{{url('/')}}"+'/cwis/cwis-mne-dashboard/report', true);
            req.responseType = "blob";
            req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            req.setRequestHeader(
                'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'),
            );
            req.setRequestHeader(
                "Content-Type", "application/json",
                "Accept", "application/pdf"
            );

            req.onload = function (event) {
                var blob = req.response;
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download="CWIS M&E " + $("#cwis-year-select").val() + ".pdf";
                link.click();
            };

            req.onreadystatechange = function() {
                if (req.readyState === 4) {
                    if (req.status === 200) {
                        $(".loading-overlay")[0].classList.toggle('is-active');
                    } else {
                        Swal.fire({
                            title: 'Failed to generate PDF report!',
                            icon: "error",
                            button: "Close",
                            className: "custom-swal",
                        })
                    }
                }
            }



            req.send(JSON.stringify({data : uriMap}));

        });
        $(document).ready(function (){
            $("#selected_year").val($("#cwis-year-select").val());
        });

    </script>

    @foreach($charts as $chart)
        {{$chart->script()}}
    @endforeach
@stop
