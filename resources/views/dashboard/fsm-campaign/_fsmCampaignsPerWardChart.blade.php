@include('layouts.dashboard.chart-card',[
    'card_title' => "FSM Campaigns by Ward",
    'export_chart_btn_id' => "exportfsmCampaignsPerWardChart",
    'canvas_id' => "fsmCampaignsPerWardChart"
])
@push('scripts')
    <script>
        var ctx = document.getElementById("fsmCampaignsPerWardChart");
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', $fsmCampaignsPerWardChart['labels']); ?>],
                datasets: [
                    {
                        label: "No. of FSM Campaigns",
                        backgroundColor: "rgba(90, 155, 212,0.2)",
                        borderColor: "rgba(90, 155, 212,1)",
                        borderWidth: 1,
                        hoverBackgroundColor: "rgba(90, 155, 212,0.4)",
                        hoverBorderColor: "rgba(90, 155, 212,1)",
                        data: [<?php echo implode(',', $fsmCampaignsPerWardChart['values']); ?>],
                    }
                ]
            },
            options: {
                animation:{
                    animateScale:true
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            userCallback: function(label, index, labels) {
                     // when the floored value is the same as the value we have a whole number
                     if (Math.floor(label) === label) {
                         return label;
                     }

                 }
                        }
                    }]
                }
            }
        });
        document.getElementById('exportfsmCampaignsPerWardChart').addEventListener("click", downloadIMG);
        //donwload pdf from original canvas
        function downloadIMG() {
            var newCanvas = document.querySelector('#fsmCampaignsPerWardChart');

            //create image from dummy canvas
            var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
            var a = document.createElement('a');
            a.href =newCanvas.toDataURL("image/png", 1.0);

            a.download = 'FSM Campaigns by Ward.png';

            // Trigger the download
            a.click();
        }
    </script>
@endpush
