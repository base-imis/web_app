<!-- Include Chart.js library -->
<style>
    /* Breakpoints */
    @media screen and (max-width: 540px) {
        .chart__figure {
            flex-direction: column;
            height: auto;
        }

        .chart__caption {
            margin: 15px auto auto;
            text-align: center;
            min-width: 160px;
        }

        .chart {
            width: 100%;
            margin-right: 0;
            margin-left: 0;
            /* Center-align on smaller screens */
        }

        .safety {
            width: 100%;
        }
    }

    /* Fonts (Google fonts) */
    .font--barlow {
        font-family: "Barlow Condensed", sans-serif;
    }

    .font--montserrat {
        font-family: "Montserrat", sans-serif;
    }

    .color--grey {
        color: #334466;
    }

    .color--green {
        color: #01713c;
    }

    /* Values */
    .canvas-size {
        width: 160px;
        height: 50px;
    }

    .font-weight--900 {
        font-weight: 900;
    }

    .animation-time--1400ms {
        animation-duration: 1400ms;
    }

    /* Fading animation */
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

    .main {
        display: grid;
    }

    .chart {
        position: relative;
        font-weight: 500;
        margin-right: auto;
        /* Center-align */
        margin-left: auto;
        /* Center-align */
        width: 50%;

        @media screen and (max-width: 540px) {
            width: 100%;
            margin-right: 0;
            margin-left: 0;
            /* Center-align on smaller screens */
        }

        .chart__figure {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            height: 100%;
        }

        .chart__canvas {
            width: 160px;
            height: 140px;
        }

        .chart__caption {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-left: auto;
            /* Center-align */
            margin-right: auto;
            /* Center-align */
            font-size: 36px;
            line-height: 56px;
            height: 100%;
            width: calc(80px + 160px);
            font-family: "Barlow Condensed", sans-serif;
            color: #01713c;
            border-bottom: 1px solid #ccc;
        }

        .chart__value {
            display: grid;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            height: calc(40px + 160px);
            width: 160px;
            animation: fadein 1400ms;
        }

        p {
            font-size: 20px;
            margin: auto;
            font-family: "Barlow Condensed", sans-serif;
        }
    }




    /* Styles for each safety container */
    .safety {
        border: 1px solid rgb(231, 227, 227);
        width: 500px;
        height: 400px;
        margin: 10px 10px;
        display: grid;
        padding: 2px;
        background-color: #F4F9F7;
    }

    .safety1,
    .safety2,
    .safety3,
    .safety4 {
        width: 100%;
        height: auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .sf1,
    .sf2,
    .sf3,
    .sf4 {
        width: calc(33.33% - 20px);
        margin-bottom: 20px;
        box-sizing: border-box;
    }

    .sf1:last-child,
    .sf2:last-child,
    .sf3:last-child,
    .sf4:last-child {
        margin-right: auto;
        margin-left: 2.5%;
    }

    .sf1 img,
    .sf2 img,
    .sf3 img,
    .sf4 img {
        width: 100%;
        height: auto;
        margin-bottom: 10px;
    }

    .safety img {
        width: 200px;
        height: 200px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
        height: 60px;
    }

    /* heading */
    .safety h2 {
        font-size: auto;
    }

    /* paragraph */
    .safety p {
        font-size: 15px;
        text-align: center;
    }

    span {
        position: absolute;
        top: 50%;
        left: 50%;
        text-align: center;
        font-size: 30px;
        margin-left: -25px;
        margin-top: -20px;
    }

    .chart-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
</style>
</head>

<body>
<div class="hi">
    <div class="safety safety1">
        <div class="card-header">
           
        </div>

        <br>
        <div class="sf1">
            <div class="chart" id="sf1aContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1aCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1a[0]) && $sf1a[0] !== null ? html_entity_decode($sf1a[0]->data_value) : 0  }},
                        '#29ab87', 
                        'sf1aCanvas', 
                        'sf1aContainer'
                    );
                });
            </script>
            <p>Population with access to safe individual toilets</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1bContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1bCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1b[0]) && $sf1b[0] !== null ? html_entity_decode($sf1b[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf1bCanvas', 
                        'sf1bContainer'
                    );
                });
            </script>
            <p>IHHL OSSs that have been desludged</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1cContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1cCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1c[0]) && $sf1c[0] !== null ? html_entity_decode($sf1c[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf1cCanvas', 
                        'sf1cContainer'
                    );
                });
            </script>
            <p>Collected FS disposed at treatment plant or designated disposal site</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1dContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1dCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1d[0]) && $sf1d[0] !== null ? html_entity_decode($sf1d[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf1dCanvas', 
                        'sf1dContainer'
                    );
                });
            </script>
            <p>FS treatment capacity as a % of total FS generated from non-sewered connections</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1eContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1eCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1e[0]) && $sf1e[0] !== null ? html_entity_decode($sf1e[0]->data_value) : '0' }},
                        '#29ab87', 
                        'sf1eCanvas', 
                        'sf1eContainer'
                    );
                });
            </script>
            <p>FS treatment capacity as a % of volume disposed at the treatment plant</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1fContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1fCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1g[0]) && $sf1g[0] !== null ? html_entity_decode($sf1g[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf1fCanvas', 
                        'sf1fContainer'
                    );
                });
            </script>
            <p>WW treatment capacity as a % of total WW generated from sewered connections and 
            greywater and supernatant generated from non-sewered connections</p>
        </div>
        <div class="sf1">
            <div class="chart" id="sf1gContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf1gCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf1g[0]) && $sf1g[0] !== null ? html_entity_decode($sf1g[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf1gCanvas', 
                        'sf1gContainer'
                    );
                });
            </script>
            <p>Effectiveness of FS treatment in meeting prescribed standards for effluent 
            discharge and biosolids disposal</p>
        </div>
    </div>

    <div class="safety safety2">
        <div class="card-header">
           
        </div>
        <br>
        <div class="sf2">
            <div class="chart" id="sf2aContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf2aCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf2a[0]) && $sf2a[0] !== null ? html_entity_decode($sf2a[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf2aCanvas', 
                        'sf2aContainer'
                    );
                });
            </script>
            <p>Low income community (LIC) population with access to safe individual toilets</p>
        </div>
        <div class="sf2">
            <div class="chart" id="sf2bContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf2bCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf2b[0]) && $sf2b[0] !== null ? html_entity_decode($sf2b[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf2bCanvas', 
                        'sf2bContainer'
                    );
                });
            </script>
            <p>LIC OSSs that have been desludged</p>
        </div>
        <div class="sf2">
            <div class="chart" id="sf2cContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf2cCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    createDoughnutChart(
                        {{ isset($sf2c[0]) && $sf2c[0] !== null ? html_entity_decode($sf2c[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf2cCanvas', 
                        'sf2cContainer'
                    );
                });
            </script>
            <p>FS collected from LIC that is disposed at treatment plant or designated 
            disposal site</p>
        </div>
        
    </div>

    <div class="safety safety3">
        <div class="card-header">
           
        </div>
        <br>
        <div class="sf3">
            <div class="chart" id="sf3aContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf3aCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    createDoughnutChart(
                        {{ isset($sf3a[0]) && $sf3a[0] !== null ? html_entity_decode($sf3a[0]->data_value) : 0 }},
                        '#29ab87', 
                        'sf3aCanvas', 'sf3aContainer'
                    );
                });
            </script>
            <p>Dependent population (without IHHL) with access to safe shared facilities</p>
        </div>
        <div class="sf3">
            <div class="chart" id="sf3bContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf3bCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
              document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf3b[0]) && $sf3b[0] !== null ? html_entity_decode($sf3b[0]->data_value) : 0 }},
        '#29ab87', 'sf3bCanvas', 'sf3bContainer'
    );
});
            </script>
            <p>Shared facilities that adhere to principles of universal design</p>
        </div>
        <div class="sf3">

            <div class="chart" id="sf3cContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf3cCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf3c[0]) && $sf3c[0] !== null ? html_entity_decode($sf3c[0]->data_value) : 0 }},
        '#29ab87', 'sf3cCanvas', 'sf3cContainer'
    );
});
            </script>
            <p>Shared facility users who are women</p>
        </div>
        <div class="sf3">
             @include("cwis.cwis-dashboard.charts.safety.sf-3.sf-3e-chart")<p>Average distance from HH to shared facility (m)</p>
        </div>

    </div>


    <div class="safety safety4">
        <div class="card-header">
          
        </div>
        <br>
       
        <div class="sf4">
            <div class="chart" id="sf4aContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf4aCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
              document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf4a[0]) && $sf4a[0] !== null ? html_entity_decode($sf4a[0]->data_value) : 0 }},
        '#29ab87', 'sf4aCanvas', 'sf4aContainer'
    );
});
            </script>
            <p>PT where FS generated is safely transported to TP or safely disposed 
                in situ</p>
        </div>
        <div class="sf4">
            <div class="chart" id="sf4bContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf4bCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
               document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf4b[0]) && $sf4b[0] !== null ? html_entity_decode($sf4b[0]->data_value) : 0 }},
        '#29ab87', 'sf4bCanvas', 'sf4bContainer'
    );
});
            </script>
            <p>PT that adhere to principles of universal design</p>
        </div>
        <div class="sf4">

            <div class="chart" id="sf4dContainer">
                <figure class="chart__figure">
                    <canvas class="chart__canvas" id="sf4dCanvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

                </figure>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf4d[0]) && $sf4d[0] !== null ? html_entity_decode($sf4d[0]->data_value) : 0 }},
        '#29ab87', 'sf4dCanvas', 'sf4dContainer'
    );
});
            </script>
            <p>PT users who are women</p>
        </div>
       
    </div>



    <div class="safety safety5" style="margin-left">
        <div class="card-header">
            Educational institutions where FS generated is safely transported to TP 
or safely disposed in situ
        </div>
        <div class="chart" id="sf5Container">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="sf5Canvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

            </figure>
        </div>
        <script>
         document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf5[0]) && $sf5[0] !== null ? html_entity_decode($sf5[0]->data_value) : 0 }},
        '#29ab87', 'sf5Canvas', 'sf5Container'
    );
});
        </script>
    </div>

    <div class="safety safety6">
        <div class="card-header">
            Healthcare facilities where FS generated is safely transported to TP or 
            safely disposed in situ
        </div>
        <div class="chart" id="sf6Container">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="sf6Canvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

            </figure>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf6[0]) && $sf6[0] !== null ? html_entity_decode($sf6[0]->data_value) : 0 }},
        '#29ab87', 'sf6Canvas', 'sf6Container'
    );
});
        </script>
    </div>


    <div class="safety safety7">
        <div class="card-header">
            Desludging services completed mechanically or semi-mechanically 
        </div>
        <div class="chart" id="sf7Container">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="sf7Canvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

            </figure>
        </div>
        <script>
           document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf7[0]) && $sf7[0] !== null ? html_entity_decode($sf7[0]->data_value) : 0 }},
        '#29ab87', 'sf7Canvas', 'sf7Container'
    );
});
        </script>

    </div>

    <div class="safety safety9">
        <div class="card-header">
        % of water contamination compliance (on fecal coliform)
        </div>
        <div class="chart" id="sf9Container">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="sf9Canvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

            </figure>
        </div>
        <script>
           document.addEventListener('DOMContentLoaded', function () {
    createDoughnutChart(
        {{ isset($sf9[0]) && $sf9[0] !== null ? html_entity_decode($sf9[0]->data_value) : 0 }},
        '#29ab87', 'sf9Canvas', 'sf9Container'
    );
});
        </script>

    </div>
</div>
