@include('layouts.dashboard.chart-card',[
    'card_title' => "Emptying Requests By Structure Types",
    'export_chart_btn_id' => "exportemptyingRequestsbyStructureTypesChart",
    'canvas_id' => "emptyingRequestsbyStructureTypesChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("emptyingRequestsbyStructureTypesChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $emptyingRequestsbyStructureTypesChart['labels']); ?>],
    datasets: [
        {
            label: "No. of Emptying Requests",
            backgroundColor: "rgba(54, 162, 235,0.5)",
            hoverBackgroundColor: "rgba(54, 162, 235,0.7)",
            data: [<?php echo implode(',', $emptyingRequestsbyStructureTypesChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    responsive: true,
      legend: {
         labels: {
              boxWidth: 10
          }
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

                 },
             }
         }],
     },
  }
});
document.getElementById('exportemptyingRequestsbyStructureTypesChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#emptyingRequestsbyStructureTypesChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Emptying Requests By Structure Types.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
