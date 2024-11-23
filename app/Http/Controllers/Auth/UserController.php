<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Swm\LandfillSite;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\Fsm\ServiceProvider;
use Spatie\Permission\Models\Role;
use DB;
use App\Models\Application;
use Encore\Admin\Layout\Content;
use App\Services\Auth\UserService;
use App\Models\Fsm\HelpDesk;
use App\Enums\UserStatus;

class UserController extends Controller
{
    protected UserService $userService;
    /**
    * Create a new controller instance.
    *
    * @param UserService $userService The user service instance
    * @return void
    */
    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->middleware(['role_or_permission:Super Admin|Municipality - IT Admin |  Municipality - Executive|List Users'], ['only' => ['index','getData']]);
        $this->middleware(['role_or_permission:Super Admin|Municipality - IT Admin |Add User'], ['only' => ['create','store']]);
        $this->middleware(['role_or_permission:Super Admin|Municipality - IT Admin |Edit User'], ['only' => ['edit','update']]);
        $this->middleware(['role_or_permission:Super Admin|Municipality - IT Admin |Delete User'], ['only' => ['destroy']]);
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page_title = "Users";
        $users = $this->userService->getAllData($request);
        return view('users.index')->with(compact('users', 'page_title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $page_title = "Create User";
       if (!$request->user()->hasRole("Super Admin")&&!$request->user()->hasRole("Municipality - IT Admin")){
            if ($request->user()->hasRole("Municipality - Sanitation Department")){
                $roles = Role::where('name','!=', 'Super Admin')
                    ->where('name','LIKE','%Service Provider%')
                    ->orWhere('name','LIKE','%Treatment Plant%')
                    ->pluck('name','name');
                $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');            }
            else {
                $roles = Role::where('name', '!=', 'Super Admin')
                    ->where('name', '!=', 'Service Provider - Admin')
                    ->where('name', '!=', 'Service Provider')
                    ->where('name', 'LIKE', '%Service Provider%')
                    ->pluck('name', 'name');
                $helpDesks = HelpDesk::where('service_provider_id', '=', $request->user()->service_provider_id)->orderBy('name')->pluck('name', 'id');
            }
        }else{
            $roles = Role::where('name', '!=', 'Super Admin')->pluck('name','name')->all();
            $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
        }
        $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->pluck('name', 'id');
        $serviceProviders = ServiceProvider::Operational()->orderBy('company_name')->pluck('company_name', 'id');
        $status = UserStatus::asSelectArray();
        return view('users.create', compact('page_title', 'roles', 'treatmentPlants', 'helpDesks', 'serviceProviders', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $data = $request->all();
        $this->userService->storeOrUpdate($id = null,$data);
        return redirect('auth/users')->with('success','User created successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userDetail = User::findorfail($id);
        $user = $this->userService->getUserRelatedData($id);
        $status = UserStatus::getDescription($userDetail->status);
        $userRoles = array();
        foreach($userDetail->roles as $role) {
          $userRoles[] = $role->name;
        }
        if (!$userDetail->hasRole('Super Admin')) {
            $page_title = "Users";
            return view('users.show')->with([ 'userDetail' => $userDetail, 'userRoles' => $userRoles, 'page_title' => $page_title, 'treatmentPlants' => $user['treatmentPlants'], 'helpDesks' => $user['helpDesks'], 'serviceProviders' => $user['serviceProviders'], 'status' => $status]);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $user = User::findorfail($id);
        if (!$user->hasRole('Super Admin')) {
            $page_title = "Edit User";
            if (!$request->user()->hasRole("Super Admin") && !$request->user()->hasRole("Municipality Admin")){
                $roles = Role::where('name','!=', 'Super Admin')
                    ->where('name','!=', 'Service Provider - Admin')
                    ->where('name','!=', 'Service Provider')
                    ->where('name','LIKE','%Service Provider%')
                    ->pluck('name','name');
                $helpDesks = HelpDesk::where('service_provider_id','=',$request->user()->service_provider_id)->orderBy('name')->pluck('name', 'id');
            }else{
                $roles = Role::where('name', '!=', 'Super Admin')->pluck('name','name')->all();
                $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
            }
            $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->pluck('name', 'id');
            $serviceProviders = ServiceProvider::Operational()->orderBy('company_name')->pluck('company_name', 'id');
            $userRole = $user->roles->pluck('name','name')->all();
            $role_arr = array();
            foreach ($user->roles as $role) {
                $role_arr[] = $role->name;
            }
            $user->roles = $role_arr;
            $status = UserStatus::asSelectArray();
            return view('users.edit', compact('page_title', 'user', 'roles', 'treatmentPlants', 'helpDesks', 'serviceProviders', 'status'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = User::findorfail($id);
        if (!$user->hasRole('Super Admin')) {
            $data = $request->all();

        $this->userService->storeOrUpdate($user->id,$request);
        return redirect('auth/users')->with('success','User updated successfully');
        } else {
            abort(404);
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
        $user = User::findorfail($id);
        if (!$user->hasRole('Super Admin')) {
            User::destroy($id);
            return redirect('auth/users')->with('success','User deleted successfully');
        } else {
            abort(404);
        }
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getLoginActivity($id)
    {
        $userDetail = User::findorfail($id);
        //$authentications = User::find($id)->authentications;

        $last_login_at = User::find($id)->lastLoginAt();
        $last_login_ip = User::find($id)->lastLoginIp();

        if (!$userDetail->hasRole('Super Admin')) {
            $page_title = "Login Activity";
            return view('users.login-activity',compact('page_title', 'last_login_at', 'last_login_ip', 'userDetail'));
        } else {
            abort(404);
        }
    }


    public function export(Request $request)
    {

        $data = $request->all();
        return $this->userService->download($data);

    }

}
