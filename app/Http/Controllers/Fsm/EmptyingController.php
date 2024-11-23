<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\EmptyingApiRequest;
use App\Http\Requests\Fsm\EmptyingRequest;
use App\Models\Fsm\Application;
use App\Models\Fsm\Emptying;
use App\Services\Fsm\EmptyingService;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Fsm\Containment;

class EmptyingController extends Controller
{
    protected EmptyingService $emptyingService;

    public function __construct(EmptyingService $emptyingService)
    {
        $this->emptyingService = $emptyingService;
    }

    /**
     * Display a list of emptying history.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $filterFormFields = $this->emptyingService->getFilterFormFields();
        $containment_code = $request->containment_code ? $request->containment_code : '';
        $exportBtnLink = Auth::user()->can('Export Emptyings')?$this->emptyingService->getExportRoute():null;
        return view('fsm.emptying.index',compact('filterFormFields','exportBtnLink','containment_code'));
    }

    /**
     * Prepare data for the DataTable.
     *
     * @param Request $request
     * @return DataTables
     * @throws Exception
     */
    public function getData(Request $request)
    {
        return $this->emptyingService->getDatatable($request);
    }

    /**
     * Display the create form for emptying.
     *
     * @return View
     */
    public function create(int $id)
    {
        return view('fsm.emptying.create',[
            'formAction' => $this->emptyingService->getCreateFormAction(),
            'formFields' => $this->emptyingService->getCreateFormFields($id),
            'indexAction' => url()->previous(),
            'application_id' => $id
        ]);
    }

    /**
     * Store a newly created emptying in storage.
     *
     * @param EmptyingRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(EmptyingRequest $request)
    {
        return $this->emptyingService->createEmptying($request);
    }

    /**
     * Display the specified emptying.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        $emptying = Emptying::find($id);
        if ($emptying) {
            $page_title = "Emptying Details";
            $formFields = $this->emptyingService->getShowFormFields($emptying);
            $indexAction = url()->previous();
            return view('layouts.show',compact('page_title','formFields','emptying','indexAction'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified emptying.
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $emptying = Emptying::find($id);
        if ($emptying) {
            $page_title = "Edit Emptying Service Details";
            $formFields = $this->emptyingService->getEditFormFields($emptying);
            $formAction = $this->emptyingService->getEditFormAction($emptying);
            $indexAction = url()->previous();
            return view('fsm.emptying.edit',compact('page_title','formFields','formAction','indexAction','emptying'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified emptying in storage.
     *
     * @param EmptyingRequest $request
     * @param int $id
     * @return Redirector|RedirectResponse
     */
    public function update(EmptyingRequest $request, $id)
    {
        return $this->emptyingService->updateEmptying($request,$id);
    }

    /**
     * Get the history of changes on the specified emptying
     *
     * @param  int  $id
     * @return Redirector|RedirectResponse
     */
    public function history($id)
    {
        return $this->emptyingService->getEmptyingHistory($id);
    }

    /**
     * Remove the specified emptying from storage.
     *
     * @param  int  $id
     * @return Redirector|RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $emptying = Emptying::findOrFail($id);
            $application = Application::findOrFail($emptying->application_id);
            $application->emptying_status=false;
            $application->save();
            $emptying->delete();
            $containment = Containment::findOrFail($application->containment_code);
            if($containment->no_of_times_emptied > 0)
            {
                $containment->no_of_times_emptied = $containment->no_of_times_emptied - 1;
                $containment->save();
            }
        } catch (\Throwable $e) {
            return redirect(route('emptying.index'))->with('error','Failed to delete Emptying');
        }
        return redirect(route('emptying.index'))->with('success','Emptying deleted successfully');

    }

    /**
     * Export applications to csv.
     *
     * @return Redirector|RedirectResponse
     */

    public function export(Request $request)
    {
        
     try {
            $this->emptyingService->export($request);
        } catch (\Throwable $e) {
            return redirect(route('emptying.index'))->with('error','Failed to export emptyings.');
        }
    }
}
