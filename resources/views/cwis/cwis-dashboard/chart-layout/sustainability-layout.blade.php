<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Chart.js library -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script> --}}
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
            }

            .sus,
            .sus4 {
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
            margin-right: 80px; /* Adjust the margin as needed */
            width: 50%;

            @media screen and (max-width: 540px) {
                width: 100%;
                margin-right: 0;
            }

            .chart__figure {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center items horizontally */
    justify-content: center; /* Center items vertically */
    margin-bottom: 20px;
    height: 100%; /* Make sure the figure takes 100% height of its container */
}

.chart__canvas {
    width: 160px;
    height: 140px;
}
.chart__caption {
    display: flex;
    justify-content: center;
    align-items: center; /* Center items vertically */
    flex-direction: column;
    margin-left: 30px;
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
    p {
    font-size: 25px;
    margin: auto;
    font-family: "Barlow Condensed", sans-serif;
}
}


        }





        /* Styles for each sus container */
        .sus {
            border: 1px solid rgb(231, 227, 227);
            width: 525px;
        height: 400px;
            margin: 10px 10px;
            display: grid;
            padding: 2px;
            background-color: #F4F9F7;
        }

        .sus img {
            width: 200px;
            height: 200px;
        }

        .sus-header {
            width: 100%;
            text-align: left; /* Adjusted alignment to left */
            margin-bottom: 10px;
            padding-bottom: 5px; /* Added padding for space between border and text */
            border-bottom: 1px solid #ccc; /* Added border-bottom */
        }

        /* heading */
        .sus h2 {
            font-size: 12px;
        }

        /* paragraph */
        .sus p {
            font-size: 15px;
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
        .card-header {
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
            height: 60px;
        }
    </style>
</head>
<body>

<div class="hi">
    <div class="sus sus1">
        <div class="card-header">
        Treated FS that is reused
        </div>
        <div class="chart" id="ss1Contain">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="ss1Canva" width="160" height="140" aria-label="" role="img"></canvas>
            </figure>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Use a ternary operator to check if $ss1[0] is set and not null
                const dataValue = {{ isset($ss1[0]) && $ss1[0] !== null ? ($ss1[0]->data_value) : 0 }};
                createDoughnutChart(dataValue, '#E49B0F', 'ss1Canva', 'ss1Contain');
            });
        </script>
    </div>
</div>


</body>
</html>
