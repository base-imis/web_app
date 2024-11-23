<div class="hi">
    <div class="sus sus1">
        <div class="card-header">
        Treated FS that is reused
        </div>
        <div class="chart" id="ss1cContain">
            <figure class="chart__figure">
                <canvas class="chart__canvas" id="ss1cCanva" width="160" height="140" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>
            </figure>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Use a ternary operator to check if $ss1[0] is set and not null
                const dataValue = {{ isset($ss1[0]) && $ss1[0] !== null ? ($ss1[0]->data_value) : 0 }};
                createDoughnutChart(dataValue, '#E49B0F', 'ss1cCanva', 'ss1cContain');
            });
        </script>
    </div>
</div>
