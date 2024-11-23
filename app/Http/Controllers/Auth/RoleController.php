<?php
// Last Modified Date: 08-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Session;
use Illuminate\Support\Facades\Input;
use Laracasts\Flash\Flash;
use DB;
use Form;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List Roles', ['only' =>['index']]);
        $this->middleware('permission:View Role', ['only' =>['show']]);
        $this->middleware('permission:Add Role', ['only' =>['create', 'store']]);
        $this->middleware('permission:Edit Role', ['only' =>['edit', 'update']]);
        $this->middleware('permission:Delete Role', ['only' =>['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Roles";
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        return view('roles.index',[
            'page_title'=>$page_title,
            'roles' => $roles
        ]);
    }

    public function searchPermission(Request $request, $id)
    {
        $search = $request->search;
        $page_title = 'Edit Role';
        $role = Role::find($id);
       $permission = DB::select("SELECT * FROM permissions WHERE LOWER(permissions.name) LIKE LOWER('%" . $search . "%')");

       $rolePermissions = DB::table("role_has_permissions")
       ->where("role_has_permissions.role_id",$id)

       ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
       ->all();
       return view('roles.edit',compact('page_title', 'role','permission','rolePermissions'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = 'Add Role';
        $permission = Permission::get();
        $grouped_permissions = $this->getGroupedPermissions();
        $rolePermissions = array();
        return view('roles.create', compact('page_title','permission', 'rolePermissions','grouped_permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
        'name' => 'required|unique:pgsql.auth.roles,name',
        'permission' => 'required',
        ]);
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return redirect('auth/roles')->with('success','Role created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_title = 'Edit Role';
        $role = Role::find($id);
        $permission = Permission::get();
        $grouped_permissions = $this->getGroupedPermissions();
        $rolePermissions = DB::table("auth.role_has_permissions")->where("auth.role_has_permissions.role_id",$id)
        ->pluck('auth.role_has_permissions.permission_id','auth.role_has_permissions.permission_id')
        ->all();
        return view('roles.edit',compact('page_title', 'role','permission','rolePermissions','grouped_permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if($role && $role->name != 'Super Admin') {
            $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
            ]);

            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions($request->input('permission'));

            return redirect('auth/roles')->with('success','Role updated successfully');
        }
        else {
            return redirect('auth/roles')->with('error','Failed to update role');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if($role && $role->name != 'Super Admin') {
            $role->delete();

            return redirect('auth/roles')->with('success','Role deleted successfully');
        }
        else {
            return redirect('auth/roles')->with('error','Failed to delete role');
        }
    }

    /**
     * Get the specified resource from storage.
     *
     * @param null
     * @return array $groupedPermissions
     */
    public function getGroupedPermissions(){

        $building = Permission::where("name","ILIKE","%building%")->orderBy("name")->get();
        $roads = Permission::where("name","ILIKE","%road%")->orderBy("name")->get();
        $containment = Permission::where("name","ILIKE","%containment%")->orderBy("name")->get();
        $application = Permission::where("name","ILIKE","%application%")->orderBy("name")->get();
        $assessment = Permission::where("name","ILIKE","%assessment%")->orderBy("name")->get();
        $emptying = Permission::where("name","ILIKE","%emptying%")->orderBy("name")->get();
        $feedback = Permission::where("name","ILIKE","%feedback%")->orderBy("name")->get();
        $vacutug = Permission::where("name","ILIKE","%vacutug%")->orderBy("name")->get();
        $treatment_plant = Permission::where("name","ILIKE","%treatment%")->orderBy("name")->get();
        $employee_info = Permission::where("name","ILIKE","%employee%")->orderBy("name")->get();
        $service_provider = Permission::where("name","ILIKE","%service provider%")->orderBy("name")->get();
        $sludge_collection = Permission::where("name","ILIKE","%sludge%")->orderBy("name")->get();
        $help_desks = Permission::where("name","ILIKE","%help%")->orderBy("name")->get();
        $places = Permission::where("name","ILIKE","%place%")->orderBy("name")->get();
        $fsm_campaigns = Permission::where("name","ILIKE","%campaign%")->orderBy("name")->get();
        $budget_allocations = Permission::where("name","ILIKE","%budget%")->orderBy("name")->get();
        $hotspot_identification = Permission::where("name","ILIKE","%hotspot%")->orderBy("name")->get();
        $data_export = Permission::where("name","ILIKE","%export%")->orderBy("name")->get();
        $data_import = Permission::where("name","ILIKE","%import%")->orderBy("name")->get();
        $users = Permission::where("name","ILIKE","%user%")->orderBy("name")->get();
        $roles = Permission::where("name","ILIKE","%role%")->orderBy("name")->get();
        $toilets = Permission::where("name","ILIKE","%toilet%")->orderBy("name")->get();
        $cwis = Permission::where("name","ILIKE","%cwis%")->orderBy("name")->get();
        $mne = Permission::where("name","ILIKE","%m&e%")->orderBy("name")->get();
        $jmp = Permission::where("name","ILIKE","%jmp%")->orderBy("name")->get();
        $map = Permission::where("name","ILIKE","%map%")->orderBy("name")->get();
        $api = Permission::where("name","ILIKE","%api%")->orderBy("name")->get();
        $chart = Permission::where("name","ILIKE","%chart%")->orderBy("name")->get();
        $ward = Permission::where("name","ILIKE","%ward%")->orderBy("name")->get();
        $sewer = Permission::where("name","ILIKE","%sewer%")->orderBy("name")->get();
        $tax = Permission::where("name","ILIKE","%tax%")->orderBy("name")->get();
        $water_supply = Permission::where("name","ILIKE","%water supply%")->orderBy("name")->get();
        $ctpt = Permission::where("name","ILIKE","%ct/pt%")->orderBy("name")->get();
        $ctpt_users = Permission::where("name","ILIKE","%Male or Female%")->orderBy("name")->get();
        $transfer_log_in = Permission::where("name","ILIKE","%transfer log in%")->orderBy("name")->get();
        $transfer_log_out = Permission::where("name","ILIKE","%transfer log out%")->orderBy("name")->get();
        $waste_recycle = Permission::where("name","ILIKE","%waste recycle%")->orderBy("name")->get();
        $collection_point = Permission::where("name","ILIKE","%collection point%")->orderBy("name")->get();
        $transfer_station = Permission::where("name","ILIKE","%transfer station%")->orderBy("name")->get();
        $landfill_site = Permission::where("name","ILIKE","%landfill site%")->orderBy("name")->get();
        $public_health = Permission::where("name","ILIKE","%yearly waterborne cases%")->orderBy("name")->get();
        $sanitation_system_type = Permission::where("name","ILIKE","%sanitation system type%")->orderBy("name")->get();
        $sanitation_system_technology = Permission::where("name","ILIKE","%sanitation system technology%")->orderBy("name")->get();
        $kpi_target = Permission::where("name","ILIKE","%kpi target%")->orderBy("name")->get();
        $kpi_dashboard = Permission::where("name","ILIKE","%kpi dashboard%")->orderBy("name")->get();
        $drains = Permission::where("name","ILIKE","%drain%")->orderBy("name")->get();
        $water_supply_network = Permission::where("name","ILIKE","%WaterSupply Network%")->orderBy("name")->get();
        $lic = Permission::where("name","ILIKE","%Low Income%")->orderBy("name")->get();
        $swm_payment = Permission::where("name","ILIKE","%Swm Service Payment%")->orderBy("name")->get();


        /*$customers = Permission::where("name","ILIKE","%customer%")->orderBy("name")->get();

        $slums = Permission::where("name","ILIKE","%slum%")->orderBy("name")->get();
        $transfer_stations = Permission::where("name","ILIKE","%transfer%")->orderBy("name")->get();*/
        $groupedPermissions = collect([
            'Buildings' => $building,
            'Roads' => $roads,
            'Containments' => $containment,
            'Applications' => $application,
            'Assessments' => $assessment,
            'Emptying' => $emptying,
            'Feedback' => $feedback,
            'Vacutug'=> $vacutug,
            'Treatment Plants'=> $treatment_plant,
            'Employee Informations'=> $employee_info,
            'Service Provider'=> $service_provider,
            'Sludge Collection'=> $sludge_collection,
            'Help Desks'=> $help_desks,
            'Places'=> $places,
            'FSM Campaigns'=> $fsm_campaigns,
            'Budget Allocations'=> $budget_allocations,
            'Hotspot Identification'=> $hotspot_identification,
            'Data Export'=> $data_export,
            'Data Import'=> $data_import,
            'Users'=> $users,
            'Roles'=> $roles,
            'Toilets'=> $toilets,
            'CWIS'=> $cwis,
            'Data Framework for Monitoring and Evaluation'=>$mne,
            'Data Framework for JMP'=>$jmp,
            'Map'=> $map,
            'API'=> $api,
            'Chart'=>$chart,
            'Ward'=>$ward,
            'Sewer'=>$sewer,
            'Tax'=>$tax,
            'Water Supply'=>$water_supply,
            'CT/PT' => $ctpt,
            'CT/PT Users' => $ctpt_users,
            'Transfer Log In' => $transfer_log_in,
            'Transfer Log Out' => $transfer_log_out,
            'Waste Recycle' => $waste_recycle,
            'Collection Point' => $collection_point,
            'Transfer Station' => $transfer_station,
            'Landfill Site' => $landfill_site,
            'Public Health' => $public_health,
            'Sanitation System Type' => $sanitation_system_type,
            'Sanitation System Technology' => $sanitation_system_technology,
            'Kpi Dashboard' => $kpi_dashboard,
            'Kpi Target' => $kpi_target,
            'Drains'=>$drains,
            'Water Supply Network' => $water_supply_network,
            'Low Income Communities' =>  $lic,
            'Swm Service Payment' => $swm_payment
            /*'Customer'=>$customers,

            'Slums'=>$slums,
            'Transfer Stations'=>$transfer_stations,*/
        ]);

        return $groupedPermissions;
    }

    public function getRoles(Request $request){

        $type = $request->user_type;
        if($type == "Help Desk"){
        $roles = Role::where('name', "LIKE", "%$type%")
                    ->pluck('name', 'name');
        } else {
            $user_id = $request->id;
            $roles = Role::where('name', "LIKE", "%$type%")->where('name',"NOT ILIKE", '%Help Desk%')
                    ->pluck('name', 'name');
        }
        $html = '<select name="roles[]" class="form-control chosen-select" id="roles" multiple="true">';

        foreach($roles as $role)
        {
            if($request->roles && in_array($role, json_decode($request->roles)))
            {
                $html .= '<option value="'.$role.'" selected>'.$role.'</option>';
            }
            else
            {
                $html .= '<option value="'.$role.'">'.$role.'</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}
