<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use App\Helpers\Common;
use Spatie\Permission\Models\Role;
use App\Models\Fsm\HelpDesk;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;

class UserService
{

    protected $session;
    protected $instance;

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
     * Get all users based on the user's role.
     *
     * @param mixed $data Additional data (not used in the method)
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllData($data)
    {
        if (Auth::user()->hasRole('Service Provider - Admin')) {
            return (User::whereHas(
                'roles',
                function ($q) {
                    $q->where('name', 'Service Provider - Emptying Operator')->orWhere('name', 'Service Provider - Help Desk');
                }
            )->where('service_provider_id', '=', Auth::user()->service_provider_id)->get());
        } else if (Auth::user()->hasRole('Treatment Plant')) {
            return (User::where('treatment_plant_id', '=', Auth::user()->treatment_plant_id)->latest('created_at')->get());
        } else if ((Auth::user()->hasRole('Municipality - Sanitation Department'))) {
            return (User::whereIn('user_type', ['Service Provider', 'Treatment Plant', 'Help Desk'])->latest('created_at')->get());
        } else if ($data->user()->hasRole('Municipality - IT Admin') || Auth::user()->hasRole('Municipality - Executive')) {

            return (User::get());
        } else {
            return  $users = User::latest('created_at')->get();
        }
    }

    /**
     * Get roles and help desks based on the user's role.
     *
     * @param mixed $data Additional data (not used in the method)
     * @return array Roles and help desks
     */
    public function getRoleHelpDeskData($data)
    {
        if (!$data->user()->hasRole("Super Admin") && !$data->user()->hasRole("Municipality Admin")) {
            if ($data->user()->hasRole("Municipality - Sanitation Department")) {
                $roles = Role::where('name', '!=', 'Super Admin')
                    ->where('name', 'LIKE', '%Service Provider%')
                    ->pluck('name', 'name');
                $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
            } else {
                $roles = Role::where('name', '!=', 'Super Admin')
                    ->where('name', '!=', 'Service Provider - Admin')
                    ->where('name', '!=', 'Service Provider')
                    ->where('name', 'LIKE', '%Service Provider%')
                    ->pluck('name', 'name');
                $helpDesks = HelpDesk::where('service_provider_id', '=', $data->user()->service_provider_id)->orderBy('name')->pluck('name', 'id');
            }
        } else {
            $roles = Role::where('name', '!=', 'Super Admin')->pluck('name', 'name')->all();
            $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
        }
        return ['roles' => $roles, 'helpDesks' => $helpDesks];
    }


    /**
     * Get user-related data such as roles, treatment plants, help desks, and service providers.
     *
     * @param int $id The user ID
     * @return array User-related data
     */
    public function getUserRelatedData($id)
    {
        $userDetail = User::findorfail($id);
        if ($userDetail->treatment_plant_id) {
            $treatmentPlants = TreatmentPlant::findorfail($userDetail->treatment_plant_id);
        } else {
            $treatmentPlants = null;
        }
        if ($userDetail->help_desk_id) {
            $helpDesks = HelpDesk::findorfail($userDetail->help_desk_id);
        } else {
            $helpDesks = null;
        }
        if ($userDetail->service_provider_id) {
            $serviceProviders = ServiceProvider::findorfail($userDetail->service_provider_id);
        } else {
            $serviceProviders = null;
        }

        $userRoles = array();
        foreach ($userDetail->roles as $role) {
            $userRoles[] = $role->name;
        }
        return ['roles' => $userRoles, 'helpDesks' => $helpDesks, 'treatmentPlants' => $treatmentPlants, 'serviceProviders' => $serviceProviders];
    }

    /**
     * Store or update user data based on the presence of an ID.
     *
     * @param int|null $id The user ID (null for new user)
     * @param array|Request $data The user data (array or Request object)
     * @return void
     */
    public function storeOrUpdate($id, $data)
    {

        if (is_null($id)) {
            $input = $data;
            $user = new User();
            $user->name = $input['name'];
            $user->username = strtolower($input['username']);
            $user->email = strtolower($input['email']);
            $user->password = bcrypt($input['password']);
            $user->user_type = $input['user_type'];
            $user->gender = $input['gender'];
            switch ($user->user_type) {
                case ('Service Provider'):
                    $user->service_provider_id = $input['service_provider_id'];
                    break;
                case ('Treatment Plant'):
                    $user->treatment_plant_id = $input['treatment_plant_id'];
                break;
                case('Help Desk'):
                    if(isset($input['service_provider_id'])){
                        $user->service_provider_id = isset($input['service_provider_id']);
                    }
                    $user->help_desk_id = $input['help_desk_id'];
                    break;
                case ('Solid Waste Management'):
                    $user->transfer_station_id = $input['transfer_station_id'] ? $input['transfer_station_id'] : null;
                    $user->landfill_id = $input['landfill_id'] ? $input['landfill_site_id'] : null;
                    break;
                default:
                    break;
            }
            $user->status = $input['status'];
            $user->save();
            $roles = [$input['roles']];
            // Search Super Admin role
            $super_admin = array_search('Super Admin', $roles);

            // array_seearch returns false if an element is not found
            // so we need to do a strict check here to make sure
            if ($super_admin !== false) {

                // Remove from array
                unset($roles[$super_admin]);
            }
            $user->assignRole($roles);
        } else {
            $user = User::find($id);
            $input = $data->all();
            $user->name = $input['name'];
            $user->gender = $input['gender'];
            $user->username = strtolower($input['username']);
            $user->email = strtolower($input['email']);
            if ($input['password']) {
                $user->password = bcrypt($input['password']);
            }
            $user->user_type = $input['user_type'];
            switch ($user->user_type) {
                case ('Service Provider'):
                    $user->service_provider_id = $input['service_provider_id'];
                    $user->help_desk_id =  null;
                    $user->treatment_plant_id =  null;
                    break;
                case ('Treatment Plant'):
                    $user->treatment_plant_id = $input['treatment_plant_id'];
                    $user->help_desk_id =  null;
                    $user->service_provider_id =  null;

                break;
                case('Help Desk'):
                    if(isset($input['service_provider_id'])){
                        $user->service_provider_id = isset($input['service_provider_id']);
                    }
                    $user->help_desk_id = $input['help_desk_id'];
                    $user->treatment_plant_id =  null;
                break;
                case('Solid Waste Management'):
                    $user->transfer_station_id = $input['transfer_station_id']? $input['transfer_station_id'] : null;
                    $user->landfill_id = $input['landfill_id']? $input['landfill_site_id'] : null;
                break;
            default:
                    break;
            }

            DB::table('fsm.applications')->where('user_id', $user->id)->update(['service_provider_id' => $user->service_provider_id]);

            DB::table('auth.model_has_roles')->where('model_id', $id)->delete();

            $roles = $input['roles'];
            // Search Super Admin role
            $super_admin = array_search('Super Admin', $roles);

            // array_seearch returns false if an element is not found
            // so we need to do a strict check here to make sure
            if ($super_admin !== false) {

                // Remove from array
                unset($roles[$super_admin]);
            }
            $user->assignRole($roles);
            $user->status = $input['status'];
            $user->save();
        }
    }
    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */
    public function download($data)
    {
        $columns = ['Name', 'Gender', 'Username', 'Email', 'Treatment Plant Id', 'Help Desk Id', 'Service Provider ID', 'User Type', 'Status'];
        $query = User::select('name', 'gender', 'username', 'email', 'treatment_plant_id', 'help_desk_id', 'service_provider_id', 'user_type', 'status')
            ->whereNull('deleted_at');
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('User.CSV')
            ->addRowWithStyle($columns, $style); //Top row of excel
        $query->chunk(5000, function ($roadlines) use ($writer) {
            $writer->addRows($roadlines->toArray());
        });
        $writer->close();
    }
}
