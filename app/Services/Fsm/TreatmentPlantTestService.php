<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;

use App\Http\Requests\Request;
use App\Models\Fsm\TreatmentPlantTest;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class TreatmentPlantTestService
{

    //    protected $session;
    //    protected $instance;

    /**
     * Constructs a new LandfillSite object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/
    }

    /**
     * Get all the All Employee Info.
     *
     *
     * @return EmployeeInfo[]|Collection
     */
    public function getAllTreatmentPlants($data)
    {

        $treatmentPlant =  TreatmentPlantTest::select('*')->whereNull('deleted_at');
        return Datatables::of($treatmentPlant)
            ->filter(function ($query) use ($data) {
                if ($data['treatment_plant_name']) {
                    $query->whereHas('treatmentplants', function ($subQuery) use ($data) {
                        $subQuery->where('name', $data['treatment_plant_name']);
                    });
                }

                if ($data['temperature']) {
                    $query->where('temperature',   $data['temperature']);
                }

                if ($data['sample_location']) {
                    $query->where('sample_location',  $data['sample_location']);
                }
                if ($data['date']) {
                    $query->where('date', 'ILIKE', '%' . $data['date'] . '%');
                }
                if ($data['tss']) {
                    $query->where('tss',  $data['tss']);
                }
                if ($data['ph']) {
                    $query->where('ph', $data['ph']);
                }
                if ($data['cod']) {
                    $query->where('cod',  $data['cod']);
                }
                if ($data['bod']) {
                    $query->where('bod', $data['bod']);
                }
                if ($data['ecoli']) {
                    $query->where('ecoli',  $data['ecoli']);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['treatment-plant-test.destroy', $model->id]]);

                if (Auth::user()->can('Edit Treatment Plant Test')) {
                    $content .= '<a title="Edit" href="' . action("Fsm\TreatmentPlantTestController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('View Treatment Plant Test')) {
                    $content .= '<a title="Detail" href="' . action("Fsm\TreatmentPlantTestController@show", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }
                if (Auth::user()->can('View Treatment Plant Test History')) {
                    $content .= '<a title="History" href="' . action("Fsm\TreatmentPlantTestController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }
                if (Auth::user()->can('Delete Treatment Plant Test')) {
                    $content .= '<a href title="Delete" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }
                $content .= \Form::close();
                return $content;
            })
            ->editColumn('treatment_plant_id', function ($model) {
                return $model->treatmentplants->name ?? '-';
            })
            ->make(true);
    }


    /**
     * Store or update a newly created resource in storage.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function storeTpt($data)
    {
        $treatmentPlant = new TreatmentPlantTest();
        $treatmentPlant->treatment_plant_id = $data['treatment_plant_id'] ? $data['treatment_plant_id'] : null;
        $treatmentPlant->date = $data['date'] ? $data['date'] : null;
        $treatmentPlant->temperature = $data['temperature'] ? $data['temperature'] : null;
        $treatmentPlant->ph = $data['ph'] ? $data['ph'] : null;
        $treatmentPlant->cod = $data['cod'] ? $data['cod'] : null;
        $treatmentPlant->bod = $data['bod'] ? $data['bod'] : null;
        $treatmentPlant->ecoli = $data['ecoli'] ? $data['ecoli'] : null;
        $treatmentPlant->tss = $data['tss'] ? $data['tss'] : null;
        $treatmentPlant->remarks = $data['remarks'] ? $data['remarks'] : null;

        $treatmentPlant->sample_location = $data['sample_location'] ? $data['sample_location'] : null;
        $treatmentPlant->user_id = Auth::user()->id;
        $treatmentPlant->save();
        return redirect('fsm/treatment-plant-test')->with('success', 'Performance Efficiency Test created successfully');
    }

    public function updateTpt($request, $id)
    {
        $treatmentPlant = TreatmentPlantTest::find($id);
        if ($treatmentPlant) {
            $treatmentPlant->treatment_plant_id = $request->treatment_plant_id ?? null;
            $treatmentPlant->date = $request->date ?? null;
            $treatmentPlant->temperature = $request->temperature ?? null;
            $treatmentPlant->ph = $request->ph ?? null;
            $treatmentPlant->cod = $request->cod ?? null;
            $treatmentPlant->bod = $request->bod ?? null;
            $treatmentPlant->ecoli = $request->ecoli ?? null;
            $treatmentPlant->tss = $request->tss ?? null;
            $treatmentPlant->sample_location = $request->sample_location ?? null;
            $treatmentPlant->remarks = $request->remarks ?? null;

            $treatmentPlant->save();

            return redirect('fsm/treatment-plant-test')->with('success', 'Performance Efficiency Test updated successfully');
        } else {
            return redirect('fsm/treatment-plant-test')->with('Error', 'Failed to Updated Performance Efficiency Test');
        }
    }

    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */

    public function exportData($data)
    {
        $searchData = $data['searchData'] ?? null;
        $treatment_plant_name = $data['treatment_plant_name'] ?? null;
        $date = $data['date'] ?? null;
        $temperature = $data['temperature'] ?? null;
        $ph = $data['ph'] ?? null;
        $cod = $data['cod'] ?? null;
        $bod = $data['bod'] ?? null;
        $tss = $data['tss'] ?? null;
        $ecoli = $data['ecoli'] ?? null;
        $sample_location = $data['sample_location'] ?? null;
        $remarks = $data['remarks'] ?? null;
        $columns = ['Treatment Plant', 'Sample Date', 'Temperature °C', 'pH', 'COD (mg/I)', 'BOD (mg/l)', 'TSS (mg/l)', 'Ecoli', 'Sample Location','Remark'];

        $query =  TreatmentPlantTest::select('treatment_plant_id', 'date', 'temperature', 'ph', 'cod','bod','tss','ecoli','sample_location','remarks')
        ->whereNull('deleted_at');
        if (!empty($treatment_plant_name)) {
            $query->whereHas('treatmentplants', function ($subQuery) use ($treatment_plant_name) {
                $subQuery->where('name', $treatment_plant_name);
            });
        }
        if (!empty($date)) {
            $query->where('date', $date);
        }
        if (!empty($temperature)) {
            $query->where('temperature', $temperature);
        }
        if (!empty($ph)) {
            $query->where('ph', $ph);
        }
        if (!empty($cod)) {
            $query->where('cod', $cod);
        }
        if (!empty($bod)) {
            $query->where('bod', $bod);
        }
        if (!empty($tss)) {
            $query->where('tss', $tss);
        }
        if (!empty($ecoli)) {
            $query->where('ecoli', $ecoli);
        }
        if (!empty($sample_location)) {
            $query->where('sample_location', $sample_location);
        }
        if (!empty($remarks)) {
            $query->where('remarks', $remarks);
        }
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Performance Efficiency Test.CSV')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($treatmentPlantTest) use ($writer) {
            foreach ($treatmentPlantTest as $test) {

                $values = [];
                $values[] = $test->treatmentplants->name;
                $values[] = $test->date;
                $values[] = $test->temperature;
                $values[] = $test->ph;
                $values[] = $test->cod;
                $values[] = $test->bod;
                $values[] = $test->tss;
                $values[] = $test->ecoli;
                $values[] = $test->sample_location;
                $values[] = $test->remarks;

                $writer->addRow($values);

            }
        });

        $writer->close();
    }

}
