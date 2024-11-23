@include('layouts.dashboard.chart-card',[
    'card_title' => "Containment Types By Structure Type",
    'export_chart_btn_id' => "exportcontainmentTypesByStructypesChart",
    {{-- 'year_id' => "year", --}}
    'canvas_id' => "containmentTypesByStructypesChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("containmentTypesByStructypesChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $containmentTypesByStructypesChart['labels']); ?>],
    datasets: [
        @foreach($containmentTypesByStructypesChart['datasets'] as $dataset)
        {
            label: <?php echo $dataset['label']; ?>,
            backgroundColor: <?php echo $dataset['color']; ?>,
            data: [<?php echo implode(',', $dataset['data']); ?>],
            values:[<?php echo implode(',', $dataset['value']); ?>]
        },
        @endforeach
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    responsive: true,
      legend: {
         display: true,
         position: 'bottom',
         align: 'start',
         labels: {
              boxWidth: 10
          }
      },
    scales: {
        xAxes: [{
            stacked: true,
            ticks: {
                beginAtZero: true
            }
        }],
        yAxes: [{
            stacked: true,
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
    },
    tooltips: {
        mode: 'index',
        callbacks: {
            label: function (tooltipItem, data) {
                var allData = data.datasets[tooltipItem.datasetIndex].data;
                var allValues = data.datasets[tooltipItem.datasetIndex].values;
                var tooltipLabel = data.datasets[tooltipItem.datasetIndex].label;
                var tooltipData = allData[tooltipItem.index];
                var tooltipValue = allValues[tooltipItem.index];
                return tooltipLabel + ": " + tooltipData.toFixed(2) + "% : "+tooltipValue;
            },
        }
    }
  }
});
document.getElementById('exportcontainmentTypesByStructypesChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#containmentTypesByStructypesChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Containment Types by Structure Type.png';

    // Trigger the download
    a.click();
      }
      // document.getElementById("year").addEventListener('change', function(){
      //   var selectedYear = this.value;

      // })

</script>


<script type="text/javascript">
 var data = [{
  data: [50, 55, 60, 33],
  backgroundColor: [
    "#4b77a9",
    "#5f255f",
    "#d21243",
    "#B27200"
  ],
  borderColor: "#fff"
}];

var options = {
  tooltips: {
    enabled: true
  },
  plugins: {
    datalabels: {
      formatter: (value, ctx) => {

        let sum = ctx.dataset._meta[0].total;
        let percentage = (value * 100 / sum).toFixed(2) + "%";
        return percentage;


      },
      color: '#fff',
    }
  }
};


  </script>
@endpush
