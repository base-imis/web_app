<!DOCTYPE html>
<html>

    <head>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td,
            th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #ddddde;
                border : 0.5px solid;
            }
            .text-right {
                text-align: right !important;
            }
        </style>
    </head>

    <body>
        <div style="width:100%; height:auto;display: -webkit-flex;;align-content: center;webkit-justify-content: center;align-items: center;text-align: center">
        <img src="data:image/img+xml;base64,'.{{base64_encode(file_get_contents(public_path("img/logo-imis.png")))}}" style="height:100%;width:150;">
            <div style="text-align:center;">
                <h1 class="heading" style="text-transform:uppercase;">Municipality</h1>
                <h2 style="text-transform:uppercase;">Application Monthwise Report</h2>
                <h3 style="text-align:center;text-transform:uppercase; font-family:Monospace; border-style:solid; border-width:1px;">
                    Integrated Municipal Information System
                </h3>
            </div>
        </div>
        <div >
            <p style="text-align:left;font-family:Monospace;font-size: 18px">Month:{{$month}}  
                <span style="float:right;font-family:Monospace;font-size: 18px">Year:{{$year}}</span></p>
        </div>
        @if(!$monthWisecount)
        
            <div style="text-align: center;"> No Data for Month </div>
        
        @endif
        @foreach($monthWisecount as $operator)
      <table id="data-table" class="table table-bordered " width="100%" style="margin-top:50px">
      <thead><p style="text-align:left;font-family:Monospace;font-size: 20px;font-weight:bold ;background-color: #ddddde; padding: 2px"> Operator Name: {{$operator->aname}}</p></thead>
          <!-- <tr >
              <th colspan="2"> Operator Name:</th>
              <th colspan="2"> {{$operator->aname}}</th>
        </tr> -->
        <tr>
            <th > Containments Emptied </th>
            <th style="text-align: right;"> {{$operator->emptycount}}</th>
            <th> Applications Received </th>
            <th style="text-align: right;"> {{$operator->applicationcount}}</th>
        </tr>
        <tr>
            <th> Safe Disposal</th>
            <th style="text-align: right;"> {{$operator->sCount}}</th>
            <th> Sludge Collections (m³) </th>
            <th style="text-align: right;"> {{$operator->sludgecount}}</th>

        </tr>
        <tr>
            <th> Total Cost (NRS) </th>
            <th style="text-align: right;"> {{$operator->totalcost}}</th>

        </tr>
        </table>
        @endforeach
        <!-- end for each -->
        <table id="data-table" class="table table-bordered " width="100%" style="margin-top:100px">
          <tr>
              <th colspan="4"  style="">Cumulative Data for {{$year}} upto month {{$month}}</th>
        </tr>
        <tr>
            @foreach($yearCount as $data)
            <th> Containments Emptied </th>
            <th style="text-align: right;"> {{$data->emptycount}}</th>
            <th> Applications Received </th>
            <th style="text-align: right;"> {{$data->applicationcount}}</th>
        </tr>
        <tr>
            
             <th> Safe Disposal</th>
            <th style="text-align: right;"> {{$data->sCount}}</th>
            <th> Sludge Collections (m³) </th>
            <th style="text-align: right;"> {{$data->sludgecount}}</th>
        </tr>
        <tr>
            <th> Total Cost (NRS) </th>
            <th style="text-align: right;"> {{$data->totalcost}}</th>
            @endforeach
        </tr>
        </table>
        <table id="data-table" class="table table-bordered " width="100%" style="margin-top:100px">
          <tr>
              <th colspan ="5" style="">Ward Wise Cumulative Data upto month {{$month}}</th>
        </tr>
 
        <tr>
            <th> Ward No:</th>
            <th> Containments Emptied </th>
            <th> Applications Received </th>
            <th>Safe Disposal</th>
            <th> Sludge Collections (m³) </th>
            <th> Total Cost (NRS) </th>
        </tr>
        @foreach($wardData as $data)
        <tr>
        <td style="text-align: right;">{{ $data-> award }}</td>
        <td style="text-align: right;"> {{$data->emptycount}}</td>
        <td style="text-align: right;"> {{$data->applicationcount}}</td>
        <td style="text-align: right;"> {{$data->sount}}</td>
        <td style="text-align: right;"> {{$data->sludgecount}}</td>
        <td style="text-align: right;"> {{$data->totalcost}}</td>

        </tr>
        @endforeach

        </table>

        {{-- <p style="float:right;">System Developed By:<b>Innovative Solution Pvt. Ltd</b> </p> --}}
    </body>


</html>
