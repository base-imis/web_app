<?php

namespace App\Services\Fsm;

use App\Classes\FormField;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Swm\Route;
use App\Models\UtilityInfo\Roadline;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Yajra\DataTables\Facades\DataTables;

class SludgeCollectionService
{

    protected $session;
    protected $instance;
    protected string $indexAction;
    protected $createRoute;
    protected $createPartialForm, $createFormFields, $createFormAction;
    protected $showFormFields, $editFormFields, $filterFormFields;

    /**
     * Constructs a new SludgeCollection object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/

        $this->createPartialForm = 'fsm.feedback.partial-form';
        $this->createFormFields = [
            ["title" => "Customer Details",
                "fields" => [
                    new FormField(
                        label: 'Address',
                        labelFor: 'address',
                        inputType: 'multiple-select',
                        inputId: 'address',
                        selectValues: Building::all()->pluck('address','address')->sort()->toArray(),

                    ),
                    new FormField(
                        label: 'Customer Name',
                        labelFor: 'customer_name',
                        inputType: 'text',
                        inputId: 'customer_name',
                    ),
                    new FormField(
                        label: 'Customer Gender',
                        labelFor: 'customer_gender',
                        inputType: 'select',
                        inputId: 'customer_gender',
                        selectValues: ["M"=>"Male","F"=>"Female","O"=>"Others"],
                    ),
                    new FormField(
                        label: 'Contact',
                        labelFor: 'contact_no',
                        inputType: 'number',
                        inputId: 'contact_no',
                        selectValues: [],
                    ),
                ]],
            ["title" => "Applicant Details",
                "copyDetails"=>true,
                "fields" => [
                    new FormField(
                        label: "Applicant's Name",
                        labelFor: 'applicants_name',
                        inputType: 'text',
                        inputId: 'applicants_name',
                        selectValues: [],
                    ),
                    new FormField(
                        label: "Applicant's Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'select',
                        inputId: 'applicant_gender',
                        selectValues: ["M"=>"Male","F"=>"Female","O"=>"Others"],
                    ),
                    new FormField(
                        label: "Applicant's Contact",
                        labelFor: 'applicants_contact',
                        inputType: 'number',
                        inputId: 'applicants_contact',
                        selectValues: [],
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-address",
                "hidden" => true,
                "fields" => [
                    new FormField(
                        label: 'Road',
                        labelFor: 'road_code',
                        inputType: 'select',
                        inputId: 'road_code',
                        selectValues: Roadline::all()->pluck('name','code')->toArray(),
                    ),
                    new FormField(
                        label: 'Containment',
                        labelFor: 'containment_code',
                        inputType: 'text',
                        inputId: 'containment_code',
                        selectValues: Containment::all()->pluck('code','code')->toArray(),
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward',
                        inputType: 'select',
                        inputId: 'ward',
                        selectValues: [1=>1,2=>2,3=>3,4=>4,5=>5],
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date',
                        inputType: 'text',
                        inputId: 'proposed_feedback_date',
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-not-address",
                "hidden" => true,
                "fields" => [
                    new FormField(
                        label: 'Nearest Road',
                        labelFor: 'road_code_no_addr',
                        inputType: 'select',
                        inputId: 'road_code_no_addr',
                        selectValues: Roadline::all()->pluck('name','code')->toArray(),
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward_no_addr',
                        inputType: 'select',
                        inputId: 'ward_no_addr',
                        selectValues: [1=>1,2=>2,3=>3,4=>4,5=>5],
                    ),
                    new FormField(
                        label: 'Landmark',
                        labelFor: 'landmark',
                        inputType: 'text',
                        inputId: 'landmark',
                        selectValues: [],
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date_no_addr',
                        inputType: 'date',
                        inputId: 'proposed_feedback_date_no_addr',
                        selectValues: [],
                    ),
                ]],
        ];
        $this->createFormAction = route('feedback.store');
        $this->indexAction = route('feedback.index');
        $this->createRoute = route('feedback.create');

        $this->filterFormFields = [
            [
                new FormField(
                    label: 'Address',
                    labelFor: 'address',
                    labelClass: 'col-sm-1',
                    inputType: 'text',
                    inputId: 'address',
                    selectValues: Building::all()->pluck('address','address')->sort()->toArray(),
                ),
                new FormField(
                    label: 'Customer Name',
                    labelFor: 'customer_name',
                    labelClass: 'col-sm-1',
                    inputType: 'text',
                    inputId: 'customer_name',
                ),
                new FormField(
                    label: 'Ward',
                    labelFor: 'ward',
                    labelClass: 'col-sm-1',
                    inputType: 'select',
                    inputId: 'ward',
                    selectValues: [1=>1,2=>2,3=>3,4=>4,5=>5],
                ),
            ],
            [
                new FormField(
                    label: 'Road',
                    labelFor: 'road_code',
                    labelClass: 'col-sm-1',
                    inputType: 'select',
                    inputId: 'road_code',
                    selectValues: Roadline::all()->pluck('name','code')->toArray(),
                ),
                new FormField(
                    label: 'Proposed SludgeCollection Date',
                    labelFor: 'proposed_feedback_date',
                    labelClass: 'col-sm-1',
                    inputType: 'text',
                    inputId: 'proposed_feedback_date',
                ),
            ],
        ];
    }

    /**
     * Get form fields for creating feedback.
     *
     * @return array
     */
    public function getCreateFormFields()
    {
        return $this->createFormFields;
    }

    /**
     * Get form fields for showing feedback.
     *
     * @return array
     */
    public function getShowFormFields($feedback)
    {
        $this->showFormFields = [
            ["title" => "Customer Details",
                "fields" => [
                    new FormField(
                        label: 'Address',
                        labelFor: 'address',
                        inputType: 'label',
                        inputId: 'address',
                        labelValue: $feedback->address
                    ),
                    new FormField(
                        label: 'Customer Name',
                        labelFor: 'customer_name',
                        inputType: 'label',
                        inputId: 'customer_name',
                        labelValue: $feedback->customer_name
                    ),
                    new FormField(
                        label: 'Customer Gender',
                        labelFor: 'customer_gender',
                        inputType: 'label',
                        inputId: 'customer_gender',
                        labelValue: $feedback->customer_gender
                    ),
                    new FormField(
                        label: 'Contact',
                        labelFor: 'contact_no',
                        inputType: 'label',
                        inputId: 'contact_no',
                        labelValue: $feedback->contact_no
                    ),
                ]],
            ["title" => "Applicant Details",
                "fields" => [
                    new FormField(
                        label: "Applicant's Name",
                        labelFor: 'applicants_name',
                        inputType: 'label',
                        inputId: 'applicants_name',
                        labelValue: $feedback->applicants_name
                    ),
                    new FormField(
                        label: "Applicant's Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'label',
                        inputId: 'applicant_gender',
                        labelValue: $feedback->applicant_gender
                    ),
                    new FormField(
                        label: "Applicant's Contact",
                        labelFor: 'applicants_contact',
                        inputType: 'label',
                        inputId: 'applicants_contact',
                        labelValue: $feedback->applicants_contact
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-address",
                "hidden" => !$feedback->verified_status,
                "fields" => [
                    new FormField(
                        label: 'Road',
                        labelFor: 'road_code',
                        inputType: 'label',
                        inputId: 'road_code',
                        labelValue: $feedback->road_code
                    ),
                    new FormField(
                        label: 'Containment',
                        labelFor: 'containment_code',
                        inputType: 'label',
                        inputId: 'containment_code',
                        labelValue: $feedback->containment_code
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward',
                        inputType: 'label',
                        inputId: 'ward',
                        labelValue: $feedback->ward
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date',
                        inputType: 'label',
                        inputId: 'proposed_feedback_date',
                        labelValue: $feedback->proposed_feedback_date
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-not-address",
                "hidden" => (bool)$feedback->verified_status,
                "fields" => [
                    new FormField(
                        label: 'Nearest Road',
                        labelFor: 'road_code',
                        inputType: 'label',
                        inputId: 'road_code',
                        labelValue: $feedback->road_code
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward',
                        inputType: 'label',
                        inputId: 'ward',
                        labelValue: $feedback->ward
                    ),
                    new FormField(
                        label: 'Landmark',
                        labelFor: 'landmark',
                        inputType: 'label',
                        inputId: 'landmark',
                        labelValue: $feedback->landmark
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date',
                        inputType: 'label',
                        inputId: 'proposed_feedback_date',
                        labelValue: $feedback->proposed_feedback_date
                    ),
                ]],
        ];

        return $this->showFormFields;
    }

    /**
     * Get form fields for editing applilcation.
     *
     * @return array
     */
    public function getEditFormFields($feedback)
    {
        $this->editFormFields = [
            ["title" => "Customer Details",
                "fields" => [
                    new FormField(
                        label: 'Address',
                        labelFor: 'address',
                        inputType: 'multiple-select',
                        inputId: 'address',
                        selectValues: Building::all()->pluck('address','address')->toArray(),
                        selectedValue: $feedback->address,
                        disabled: true
                    ),
                    new FormField(
                        label: 'Customer Name',
                        labelFor: 'customer_name',
                        inputType: 'text',
                        inputId: 'customer_name',
                        inputValue: $feedback->customer_name
                    ),
                    new FormField(
                        label: 'Customer Gender',
                        labelFor: 'customer_gender',
                        inputType: 'select',
                        inputId: 'customer_gender',
                        selectValues: ["M"=>"Male","F"=>"Female","O"=>"Others"],
                        selectedValue: $feedback->customer_gender
                    ),
                    new FormField(
                        label: 'Contact',
                        labelFor: 'contact_no',
                        inputType: 'number',
                        inputId: 'contact_no',
                        inputValue: $feedback->contact_no
                    ),
                ]],
            ["title" => "Applicant Details",
                "copyDetails"=>true,
                "fields" => [
                    new FormField(
                        label: "Applicant's Name",
                        labelFor: 'applicants_name',
                        inputType: 'text',
                        inputId: 'applicants_name',
                        inputValue: $feedback->applicants_name
                    ),
                    new FormField(
                        label: "Applicant's Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'select',
                        inputId: 'applicant_gender',
                        selectValues: ["M"=>"Male","F"=>"Female","O"=>"Others"],
                        selectedValue: $feedback->applicant_gender
                    ),
                    new FormField(
                        label: "Applicant's Contact",
                        labelFor: 'applicants_contact',
                        inputType: 'number',
                        inputId: 'applicants_contact',
                        inputValue: $feedback->applicants_contact
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-address",
                "hidden" => !$feedback->verified_status,
                "fields" => [
                    new FormField(
                        label: 'Road',
                        labelFor: 'road_code',
                        inputType: 'multiple-select',
                        inputId: 'road_code',
                        selectValues: Roadline::all()->pluck('name','code')->toArray(),
                        selectedValue: $feedback->road_code
                    ),
                    new FormField(
                        label: 'Containment',
                        labelFor: 'containment_code',
                        inputType: 'select',
                        inputId: 'containment_code',
                        selectValues: Containment::all()->pluck('code','code')->toArray(),
                        selectedValue: $feedback->containment_code
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward',
                        inputType: 'select',
                        inputId: 'ward',
                        selectValues: [1=>1,2=>2,3=>3,4=>4,5=>5],
                        selectedValue: $feedback->ward
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date',
                        inputType: 'date',
                        inputId: 'proposed_feedback_date',
                        selectedValue: $feedback->proposed_feedback_date->format('Y-m-d')
                    ),
                ]],
            ["title" => "Building Details",
                "id" => "building-if-not-address",
                "hidden" => $feedback->verified_status,
                "fields" => [
                    new FormField(
                        label: 'Nearest Road',
                        labelFor: 'road_code_no_addr',
                        inputType: 'select',
                        inputId: 'road_code_no_addr',
                        selectValues: Roadline::all()->pluck('name','code')->toArray(),
                        selectedValue: $feedback->road_code
                    ),
                    new FormField(
                        label: 'Ward',
                        labelFor: 'ward_no_addr',
                        inputType: 'select',
                        inputId: 'ward_no_addr',
                        selectValues: [1=>1,2=>2,3=>3,4=>4,5=>5],
                        selectedValue: $feedback->ward
                    ),
                    new FormField(
                        label: 'Landmark',
                        labelFor: 'landmark',
                        inputType: 'text',
                        inputId: 'landmark',
                        inputValue: $feedback->landmark
                    ),
                    new FormField(
                        label: 'Proposed SludgeCollection Date',
                        labelFor: 'proposed_feedback_date_no_addr',
                        inputType: 'date',
                        inputId: 'proposed_feedback_date_no_addr',
                        selectedValue: $feedback->proposed_feedback_date->format('Y-m-d')
                    ),
                ]],
        ];
        return $this->editFormFields;
    }

    /**
     * Get action/route for create form.
     *
     * @return String
     */
    public function getCreateFormAction()
    {
        return $this->createFormAction;
    }

    /**
     * Get action/route for index page of SludgeCollections.
     *
     * @return String
     */
    public function getIndexAction()
    {
        return $this->indexAction;
    }

    /**
     * Get action/route for create page of SludgeCollections.
     *
     * @return String
     */
    public function getCreateRoute()
    {
        return $this->createRoute;
    }

    /**
     * Get action/route for edit form.
     *
     * @return String
     */
    public function getEditFormAction($feedback)
    {
        $this->editFormAction = route('feedback.update', $feedback);
        return $this->editFormAction;
    }

    /**
     * Get form fields for filter.
     *
     * @return array
     */
    public function getFilterFormFields()
    {
        return $this->filterFormFields;
    }

    /**
     * Get all the feedbacks.
     *
     *
     * @return Application[]|Collection
     */
    public function getAllSludgeCollections()
    {
        return Application::latest()->whereNull('deleted_at');
    }

    /**
     * Get Datatables of SludgeCollections.
     *
     * @return DataTables
     * @throws Exception
     */
    public function getDatatable(Request $request)
    {
        return DataTables::of($this->getAllSludgeCollections())
            ->filter(function ($query) use ($request) {
                if ($request->address){
                    $query->where('address','ILIKE','%'.$request->address.'%');
                }
                if ($request->customer_name){
                    $query->where('customer_name','ILIKE','%'.$request->customer_name.'%');
                }
                if ($request->ward){
                    $query->where('ward',$request->ward);
                }
                if ($request->road){
                    $query->where('road_code',$request->road_code);
                }
                if ($request->proposed_feedback_date){
                    $query->where('proposed_feedback_date',$request->proposed_feedback_date);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['feedback.destroy', $model->id]]);
                $content .= '<div class="btn-group">';
                $content .= '<a title="Edit" href="' . route('feedback.edit', [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-edit"></i></a> ';
                $content .= '<a title="Detail" href="' . route('feedback.show', [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-list"></i></a> ';
                $content .= '<a title="History" href="' . route('feedback.show', [$model->id]) . '" class="btn btn-info btn-xs"><i class="fa fa-history"></i></a> ';
                $content .= '<a title="Delete"  class="delete btn btn-danger btn-xs"><i class="fa fa-trash"></i></a> ';
                $content .= '</div>';
                $content .= \Form::close();
                return $content;
            })
            ->editColumn('assessment_status',function($model){
                $content = '<div class="feedback-quick__actions">';
                $content .= $model->assessment_status?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
                $content .= '<a title="Assessment Details" href="' . route("feedback.index", [$model->id]) . '" class="btn-info btn-sm" ' . ($model->assessment_status ? '' : 'disabled') . ' ><i class="fa fa-sticky-note"></i></a> ';
                $content .= '</div>';
                return $content;
            })
            ->editColumn('feedback_status',function($model){
                $content = '<div class="feedback-quick__actions">';
                $content .= $model->feedback_status?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
                if ($model->feedback_status == TRUE) {
                    $content .= '<a title="SludgeCollection Service Details" href="' . route("feedback.index", [$model->id]) . '" class="btn-info btn-sm"><i class="fa fa-recycle"></i></a> ';
                } else {
                    $content .= '<a title="Edit SludgeCollection Service Details" href="' . route("feedback.index", [$model->id]) . '" class="btn-info btn-sm"' . ($model->assessment_status ? '' : 'disabled') . '><i class="fa fa-recycle"></i></a> ';
                }
                $content .= '</div>';
                return $content;
            })
            ->editColumn('feedback_status',function ($model){
                $content = '<div class="feedback-quick__actions">';
                $content .= $model->feedback_status?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
                $content .= '<a title="SludgeCollection Details" href="' . route("feedback.index", [$model->id]) . '" class="btn-info btn-sm"' . ($model->assessment_status && $model->feedback_status ? '' : 'disabled') . '><i class="fa fa-pencil"></i></a> ';
                $content .= '</div>';
                return $content;
            })
            ->rawColumns(['assessment_status','feedback_status','feedback_status','action'])
            ->make(true);
    }


}
