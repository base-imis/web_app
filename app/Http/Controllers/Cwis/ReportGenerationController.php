<?php

namespace App\Http\Controllers\Cwis;

use App\Http\Controllers\Controller;
use App\Models\Cwis\cwis_mne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Application;
use App\Models\User;
//use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Database\PostgresConnection;
use Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Input;
use PDF;
use PhpOption\None;


class ReportGenerationController extends Controller
{
     public function monthlyApplicationsPdf($year, $month){
        if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Municipality - IT Admin')|| Auth::user()->hasRole('Municipality - Executive')) {
        $monthWisequery = 'with application as(
            select SERVPROV.NAME as aname, count(applications.id) as applicationCount
            from servprov
            LEFT JOIN APPLICATIONS ON SERVPROV.gid= APPLICATIONS.service_provider_id where EXTRACT(YEAR FROM application_date) = '. $year . '
            and EXTRACT(Month from application_date)  = '. $month .'
            GROUP BY SERVPROV.NAME
        ),
        emptying as(
            select SERVPROV.NAME as aname, count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost  , sum(volume_of_sludge) as sludgeCount
            from servprov
            Left JOIN emptyings ON SERVPROV.gid= emptyings.service_provider_id  where EXTRACT(YEAR FROM emptied_date) ='. $year . '
            and EXTRACT(Month from emptied_date)  = '. $month .'
            GROUP BY SERVPROV.NAME
        )
        select application.aname, applicationCount, emptyCount, sludgeCount, totalCost  from application full join emptying ON application.aname = emptying.aname; ';

        $monthWisecount= DB::Select($monthWisequery);

        $yearCountquery = 'with application as(
            select  count(applications.id) as applicationCount
            from applications
            where EXTRACT(YEAR FROM application_date) = '. $year . ' and EXTRACT(Month from application_date)  <= '. $month .'

        ),
        emptying as(
            select  count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost, sum(volume_of_sludge) as sludgeCount
            from emptyings
             where EXTRACT(YEAR FROM emptied_date) = '. $year . ' and  EXTRACT(Month from emptied_date)  <= '. $month .'
        )
        select applicationCount, emptyCount, sludgeCount, totalCost  from application, emptying; ';

        $yearCount= DB::Select($yearCountquery);

        $wardMonthlyquery = ' with application as(
            select count(applications.id) as applicationCount ,APPLICATIONS.ward as award
                   from APPLICATIONS
                   where EXTRACT(YEAR FROM application_date) = '. $year . ' and EXTRACT(MONTH FROM application_date) <= '. $month . '
                   GROUP BY APPLICATIONS.ward
         ),
          emptying as(
            select count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost,count(volume_of_sludge) as sludgeCount, ward as eward
                   from emptyings
                   Left JOIN applications ON applications.id= emptyings.application_id  WHERE EXTRACT(YEAR FROM emptied_date) = '. $year . '   and EXTRACT(MONTH FROM emptied_date) <= '. $month . '
                   GROUP BY APPLICATIONS.ward
               )

               select  applicationCount, emptyCount, sludgeCount, totalCost, award  from application, emptying where award = eward ORDER BY award ; ' ;


        $wardData= DB::Select($wardMonthlyquery);



        return PDF::loadView('pdf.monthly_report', compact('year', 'month','monthWisecount','yearCount','wardData'))->download('Monthly Report.pdf');
          }
          else{
            $service_provider_id = User::where('id', '=','4')->pluck('service_provider_id')->first();
            $monthWisequery = 'with application as(
                select SERVPROV.NAME as aname, count(applications.id) as applicationCount
                from servprov
                LEFT JOIN APPLICATIONS ON SERVPROV.gid= APPLICATIONS.service_provider_id where EXTRACT(YEAR FROM application_date) = '. $year . '
                 and EXTRACT(Month from application_date)  = '. $month .'
                 and APPLICATIONS.service_provider_id='. $service_provider_id. '
                GROUP BY SERVPROV.NAME
            ),
            emptying as(
                select SERVPROV.NAME as aname, count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost  , sum(volume_of_sludge) as sludgeCount
                from servprov
                Left JOIN emptyings ON SERVPROV.gid= emptyings.service_provider_id  where EXTRACT(YEAR FROM emptied_date) ='. $year . '
                and EXTRACT(Month from emptied_date)  = '. $month .'
                and emptyings.service_provider_id='. $service_provider_id. '
                GROUP BY SERVPROV.NAME
            )
            select application.aname, applicationCount, emptyCount, sludgeCount, totalCost  from application full join emptying ON application.aname = emptying.aname; ';

            $monthWisecount= DB::Select($monthWisequery);

            $yearCountquery = 'with application as(
                select  count(applications.id) as applicationCount
                from applications
                where EXTRACT(YEAR FROM application_date) = '. $year . '
                and EXTRACT(Month from application_date)  <= '. $month .'
                and applications.service_provider_id='. $service_provider_id. '
            ),
            emptying as(
                select  count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost, sum(volume_of_sludge) as sludgeCount
                from emptyings
                 where EXTRACT(YEAR FROM emptied_date) = '. $year . '
                 and  EXTRACT(Month from emptied_date)  <= '. $month .'
                and emptyings.service_provider_id='. $service_provider_id. '

            )
            select applicationCount, emptyCount, sludgeCount, totalCost  from application, emptying; ';

            $yearCount= DB::Select($yearCountquery);

            $wardMonthlyquery = ' with application as(
                select count(applications.id) as applicationCount ,APPLICATIONS.ward as award
                    from APPLICATIONS
                    where EXTRACT(YEAR FROM application_date) = '. $year . '
                    and EXTRACT(MONTH FROM application_date) <= '. $month . '
                    and applications.service_provider_id='. $service_provider_id. '
                       GROUP BY APPLICATIONS.ward
                       
             ),
              emptying as(
                select count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost,count(volume_of_sludge) as sludgeCount, ward as eward
                    from emptyings
                    Left JOIN applications ON applications.id= emptyings.application_id  WHERE EXTRACT(YEAR FROM emptied_date) = '. $year . '
                    and EXTRACT(MONTH FROM emptied_date) <= '. $month . '
                    and emptyings.service_provider_id='. $service_provider_id. '
                    GROUP BY APPLICATIONS.ward
                   )

                   select  applicationCount, emptyCount, sludgeCount, totalCost, award  from application, emptying where award = eward ORDER BY award; ' ;


            $wardData= DB::Select($wardMonthlyquery);

            return PDF::loadView('pdf.monthly_report', compact('year', 'month','monthWisecount','yearCount','wardData'))->download('Monthly Report.pdf');
          }
    }

    public function mneReport(Request $request){
        $co_cf_labels = ["Safety", "Equity", "Sustainability"];
        $years = cwis_mne::query()
            ->select("year")
            ->orderBy("year")
            ->groupBy("year")
            ->pluck("year");
        $selected_year = $years->last();
        $cwis_mne = [];
        if (request()->selected_year) {
            $selected_year = request()->selected_year;
        }
        $param_list = cwis_mne::where('year', '=', $years->last())
            ->orderBy('parameter_id')
            ->groupBy('parameter_id')
            ->pluck('parameter_id');

        $param_listcount = count($param_list);
        for ($i = 0; $i < $param_listcount; $i++) {
            $param_titles[$i] = DB::Table('cwis.data_mne as d')
                ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                ->where('d.year', '=', $years->last())
                ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                ->orderBy('d.source_id')
                ->pluck('parameter_title');
            $param_details[$i] = DB::Table('cwis.data_mne as d')
                ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.co_cf', 'd.data_value','d.sym_no','d.heading','d.label')
                ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr,  d.data_type[array_length(d.data_type, 1)] as data_type_req')
                ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                ->where('d.year', '=', $years->last())
                ->where('d.parameter_id', '=', $param_list[$i])
                ->orderBy('d.source_id')
                ->get();

            foreach ($param_details[$i] as $key => $param) {
                $labels = [];
                $yearly_data[$key] = DB::Table('cwis.data_mne as d')
                    ->select("d.year","d.data_value")
                    ->orderBy("d.year")
                    ->groupBy("d.year","d.data_value")
                    ->where("d.sym_no","=",$param->sym_no)
                    ->pluck("d.data_value")
                    ->toArray();


                foreach ($co_cf_labels as $co_cf_label) {
                    if (Str::contains(strtolower(str_replace("&", "and", $param->co_cf)), strtolower($co_cf_label)))
                        array_push($labels, str_replace(" ", "_", $co_cf_label));
                }
                $param_details[$i][$key]->labels = implode(" ", $labels);
                $param_details[$i][$key]->labelsArr = $labels;
                $param_details[$i][$key]->years = $years->toArray();
                $param_details[$i][$key]->yearly_data = $yearly_data[$key];
            }
            $cwis_mne[trim($param_titles[$i][0])] = $param_details[$i];
        }
        $charts = $request->data;
        $pdf = PDF::loadView("cwis.pdf.cwis_mne_dashboard_report",compact("cwis_mne","charts","selected_year"));
        return $pdf->inline('CWIS M and E DASHBOARD - '.$selected_year.'.pdf');
    }

}
