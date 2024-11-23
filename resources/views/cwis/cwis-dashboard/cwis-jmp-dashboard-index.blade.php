@extends('dashboard')
@section('title', $page_title)
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
        margin-left: 3rem;
        letter-spacing: 0.4px;
        font-size: 1.5rem;
        height: 100%;
        width: 100%;
        font-family: "Barlow Condensed", sans-serif;
        color: #01713c;
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
        color: #01713c;
    }

    .single-valued-chart-canvas-div {
        display: flex;
        flex-direction: column;
        align-content: center;
        justify-content: center;
        align-items: center;
    }

    .box-container {
        border: 5px whitesmoke;
        min-height: 350px;
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
        padding: 2rem;
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

    .safety-label {
        background-color: #01713c;
    }

    .equity-label {
        background-color: #00c0ef;
    }

    .sustainability-label {
        background-color: #c87f0a;
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
        background-color: #EFF2F4;
        width: 100%;
    }

    .section-header {
        padding: 1.5rem;
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

    .flexible{
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

    ::backdrop {
        background-color: white;
    }

    html {
        overflow-y: scroll !important;
    }

    body {
        padding-right: 0 !important;
    }


</style>
<style>
    /* .container{ border: 2px solid green }
.grid-item{ border: 2px solid red; padding: 20px;}
.grid{ border: 1px solid blue} */
    .filters ul {
        display: flex;
        justify-content: center;
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
        margin-bottom: -2px;
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


    /*@media screen and (max-width: 1250px) {*/
    /*    .grid-isotope {*/
    /*        width: 50% !important;*/
    /*    }*/
    /*}*/

    /*@media screen and (max-width: 1000px) {*/
    /*    .grid-isotope {*/
    /*        width: 100% !important;*/
    /*    }*/
    /*}*/


</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="filters">
                <ul>
                    <li class="is-checked" data-filter="*">All</li>
{{--                    @foreach($co_cf_labels as $label)--}}
{{--                        <li data-filter=".{{str_replace(' ', '_', $label)}}">{{$label}}</li>--}}
{{--                    @endforeach--}}
                    <li> <select class="filters-select" id="cwis-year-select">
                        @foreach($years as $year)
                            @if($year == $selected_year)
                                <option selected='selected' value="{{$year}}">{{$year}}</option>
                            @else
                            <option value="{{$year}}">{{$year}}</option>
                            @endif
                        @endforeach
                    </select>
                    </li>
                </ul>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="dropdown">

        </div>
    </div>
    <div class="row">
        <div class="col-md-12 sections">
            {{--            Access to Toilets & Containment Type--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row equity" id="section_equity">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Access to Toilets & Containment Type"])
                        <div class="section">
                            <div class="section-header">
                                <h1>Access to Toilets & Containment Type</h1>
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][0],
                                $cwis_mne["Access to Toilets & Containment Type"][1],
                                $cwis_mne["Access to Toilets & Containment Type"][2]],
                                "layout_id"=>"row_1",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"3"
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][3],$cwis_mne["Access to Toilets & Containment Type"][4]],
                                "layout_id"=>"row_2",
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][5],$cwis_mne["Access to Toilets & Containment Type"][6]],
                                "layout_id"=>"row_3",
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                            "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][7],$cwis_mne["Access to Toilets & Containment Type"][8]],
                            "layout_id"=>"row_4",
                            "chart_type"=>"bar",
                            "charts_in_box"=>"1"
                        ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
            {{--            Desludging Status--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row safety" id="section_safety">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Desludging Status"])
                        <div class="section">
                            <div class="section-header">
                                <h1>Desludging Status</h1>
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Desludging Status"][0],
                                $cwis_mne["Desludging Status"][2],
                                $cwis_mne["Desludging Status"][3]],
                                "layout_id"=>"row_5",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"3"
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Desludging Status"][1]],
                                "layout_id"=>"row_6",
                                "chart_type"=>"text",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Desludging Status"][4]],
                                "layout_id"=>"row_7",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Desludging Status"][5]],
                                "layout_id"=>"row_8",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
            {{--            Disposal and Treatment--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row safety" id="section_safety">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Disposal and Treatment"])
                        <div class="section">
                            <div class="section-header">
                                <h1>Disposal and Treatment</h1>
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][0],
                                $cwis_mne["Disposal and Treatment"][1]],
                                "layout_id"=>"row_51",
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][3],
                                $cwis_mne["Disposal and Treatment"][4]],
                                "layout_id"=>"row_52",
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][1]],
                                "layout_id"=>"row_71",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][5]],
                                "layout_id"=>"row_81",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
            {{--            Reuse--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row sustainability" id="section_sustainability">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Reuse"])
                        <div class="section">
                            <div class="section-header">
                                <h1>Reuse</h1>
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Reuse"][0]],
                                "layout_id"=>"row_512",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
            {{--            Outcome Indicators--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row safety" id="section_safety">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Outcome Indicators"])
                        <div class="section">
                            <div class="section-header">
                                <h1>Outcome Indicators</h1>
                            </div>
                            <div class="row">
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][0]],
                                "layout_id"=>"row_511",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                                @include("cwis.cwis-dashboard.chart-layout.chart-layout",[
                                "charts"=>[$cwis_mne["Outcome Indicators"][1]],
                                "layout_id"=>"row_521",
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1"
                            ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js"
            integrity="sha512-Zq2BOxyhvnRFXu0+WE6ojpZLOU2jdnqbrM1hmVdGzyeCa1DgM3X5Q4A/Is9xA1IkbUeDd7755dNNI/PzSf2Pew=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{--    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>--}}
    <script>
        var $section = $('.sections').isotope({
            // options
            itemSelector: '.grid-row',
            layoutMode: 'fitRows',
            percentPosition: true
        });
        // change is-checked class on buttons
        var $buttonGroup = $('.filters');
        $buttonGroup.on('click', 'li', function (event) {
            $buttonGroup.find('.is-checked').removeClass('is-checked');
            var $button = $(event.currentTarget);
            $button.addClass('is-checked');
            var filterValue = $button.attr('data-filter');
            $section.isotope({
                filter: filterValue
            });
        });
        $("#cwis-year-select").on("change",function(){
           var selected_value = $(this).val();
            var app_url = "{{\Illuminate\Support\Facades\Request::url()}}";
            window.location.replace(app_url+"?year="+selected_value);
        });
    </script>
@endsection
