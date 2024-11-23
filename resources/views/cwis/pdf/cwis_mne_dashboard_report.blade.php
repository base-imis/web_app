<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="{{ public_path('css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td {
            border: none;
            border-collapse: collapse;
        }

        tr:nth-child(even) {
            background-color: #ddddde;
            border: 0.5px solid;
        }

        .text-right {
            text-align: right !important;
        }

    </style>
    <style>
        .row {
            display: -webkit-box;
            display: flex;
            -webkit-box-pack: center;
            justify-content: center;
        }
        .row > div {
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            flex: 1;
        }
        .row > div:last-child {
            margin-right: 0;
        }

        .chart-row {
            display: -webkit-box;
            display: -webkit-flex;;
            /*align-content: center;*/
            flex-wrap: wrap;
        }

        .row-header {
            display: -webkit-flex;;
            align-content: center;
        }

        .single-valued-chart {
            position: relative;
            margin: auto;
            font-weight: 900;
        }

        .single-valued-chart-figure {
            display: -webkit-flex;;
            align-content: center;
            webkit-justify-content: center;
            align-items: center;
            text-align: center;
        }

        @media screen and (max-width: 1350px) {
            .single-valued-chart-figure {
                flex-direction: column;
                height: auto;
            }

        }

        .single-valued-chart-caption {
            flex-wrap: wrap;
            display: -webkit-flex;;
            justify-content: center;
            flex-direction: column;
            margin-left: 3rem;
            letter-spacing: 0.4px;
            font-size: 1.5rem;
            height: 100%;
            max-width: 440px;
            font-family: "Barlow Condensed", sans-serif;
            padding: 1rem;
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
        }

        .single-valued-chart-value p {
            font-size: 2rem;
            margin: auto;
            padding-left: 6px;
            font-family: "Barlow Condensed", sans-serif;
            color: #01713c;
        }

        .single-valued-chart-canvas-div {
            display: -webkit-flex;
            flex-direction: column;
            align-content: center;
            justify-content: center;
            align-items: center;
        }

        .chart-div {
            /*min-width: 750px;*/
            margin-bottom: 10em;
            min-width: 300px;
        }

        .gauge-chart {
            max-height: 150px;
            max-width: 150px;
            min-height: 150px;
            min-width: 150px;
        }

        .gauge-container {
            width: 100%;
            display: -webkit-flex;;
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
            display: -webkit-flex;;
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
            display: -webkit-flex;;
            flex-direction: column;
            align-items: center;
            justify-content: space-evenly;
        }

        .single-valued-num-chart-text, .single-valued-num-chart-number {
            font-size: 3em;
            color: rgb(54, 162, 235);
            font-weight: bold;
            line-height: 1.75;
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
            display: -webkit-flex;;
            justify-content: center;
        }

        .content-wrapper {
            min-height: 100% !important;
        }

        .section {
            grid-area: section;
            width: 100%;
        }

        div.page
        {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .section-header {
            padding: 1.5rem;
        }

        .section-header h1 {
            font-size: 2.5rem;
        }

        .flexible{
            display: -webkit-flex;;
            align-items: center;
            align-content: center;
        }


    </style>
</head>

<body>
<div class="container">
    <div style="width:100%; height:auto;align-content: center;webkit-justify-content: center;align-items: center;text-align: center">
      {{--<img src="data:image/svg+xml;base64,'.{{base64_encode(file_get_contents(public_path("img/svg/-logo.svg")))}}" style="height:250px;width:500px;">--}}
        <div style="text-align:center;">
            <h1 class="heading" style="text-transform:uppercase;">Municipality</h1>
          <h2 style="text-transform:uppercase;">CWIS Monitoring and Evaluation Report {{$selected_year}}</h2> 
            <h3 style="text-align:center;text-transform:uppercase; font-family:Monospace; border-style:solid; border-width:1px;">
                Integrated Municipal Information System
            </h3>
        </div>
    </div>

<div class="row">
        <div class="col-md-12 sections">
            {{--            Access to Toilets & Containment Type--}}
            <div class="col-sm-12 col-md-12 col-lg-12 grid-row equity" id="section_equity">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    @isset($cwis_mne["Access to Toilets & Containment Type"])
                        <div class="section page">
                            <div class="section-header">
                                <h1>Access to Toilets & Containment Type</h1>
                            </div>
                            <div class="row">
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][0],
                                $cwis_mne["Access to Toilets & Containment Type"][1],
                                $cwis_mne["Access to Toilets & Containment Type"][2]],
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"3",
                                "sources" => [$charts["row-1-1"],$charts["row-1-2"],$charts["row-1-3"]]
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][3],$cwis_mne["Access to Toilets & Containment Type"][4]],
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1",
                                "sources" => [$charts["row-2-1"]]
                            ])
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][5],$cwis_mne["Access to Toilets & Containment Type"][6]],
                                "chart_type"=>"bar",
                                "charts_in_box"=>"1",
                                "sources" => [$charts["row-2-2"]]
                            ])
                            </div>
                            <div class="row">
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                            "charts"=>[$cwis_mne["Access to Toilets & Containment Type"][7],$cwis_mne["Access to Toilets & Containment Type"][8]],
                            "chart_type"=>"bar",
                            "charts_in_box"=>"1",
                                "sources" => [$charts["row-3-1"]]
                        ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
            <div class="page">
                {{--            Desludging Status--}}
                <div class="col-sm-12 col-md-12 col-lg-12 grid-row safety" id="section_safety">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @isset($cwis_mne["Desludging Status"])
                            <div class="section">
                                <div class="section-header">
                                    <h1>Desludging Status</h1>
                                </div>
                                <div class="row">
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Desludging Status"][0],
                                    $cwis_mne["Desludging Status"][2],
                                    $cwis_mne["Desludging Status"][3]],
                                    "chart_type"=>"percentage",
                                    "charts_in_box"=>"3",
                                    "sources" => [$charts["row-4-1"],$charts["row-4-2"],$charts["row-4-3"]]
                                ])
                                </div>
                                <div class="row">
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Desludging Status"][1]],
                                    "chart_type"=>"text",
                                    "charts_in_box"=>"1",
                                ])
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Desludging Status"][4]],
                                    "chart_type"=>"percentage",
                                    "charts_in_box"=>"1",
                                    "sources" => [$charts["row-5-2"]]
                                ])

                                </div>
                                <div class="row">
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Desludging Status"][5]],
                                    "chart_type"=>"ratio",
                                    "charts_in_box"=>"1",
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
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Disposal and Treatment"][0],
                                    $cwis_mne["Disposal and Treatment"][1]],
                                    "chart_type"=>"bar",
                                    "charts_in_box"=>"1",
                                    "sources" => [$charts["row-7-1"]]
                                ])
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Disposal and Treatment"][3],
                                    $cwis_mne["Disposal and Treatment"][4]],
                                    "chart_type"=>"bar",
                                    "charts_in_box"=>"1",
                                    "sources" => [$charts["row-7-2"]]
                                ])
                                </div>
                                <div class="row">
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Disposal and Treatment"][1]],
                                    "chart_type"=>"percentage",
                                    "charts_in_box"=>"1",
                                    "sources" => [$charts["row-8-1"]]
                                ])
                                    @include("cwis.pdf.cwis-mne.chart-layout",[
                                    "charts"=>[$cwis_mne["Disposal and Treatment"][5]],
                                    "chart_type"=>"percentage",
                                    "charts_in_box"=>"1",
                                    "sources" => [$charts["row-8-2"]]
                                ])
                                </div>
                            </div><!--end of div section-->
                        @endisset
                    </div>
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
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Reuse"][0]],
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1",
                                "sources" => [$charts["row-9-1"]]
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
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Disposal and Treatment"][0]],
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1",
                                "sources" => [$charts["row-10-1"]]
                            ])
                                @include("cwis.pdf.cwis-mne.chart-layout",[
                                "charts"=>[$cwis_mne["Outcome Indicators"][1]],
                                "chart_type"=>"percentage",
                                "charts_in_box"=>"1",
                                "sources" => [$charts["row-10-2"]]
                            ])
                            </div>
                        </div><!--end of div section-->
                    @endisset
                </div>
            </div>
        </div>
    </div> 


</div>

</body>


</html>
