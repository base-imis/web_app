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
        margin-right: 80px;
        /* Adjust the margin as needed */
        width: 50%;

        @media screen and (max-width: 540px) {
            width: 100%;
            margin-right: 0;
        }

        .chart__figure {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            height: 140px;
            /* Adjust the height as needed */

            @media screen and (max-width: 540px) {
                flex-direction: column;
                height: auto;
                margin-bottom: 0;
            }
        }

        .chart__canvas {
            margin: auto;
            width: 160px;
            height: 140px;
            /* Adjust the height as needed */
        }

        .chart__caption {
            display: flex;
            justify-content: center;
            flex-direction: column;
            margin-left: 30px;
            font-size: 36px;
            line-height: 56px;
            height: 100%;
            width: calc(80px + 160px);
            color: #01713c;

            @media screen and (max-width: 540px) {
                margin: 15px auto auto;
                text-align: center;
                min-width: 160px;
            }
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
            text-align: center;
            /* Center the text */

            @media screen and (max-width: 540px) {
                left: 0;
                right: 0;
                width: 100%;
            }

            p {
                font-size: 20px;
                margin: auto;
                font-family: "Barlow Condensed", sans-serif;
            }
        }
    }

    .hi {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        width: fit-content;
        height: fit-content;
    }

    /* Styles for each card container */
    .card {
        border: 1px solid rgb(231, 227, 227);
        width: 420px;
        height: 350px;
        margin: 10px 10px;
        display: grid;
        padding: 2px;
        place-items: center;
        background-color: #F4F9F7;
    }

    .card4 {
        width: 825px;
        place-items: none;
    }

    .card img {
        width: 200px;
        height: 200px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
    }

    /* heading */
    .card h2 {
        font-size: 12px;
    }

    /* paragraph */
    .card p {
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
</style>
<div class="hi">
    <div class="card card1">
         <div class="card-header">
        <h2>Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellat eveniet voluptatibus maxime blanditiis, facilis quos, sed quisquam quo quod saepe quis laudantium quasi. Tempore nulla maiores minima, consectetur eveniet quidem.</h2>
      </div>
      <div class="chart" id="charteq1Container">
        <figure class="chart__figure">
            <canvas class="chart__canvas" id="charteq1Canvas" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>

        </figure>
    </div>
    </main><p></p>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>


</div>
<script>

    var percent = 30;
    var color = '#6CB4EE';
    var canvas = 'charteq1Canvas';
    var container = 'charteq1Container';

    var percentValue = percent; // Sets the single percentage value
    var colorGreen = color; // Sets the chart color
    var animationTime = '1400'; // Sets speed/duration of the animation

    var charteq1Canvas = document.getElementById(canvas); // Sets canvas element by ID
    var charteq1Container = document.getElementById(container); // Sets container element ID

    var divElement = document.createElement('div'); // Create element to hold and show percentage value in the center on the chart
    var domString = '<div class="chart__value"><p style="color: #6CB4EE">' + percentValue + '%</p></div>'; // String holding markup for above created element

    // Create a new Chart object
    var doughnutChart = new Chart(charteq1Canvas, {
        type: 'doughnut', // Set the chart to be a doughnut chart type
        data: {
            datasets: [
                {
                    data: [percentValue, 100 - percentValue], // Set the value shown in the chart as a percentage (out of 100)
                    backgroundColor: [colorGreen], // The background color of the filled chart
                    borderWidth: 0 // Width of border around the chart
                }
            ]
        },
        options: {
            cutoutPercentage: 78, // The percentage of the middle cut out of the chart
            responsive: true, // Set the chart to not be responsive
            tooltips: {
                enabled: false // Hide tooltips
            }
        }
    });

    Chart.defaults.global.animation.duration = animationTime; // Set the animation duration

    divElement.innerHTML = domString; // Parse the HTML set in the domString to the innerHTML of the divElement
    charteq1Container.appendChild(divElement.firstChild);
    // Append the divElement within the charteq1Container as its child

    function generateChartImage() {
        var tempCanvas = document.createElement('canvas'); // Create a temporary canvas
        var tempCtx = tempCanvas.getContext('2d');

        // Set canvas size
        tempCanvas.width = charteq1Canvas.width;
        tempCanvas.height = charteq1Canvas.height;

        // Draw the doughnut chart
        tempCtx.drawImage(charteq1Canvas, 0, 0);

        // Draw the percentage value
        var percentText = percentValue + '%';
        tempCtx.fillStyle = '#6CB4EE';
        tempCtx.font = '24px Arial'; // Customize font size and style as needed
        tempCtx.textAlign = 'center';
        tempCtx.fillText(percentText, tempCanvas.width / 2, tempCanvas.height / 2);

        return tempCanvas.toDataURL();
    }

    function downloadPDF() {
        var chartImage = generateChartImage();

        // Use Ajax to send the image data to the server
        fetch('/chart/download-pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                chartImage: chartImage,
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to download PDF');
            }
            return response.blob();
        })
        .then(blob => {
            // Convert blob to downloadable file
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'chart_pdf.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
{{-- <button onclick="downloadPDF()">Generate PDF</button> --}}
<a href="{{ route('generate-pdf') }}" download="" >Download </a>
</div>
