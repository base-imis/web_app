@include('layouts.dashboard.chart-card', [
    'card_title' => "Ward-Wise Presence of Water Supply Pipelines",
    'export_chart_btn_id' => "exportPipeCodePresenceWard",
    'canvas_id' => "pipeCodePresenceWard"
])
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data from controller
    var sqlResult = @json($pipeCodePresenceWard);

    // Extract ward names and counts
    var wardLabels = sqlResult.map(item => item.ward);
    var buildingsWithPipeCode = sqlResult.map(item => item.buildings_with_pipe_code);
    var buildingsWithoutPipeCode = sqlResult.map(item => item.buildings_without_pipe_code);

    // Chart data setup
    var chartData = {
        labels: wardLabels,
        datasets: [
            {
                label: "Yes",
                backgroundColor: "#89CFF0",
                data: buildingsWithPipeCode
            },
            {
                label: "No",
                backgroundColor: "#808080",
                data: buildingsWithoutPipeCode
            }
        ]
    };

    var options = {
        scales: {
            x: { stacked: true },
            y: { stacked: true },
        },
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { boxWidth: 10 }
            }
        }
    };

    var ctx = document.getElementById('pipeCodePresenceWard').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: options,
    });

    // Download as PNG
    document.getElementById('exportPipeCodePresenceWard').addEventListener("click", function() {
        var newCanvas = document.getElementById('pipeCodePresenceWard');
        var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
        var a = document.createElement('a');
        a.href = newCanvasImg;
        a.download = 'Ward-Wise Presence of Water Supply Pipelines.png';
        a.click();
    });
});
</script>
@endpush
