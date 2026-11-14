<!-- Last Modified Date: 16-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', __('CWIS Dashboard'))
@section('content')
<style>
    h1 {
        margin: 0;
        font-size: 36px;
        width: 100%;

    }

    .tabs {
        display: flex;
        flex-wrap: wrap;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
    }

    .input {
        position: absolute;
        opacity: 0;
    }

    .label {
        width: 100%;
        padding: 20px 30px;
        cursor: pointer;
        font-weight: bold;
        font-size: 18px;
        color: #7f7f7f;
        transition: background 0.1s, color 0.1s;
        height: 50%;
    }

    .label:hover {
        background: #d8d8d8;
    }

    .label:active {
        background: #ccc;
    }

    .input:focus+.label {
        z-index: 1;
    }

    .input:checked+.label {
        background: #fff;
        color: #000;
    }

    @media (min-width: 600px) {
        .label {
            width: auto;
        }
    }

    .panel {
        display: none;
        padding: 20px 30px 30px;
        width: 100%;
    }

    @media (min-width: 600px) {
        .panel {
            display: none;
            padding: 20px 30px 30px;
            width: 100%;
            order: 99;
        }
    }

    .input:checked+.label+.panel {
        display: block;
    }

    @media (max-width: 600px) {
        .input:checked+.label+.panel {
            display: none;
        }
        #tab-accountability:checked + .label + .panel,
        #tab-rpm:checked + .label + .panel {
            display: block;
            padding: 16px 0;
        }
    }

    .select-dropdown,
    .select-dropdown * {
        margin-left: 10px;
        padding: 0;
        position: relative;
        box-sizing: border-box;
        height: 50%;
        margin-top: 1%;
        font-weight: bold;
        color: #424242;
        font-family: 'Bree Serif', serif;
        border: 1px #1d6086;
        border-style: groove;
    }

    .select-dropdown {
        position: relative;
        border-radius: 4px;
    }

    .select-dropdown select {
        font-size: 1rem;
        font-weight: normal;
        max-width: 100%;
        padding: 8px 24px 8px 10px;
        border: none;
        background-color: transparent;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .select-dropdown select:active,
    .select-dropdown select:focus {
        outline: none;
        box-shadow: none;
    }

    .select-dropdown:after {
        content: "";
        position: absolute;
        top: 50%;
        right: 8px;
        width: 0;
        height: 0;
        margin-top: -2px;
        border-top: 5px solid #aaa;
        border-right: 5px solid transparent;
        border-left: 5px solid transparent;
    }

    .buttons-container {
        margin-left: auto;
        display: flex;
    }

    .export-button,
    .pdf {
        margin-top: 8%;
        height: 40px;
        padding: 5px;
        font-size: 15px;
        cursor: pointer;
        background: #17A1B7;
        color: white;
        border: none;
        border-radius: 5px;
    }

    .export-button:hover,
    .pdf:hover {
        background: rgb(6, 110, 126);
    }

    .export-button,
    .pdf {
        margin-right: 10px;
    }

    .hi {
        display: flex;
        flex-wrap: wrap;

    }
    #loader-overlay {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6); /* Semi-transparent black */
        z-index: 9999; /* High priority */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Loader content */
    .loader-content {
        text-align: center;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px;
    }

    /* Spinner icon */
    .fa-spinner {
        font-size: 30px;
        margin-bottom: 10px;
    }
</style>
@if ($hasInvalidValues)
<!-- Bootstrap Modal -->
<div class="modal fade" id="invalidDataModal" tabindex="-1" role="dialog" aria-labelledby="invalidDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        
            <div class="modal-body text-center">
                <p>
                    {{__('Some indicators have been calculated as')}} <strong>NaN</strong> {{__('(Not a Number)')}} or <strong>NA</strong> {{__('(Not Available)')}}.
                </p>
                <p>
                    {{__('This happens due to one or more of the following reasons')}}:
                </p>
                <ul class="text-left">
                    <li><strong>NA:</strong>{{__('Occurs when the numerator or denominator is missing')}}.</li>
                    <li><strong>NaN:</strong> {{__('Happens when')}}:
                        <ul>
                            <li>{{__('The denominator is zero')}} {{__('(division by zero)')}}.</li>
                            <li>{{__('Both the numerator and denominator are zero')}} {{__('(undefined result)')}}.</li>
                        </ul>
                    </li>
                </ul>
             
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
               
            </div>
        </div>
    </div>
</div>
@endif

@if ($hasInvalidValues)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#invalidDataModal').modal('show');
    });
</script>
@endif
<script>
    /**
     * Creates a doughnut chart with the given percentage value and color, and appends it to a specified container.
     *
     * @param {number} percent - The percentage value to represent in the chart.
     * @param {string} color - The color of the chart.
     * @param {string} canvasId - The ID of the canvas element to render the chart.
     * @param {string} containerId - The ID of the container element to append the chart's value display.
     * @returns {void}
     */
    function createDoughnutChart(percent, color, canvasId, containerId) {
        // Retrieve canvas and container elements
        var canvas = document.getElementById(canvasId);
        var container = document.getElementById(containerId);

        // Ensure percent value is within bounds
        var percentValue = percent;
        var colorGreen = color;
        var animationTime = '1400';
        if (percent > 100) {
            percentValue = 100;
        }
        // Create a div element to display the chart's value
        var divElement = document.createElement('div');
        let domString = (percentValue === 'NaN' || isNaN(percentValue))
    ? '<div class="chart__value"><p style="color: ' + colorGreen + '">' + percent + '</p></div>'
    : '<div class="chart__value"><p style="color: ' + colorGreen + '">' + percent + '%</p></div>';
        // Set data values for the chart
        var dataValues = percentValue > 100 ? [100, 0] : [percentValue, 100 - percentValue];
        // Create the doughnut chart using Chart.js library
        var doughnutChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: dataValues,
                    backgroundColor: [colorGreen],
                    borderWidth: 0
                }]
            },
            options: {
                cutoutPercentage: 78,
                responsive: true,
                tooltips: {
                    enabled: false
                }
            }
        });
        // Set animation duration for the chart
        Chart.defaults.global.animation.duration = animationTime;
        // Append the chart's value display to the container
        divElement.innerHTML = domString;
        container.appendChild(divElement.firstChild);
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle dropdown change event
        $("#cwis-year-select").on("change", function() {
            var selectedYear = $(this).val();
            if (selectedYear) {
                // Update the URL with the selected year as a query parameter
                var newUrl = new URL(window.location.href);
                newUrl.searchParams.set('year', selectedYear);
                window.location.href = newUrl.toString();
            }
        });

        // On page load, set the selected year in the dropdown based on the URL parameter
        var urlParams = new URLSearchParams(window.location.search);
        var yearParam = urlParams.get('year');
        var latestYear = <?php echo $latestYear; ?>;

        if (yearParam) {
            // If the 'year' parameter exists in the URL, set it as the selected value
            $("#cwis-year-select").val(yearParam);
        } else {
            // If no 'year' parameter, default to the latest year
            $("#cwis-year-select").val(latestYear);
            // Optionally, update the URL to include the latest year
            var newUrl = new URL(window.location.href);
            newUrl.searchParams.set('year', latestYear);
            window.history.replaceState({}, '', newUrl.toString());
        }

        // Handle the export button click event
        $("#export").click(function() {
            var selectedYear = $("#cwis-year-select").val();
            if (!selectedYear) {
                alert('Please select a year.');
            } else {
                // Open the export URL corresponding to the selected year
                window.open("{{ url('cwis/export-csv') }}/" + selectedYear);
            }
        });
    });
</script>
<p id="cwis-dashboard-country">{{ __('Country') }}: <strong>{{ $countryLabel }}</strong></p>
@if($resultCountryMismatch)
    <div class="alert alert-info" role="status">{{ __('Saved results for this year belong to another country selection. No values are shown for the selected country until its results are generated.') }}</div>
@endif
@if(isset($noDataMessage))
        <div class="alert alert-warning">
            {{ $noDataMessage }}
        </div>
    @else
<div class="tabs">
    <input class="input" name="tabs" type="radio" id="tab-1" checked="checked" />
    <label class="label" for="tab-1">{{ __('All') }}</label>
    <div class="panel">
        @if($equityIndicators->isNotEmpty())
            <h1>{{__('Equity')}}</h1>
            @include('cwis.cwis-dashboard.chart-layout.country-equity-cards')
        @endif
        @if($safetyIndicators->isNotEmpty())
            <h1>{{__('Safety')}}</h1>
            @include('cwis.cwis-dashboard.chart-layout.safety-layout')
            @include('cwis.cwis-dashboard.chart-layout.safety-d22-cards', ['safetyChartPrefix' => 'all'])
        @endif
        @if($sustainabilityIndicators->isNotEmpty())
            <h1>{{__('Sustainability')}}</h1>
            @include('cwis.cwis-dashboard.chart-layout.country-sustainability-cards')
        @endif
        @if($responsibilityIndicators->isNotEmpty())
            <h1>{{ __('Responsibility') }}</h1>
            @include('cwis.cwis-dashboard.chart-layout.country-responsibility-cards')
        @endif
        @if($accountabilityIndicators->isNotEmpty())
            <h1>{{ __('Accountability') }}</h1>
            @include('cwis.cwis-dashboard.chart-layout.country-accountability-cards')
        @endif
        @if($rpmIndicators->isNotEmpty())
            <h1>{{ __('Resource Planning and Management') }}</h1>
            @include('cwis.cwis-dashboard.chart-layout.country-rpm-cards')
        @endif
    </div>
    @if($equityIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-2" />
    <label class="label" for="tab-2">{{__('Equity')}}</label>
    <div class="panel">
        @include('cwis.cwis-dashboard.chart-layout.country-equity-cards')
    </div>
    @endif
    @if($safetyIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-4" />
    <label class="label" for="tab-4">{{__('Safety')}}</label>
    <div class="panel">
        <br>
        @include('cwis.cwis-dashboard.chart-layout.seperate-chart-layout.safety-card')
        @include('cwis.cwis-dashboard.chart-layout.safety-d22-cards', ['safetyChartPrefix' => 'tab'])
    </div>
    @endif
    
    @if($sustainabilityIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-sustainability" />
    <label class="label" for="tab-sustainability">{{ __('Sustainability') }}</label>
    <div class="panel">
        @include('cwis.cwis-dashboard.chart-layout.country-sustainability-cards')
    </div>
    @endif
    @if($responsibilityIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-responsibility" />
    <label class="label" for="tab-responsibility">{{ __('Responsibility') }}</label>
    <div class="panel">
        @include('cwis.cwis-dashboard.chart-layout.country-responsibility-cards')
    </div>
    @endif
    @if($accountabilityIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-accountability" />
    <label class="label" for="tab-accountability">{{ __('Accountability') }}</label>
    <div class="panel">
        @include('cwis.cwis-dashboard.chart-layout.country-accountability-cards')
    </div>
    @endif
    @if($rpmIndicators->isNotEmpty())
    <input class="input" name="tabs" type="radio" id="tab-rpm" />
    <label class="label" for="tab-rpm">{{ __('RPM') }}</label>
    <div class="panel">
        @include('cwis.cwis-dashboard.chart-layout.country-rpm-cards')
    </div>
    @endif
    <div class="select-dropdown">
        <select style="text-align: center;" id="cwis-year-select">
            <option value="">{{__('Select year')}}</option>
            @foreach($presentYears as $year)
            <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <div class="buttons-container">
        @can('Push CWIS Indicator to NSD')
        <button class="export-button" style="margin-right:none" id="nsd-push">Push Data to NSD</button>
        @endcan
        @can('Check Status of Indicator in NSD')
        <button class="export-button" style="margin-right:none" id="nsd-status">Check Publication status in NSD</button>
        @endcan
        <button class="export-button" style="margin-right:none" id="export">{{__("Export to Excel")}}</button>
        <button type="button" class="pdf" data-url="{{ url('cwis/chart/download-pdf') }}" data-year="{{ $selectedYear }}" data-country="{{ $country }}">{{__("Generate PDF")}}</button>
    </div>
</div>
<div id="loader-overlay" style="display: none;">
    <div class="loader-content">
        <i class="fa fa-spinner fa-spin"></i>
        <p>Loading...</p>
    </div>
    </div>

<script>
    $('#nsd-push').on('click', function (e) {
    e.preventDefault();

    let selectedYear = $('#cwis-year-select').val();
    if (!selectedYear) {
        toastr.error("Please select a year first.");
        return;
    }

    let url = "{{ url('fsm/nsd/push-nsd') }}/" + selectedYear;

    // Disable the button and show a loading spinner
    let $button = $(this);
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

    // Make an AJAX request to check and push data
    $.get(url, function(response) {
        if (response.error) {
            $button.prop('disabled', false).html('Push NSD'); // Re-enable button
        } else {
            setTimeout(function () {
                window.location.href = url;
            });
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
      toastr.error("CWIS Data Could Not be Pushed. Please Check NSD Integration Settings");
      $button.prop('disabled', false).html('Push CWIS Indicator to NSD'); // Re-enable button
    });
});



   $('#nsd-status').on('click', function (e) {
    e.preventDefault();
    let selectedYear = $('#cwis-year-select').val();
    if (!selectedYear) {
        toastr.error("Please select a year first.");
        return;
    }

    let url = "{{ url('fsm/nsd/cwis-status') }}" ;

    $.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        success: function (response) {
            console.log(response);            

            if (Array.isArray(response) && response.length > 0) {
                let data = response[0]; 
                let city = data.city;
                let publishedYears = data.published_years || [];
                let draftYears = data.draft_years || [];
               
                Swal.fire({
                    title: 'CWIS Status of Published and Draft Years',
                    html: `<p><strong>City:</strong> ${city}</p>
                        <p><strong>Published Years:</strong> ${publishedYears.length ? publishedYears.join(", ") : "None"}</p>
                           <p><strong>Draft Years:</strong> ${draftYears.length ? draftYears.join(", ") : "None"}</p>`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            } 
        },
        error: function (xhr, status, error) {
            Swal.fire({
                title: 'Error',
                text: "Unable to retrieve year data from NSD. Please verify your NSD integration settings",
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });
});

</script>
<script src="{{ asset('js/cwis-pdf-export.js') }}"></script>

@endif

@stop
