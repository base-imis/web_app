<?php

namespace App\Http\Controllers\Cwis;

use App\Http\Controllers\Controller;
use App\Models\Cwis\Athena;
use App\Models\Cwis\cwis_datastore;
use App\Models\Cwis\DataJmp;
use App\Models\Cwis\cwis_mne;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CwisDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexJmp()
    {
        $page_title = 'Dashboard';
        $years = cwis_mne::query()
            ->select("year")
            ->orderBy("year")
            ->groupBy("year")
            ->pluck("year");
        $selected_year = $years->last();

        $subCategory_titles = DB::Table('cwis_data_jmp as d')
            ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
            ->where('d.year', '=', 2022)
            ->distinct()->pluck('ds.sub_category_title');

        $param_list = DataJmp::where('year', '=', 2022)
            ->orderBy('parameter_id')
            ->groupBy('parameter_id')
            ->pluck('parameter_id');
        $param_listcount = count($param_list);

        for ($i = 0; $i < $param_listcount; $i++) {
            $param_titles[$i] = DB::Table('cwis_data_jmp as d')
                ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                ->where('d.year', '=', 2022)
                ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                ->orderBy('d.source_id')
                ->pluck('parameter_title');
            $param_details[$i] = DB::Table('cwis_data_jmp as d')
                ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.data_value')
                ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr, d.data_type[array_length(d.data_type, 1)] as data_type_req')
                ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                ->where('d.year', '=', 2022)
                ->where('d.parameter_id', '=', $param_list[$i])
                ->orderBy('d.source_id')
                ->get();
            $cwis_jmp[trim($param_titles[$i][0])] = $param_details[$i];
        }


        return (view("cwis/cwis-dashboard/cwis-jmp-dashboard-index", compact("page_title", "years", "selected_year")));
    }

    public function indexMe()
    {



        $co_cf_labels = ["Equity", "Safety", "Sustainability"];
        $years = cwis_mne::query()
            ->select("year")
            ->orderBy("year")
            ->groupBy("year")
            ->pluck("year");
        $selected_year = $years->last();
        $primary_colors = ["#008FFB", "#008FFB", "#008FFB", "#008FFB", "#008FFB,#008FFB", "#008FFB", "#008FFB", "#008FFB", "#008FFB"];
        $secondary_colors = ["#008FFB", "#008FFB", "#008FFB", "#008FFB", "#008FFB,#008FFB", "#008FFB", "#008FFB", "#008FFB", "#008FFB"];
        if (request()->year) {
            $selected_year = request()->year;
            $param_list = cwis_mne::where('year', '=', request()->year)
                ->orderBy('parameter_id')
                ->groupBy('parameter_id')
                ->pluck('parameter_id');

            $param_listcount = count($param_list);

            for ($i = 0; $i < $param_listcount; $i++) {
                $param_titles[$i] = DB::Table('cwis.data_cwis as d')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', request()->year)
                    ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                    ->orderBy('d.source_id')
                    ->pluck('parameter_title');
                $param_details[$i] = DB::Table('cwis.data_cwis as d')
                    ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.co_cf', 'd.data_value', 'd.sym_no', 'd.heading', 'd.label', 'd.indicator_code')
                    ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr,  d.data_type[array_length(d.data_type, 1)] as data_type_req')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', request()->year)
                    ->where('d.parameter_id', '=', $param_list[$i])
                    ->orderBy('d.source_id')
                    ->get();

                foreach ($param_details[$i] as $key => $param) {
                    $labels = [];
                    $yearly_data[$key] = DB::Table('cwis.data_cwis as d')
                        ->select("d.year", "d.data_value")
                        ->orderBy("d.year")
                        ->groupBy("d.year", "d.data_value")
                        ->where("d.sym_no", "=", $param->sym_no)
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
        } else {
            $param_list = cwis_mne::where('year', '=', $years->last())
                ->orderBy('parameter_id')
                ->groupBy('parameter_id')
                ->pluck('parameter_id');

            $param_listcount = count($param_list);
            for ($i = 0; $i < $param_listcount; $i++) {
                $param_titles[$i] = DB::Table('cwis.data_cwis as d')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', $years->last())
                    ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                    ->orderBy('d.source_id')
                    ->pluck('parameter_title');


                $param_details[$i] = DB::Table('cwis.data_cwis as d')
                    ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.co_cf', 'd.data_value', 'd.sym_no', 'd.heading', 'd.label', 'd.indicator_code')
                    ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr,  d.data_type[array_length(d.data_type, 1)] as data_type_req')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', $years->last())
                    ->where('d.parameter_id', '=', $param_list[$i])
                    ->orderBy('d.source_id')
                    ->get();

                foreach ($param_details[$i] as $key => $param) {
                    $labels = [];
                    $yearly_data[$key] = DB::Table('cwis.data_cwis as d')
                        ->select("d.year", "d.data_value")
                        ->orderBy("d.year")
                        ->groupBy("d.year", "d.data_value")
                        ->where("d.sym_no", "=", $param->sym_no)
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
        }

        $charts = [];
        $cwis_mne['EQ - 1. % of LIC population with access to safe individual toilets / % of total population with access'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['EQ - 1. % of LIC population with access to safe individual toilets / % of total population with access'][0]
                ],
            ],
        ];
        $cwis_mne['EQ - 2. % safe management LIC/% safe management citywide (IHHL)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['EQ - 2. % safe management LIC/% safe management citywide (IHHL)'][0]
                ],
            ],

        ];
        $cwis_mne['EQ - 3. Subsidy amount paid to NSS/SS'] = [
            [
                "group_type" => 'ratio',
                "data" => [
                    $cwis_mne['EQ - 3. Subsidy amount paid to NSS/SS'][0]
                ],
            ],

        ];
        $cwis_mne['EQ - 4. % of women in sanitation related decision-making bodies (service authorities)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['EQ - 4. % of women in sanitation related decision-making bodies (service authorities)'][0], $cwis_mne['EQ - 4. % of women in sanitation related decision-making bodies (service authorities)'][1]
                ],
            ],
        ];
        $cwis_mne['EQ - 5. Gender pay gap in the sanitation workforce'] = [
            [
                "group_type" => 'ratio',
                "data" => [
                    $cwis_mne['EQ - 5. Gender pay gap in the sanitation workforce'][0]
                ],
            ],

        ];
        // $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'] = [
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][0]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][1]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][2]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][3]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][4]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][5]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][6]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][7]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][8]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['EQ - 6. [Indicator Area] Sanitation worker equity'][9]
        //         ],
        //     ],

        // ];
        $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][0], $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][1], $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][2], $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][3]

                ],
            ],
            [
                "group_type" => 'percent',
                "data" => [

                    $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][4], $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][5],
                    $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][6], $cwis_mne['SF - 1. % safely managed sanitation (citywide IHHL)'][7]


                ],
            ]

        ];
        $cwis_mne['SF - 2. % safely managed sanitation for LIC IHHL'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 2. % safely managed sanitation for LIC IHHL'][0], $cwis_mne['SF - 2. % safely managed sanitation for LIC IHHL'][1], $cwis_mne['SF - 2. % safely managed sanitation for LIC IHHL'][2], $cwis_mne['SF - 2. % safely managed sanitation for LIC IHHL'][3]
                ],
            ],

        ];
        $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][0],
                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][1],
                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][2],
                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][3]
                ],
            ],
            [
                "group_type" => 'ratio',
                "data" => [

                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][4]
                ],
            ],
            [
                "group_type" => 'ratio',
                "data" => [

                    $cwis_mne['SF - 3. % safely managed liquid waste for shared facilities (CT & shared household toilets)'][5]
                ],
            ],
        ];
        $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'][0], $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'][1], $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'][2],
                    $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'][4]

                ],
            ],
            [
                "group_type" => 'ratio',
                "data" => [

                    $cwis_mne['SF - 4. % of public spaces that have adequate sanitation facilities (Public Toilets/PT)'][3]
                ],
            ],


        ];
        $cwis_mne['SF - 5. % of educational institutions where FS/WW generated is safely transported to TP or safely disposed in situ'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 5. % of educational institutions where FS/WW generated is safely transported to TP or safely disposed in situ'][0]
                ],
            ],


        ];
        $cwis_mne['SF - 6. % of healthcare facilities where FS/WW generated is safely transported to TP or safely disposed in situ'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 6. % of healthcare facilities where FS/WW generated is safely transported to TP or safely disposed in situ'][0]
                ],
            ],


        ];
        $cwis_mne['SF - 7. % of desludging services completed mechanically or semi-mechanically (gulper)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 7. % of desludging services completed mechanically or semi-mechanically (gulper)'][0]
                ],
            ],


        ];
        $cwis_mne['SF - 8. % of desludging vehicles which comply with maintenance standards'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 8. % of desludging vehicles which comply with maintenance standards'][0]
                ],
            ],


        ];
        $cwis_mne['SF - 9. % of water contamination compliance (on fecal coliform)'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SF - 9. % of water contamination compliance (on fecal coliform)'][0]
                ],
            ],


        ];
        $cwis_mne['SF - 10. Incidence (per 1000) of fecal-oral pathway diseases'] = [
            [
                "group_type" => 'ratio',
                "data" => [
                    $cwis_mne['SF - 10. Incidence (per 1000) of fecal-oral pathway diseases'][0]
                ],
            ],


        ];
        // $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'] = [
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][0]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][1]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][2]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][3]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][4]
        //         ],
        //     ],
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 11. [Indicator Area] Sanitation worker safety'][5]
        //         ],
        //     ],

        // ];
        // $cwis_mne['SF - 12. Is there a certification mechanism for which treated wastewater and biosolids have to qualify?'] = [
        //     [
        //         "group_type" => 'text',
        //         "data" => [
        //             $cwis_mne['SF - 12. Is there a certification mechanism for which treated wastewater and biosolids have to qualify?'][0]
        //         ],
        //     ],


        // ];
        $cwis_mne['SS - 1. % of treated FS and WW that is reused'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SS - 1. % of treated FS and WW that is reused'][0]
                ],
            ],


        ];
        $cwis_mne['SS - 2. % of O&M cost recovered for sanitation infrastructure'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SS - 2. % of O&M cost recovered for sanitation infrastructure'][0]
                ],
            ],


        ];
        $cwis_mne['SS - 3. % of sanitation capital investments covered by budget line/government transfers'] = [
            [
                "group_type" => 'percent',
                "data" => [
                    $cwis_mne['SS - 3. % of sanitation capital investments covered by budget line/government transfers'][0]
                ],
            ],


        ];

        foreach ($cwis_mne as $section_name => $section) {
            foreach ($section as $key => $group) {
                $temp = [];

                switch ($group["group_type"]) {

                    case "percent":
                        foreach ($group["data"] as $dataKey => $datum) {
                            $radialChart = (new LarapexChart)->radialChart()
                                ->setChartKey(str_replace(" ", "-", $section_name) . '-' . $key . '-' . $dataKey)
                                ->setRadialBar(
                                    false,
                                    5,
                                    '15px',
                                    $primary_colors[$dataKey],
                                    $secondary_colors[$dataKey],
                                    "60%"
                                )
                                ->setHeight("120")
                                ->setWidth("150")
                                ->setDataLabels(true)
                                ->setLabels([$datum->data_value . '%'])
                                ->addData([$datum->data_value])
                                ->setColors([$primary_colors[$dataKey]]);
                            array_push($temp, $radialChart);
                            array_push($charts, $radialChart);
                        }
                        break;

                    case "bar":
                        // f(x) = c / (1 + a*exp(-x*b)) -> LOGISTIC GROWTH MODEL
                        $optimalColumnWidthPercent = 20 + (60 / (1 + 30 * exp(-5 / 3)));
                        $barChart = (new LarapexChart())->BarChart()
                            ->setChartKey(str_replace(" ", "-", $section_name) . '-' . $key . '-' . $dataKey)
                            ->setHeight("250")
                            ->setHorizontal(true, 4, true, $optimalColumnWidthPercent . "%")
                            ->setXAxis([$group["data"][0]->label, $group["data"][1]->label])
                            ->setTitle($group["data"][0]->heading)
                            ->setDataLabels(true)
                            ->setDataset([[
                                'name' => "Percent",
                                'data' => ($group["data"][0]->data_value == "NA" && $group["data"][1]->data_value == "NA") ? [] : [is_numeric($group["data"][0]->data_value) ? $group["data"][0]->data_value : null, is_numeric($group["data"][1]->data_value) ? $group["data"][1]->data_value : null]
                            ]]);
                        array_push($temp, $barChart);
                        array_push($charts, $barChart);
                        break;
                }
                $group["charts"] = $temp;
                $cwis_mne[$section_name][$key] = $group;
            }
        }

        return view('cwis/cwis-dashboard/cwis-mne-dashboard-index', ["charts" => $charts, "co_cf_labels" => $co_cf_labels, "years" => $years, "selected_year" => $selected_year, "page_title" => "", "cwis_mne" => $cwis_mne, "primary_colors" => $primary_colors, "secondary_colors" => $secondary_colors]);
    }


    public function getAll($year){
        if  ($year!== null){

            $result = cwis_mne::where('year', $year)
            ->select('id','data_value', 'heading', 'assmntmtrc_dtpnt', 'unit')
            ->get();

        }else{
        $result = cwis_mne::where('year', '2021')
        ->select('id','data_value', 'heading', 'assmntmtrc_dtpnt', 'unit')
        ->get();
        }
    // $result is now a collection of objects, each representing a row in the result set.

    // Transform the collection into an array with the specified keys
    $resultArray = $result->map(function ($item) {
        return [
            'id'=>$item->id,
            'data_value' => $item->data_value,
            'heading' => $item->heading,
            'assmntmtrc_dtpnt' => $item->assmntmtrc_dtpnt,
            'unit' => $item->unit,
        ];
    })->toArray();

    dd($resultArray);
    }
}
