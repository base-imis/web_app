<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@include('layouts.dashboard.chart-card',[
    'card_title' => "Applications, Emptying Services, Sludge Disposed, Feedbacks Details",
    'export_chart_btn_id' => "exportemptyingServicePerWardsAssessmentFeedbackChart",
    'canvas_id' => "emptyingServicePerWardsAssessmentFeedbackChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("emptyingServicePerWardsAssessmentFeedbackChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $emptyingServicePerWardsAssessmentFeedbackChart['labels']); ?>],
    datasets: [
        @foreach($emptyingServicePerWardsAssessmentFeedbackChart['datasets'] as $dataset)
        {
            stack: <?php echo $dataset['stack']; ?>,
            label: <?php echo $dataset['label']; ?>,
            backgroundColor: <?php echo $dataset['color']; ?>,
            data: [<?php echo implode(',', $dataset['data']); ?>],
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
         labels: {
              boxWidth: 10
          }
      },
    scales: {
     xAxes: [{
                 scaleLabel: {
        display: true,
        labelString: 'Wards',
        //fontSize: 10,
      },
            
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
    }
   
  }
});
document.getElementById('exportemptyingServicePerWardsAssessmentFeedbackChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#emptyingServicePerWardsAssessmentFeedbackChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Applications, Emptying services, Feedback details by Wards.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
