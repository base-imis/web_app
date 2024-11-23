<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            [
                'name' => 'Super Admin'
            ],
            [
                'name' => 'Municipality - Executive',
            ],
            [
                'name' => 'Municipality - Building Permit Department',
            ],
            [
                'name' => 'Municipality - Building Surveyor (Ward)',
            ],
            [
                'name' => 'Municipality - Infrastructure Department',
            ],
            [
                'name' => 'Municipality - Tax Department',
            ],
            [
                'name' => 'Municipality - Water Supply Department',
            ],
            [
                'name' => 'Municipality - Sanitation Department',
            ],
            [
                'name' => 'Municipality - IT Admin',
            ],
            [
                'name' => 'Municipality - Public Health Department',
            ],
            [
                'name' => 'Municipality - Help Desk',
            ],
            [
                'name' => 'Municipality - Solid Waste',
            ],
            [
                'name' => 'Service Provider - Admin',
            ],
            [
                'name' => 'Service Provider - Help Desk',
            ],
            [
                'name' => 'Service Provider - Emptying Operator',
            ],
            [
                'name' => 'Treatment Plant',
            ],
            [
                'name' => 'External',
            ],
        ];
        foreach ($roles as $role){
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name){
                case 'Municipality - Executive':
                    $createdRole->givePermissionTo(Permission::all()->whereNotIn('type',['Add','Edit','Delete']));
                    break;

                case 'Municipality - Building Permit Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures','Building Surveys','Containments']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Roadlines','Drains','Sewers'])
                    ->whereNotIn('type',['Add','Edit','Delete']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIn('name',['View Building On Map','View Road On Map','Containments Map Layer','Buildings Map Layer','Roads Map Layer',
                    'Places Map Layer','Land Use Map Layer','Wards Map Layer','Summarized Grids Map Layer','Sewers Line Map Layer',
                    'Sanitation System Map Layer','Water Body Map Layer','General Map Tools','Data Export Map Tools',
                    'Decision Map Tools','Summary Information Map Tools']));
                    $createdRole->givePermissionTo(Permission::all()
                    ->WhereIn('group',['Dashboard'])
                    ->whereIn('name',['Containment type Chart',
                    'Building Structures by building use Chart',
                    'Building Structures per Ward Chart'
                   ]));
                    break;

                case 'Municipality - Building Surveyor (Ward)':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['API'])
                    ->where('name','Access Building Survey API'));
                    break;

                case 'Municipality - Infrastructure Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Roads','Drains','Sewers']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIn('name',['View Road On Map', 'Roads Map Layer','Sewers Line Map Layer','Summary Information Map Tools','Data Export Map Tools','General Map Tools',
                    'Decision Map Tools','Export in General Map Tools',
                    'Export in Decision Map Tools',
                    'Export in Summary Information Map Tools',
                    'Export in KML Drag And Drop']));
                    break;

                case 'Municipality - Tax Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps', 'Property Tax Collection ISS', 'Water Supply ISS', 'Buildings Structures'])
                    ->whereIN('name',['Tax Payment Status Buildings Map Layer',
                        'List Property Tax Collection',
                        'Import Property Tax Collection From CSV',
                        'Export Property Tax Collection Info',
                        'Wards Map Layer', 'Summarized Grids Map Layer', 'List Building Structures', 'View Building Structure', 'Export Building Structures'])

                   );
                    break;

                case 'Municipality - Water Supply Department':
                        $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps', 'Property Tax Collection ISS', 'Water Supply ISS', 'Buildings Structures'])
                        ->whereIN('name',['Water Payment Status Map Layer', 
                            'List Water Supply Collection',
                            'Import Water Supply Collection From CSV',
                            'Export Water Supply Collection Info',
                            'Wards Map Layer', 'Summarized Grids Map Layer', 'List Building Structures', 'View Building Structure', 'Export Building Structures'])
    
                );
                break;


                case 'Municipality - Solid Waste':
                    $createdRole->givePermissionTo(Permission::where(function ($query) {
                        $query->where('group', 'ILIKE', '%Swm%')
                                ->orWhere('group', 'ILIKE', '%swm-payment%');
                    })->get()
                );
                break;
                    
    
                case 'Municipality - Sanitation Department':
                     $createdRole->givePermissionTo(Permission::all()->whereIn('group',['API'])
                    ->where('name','Access Sewer Connection API'));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Sewer Connection']));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Help Desks','Service Providers', 'Employee Infos',
                    'Treatment Plants','Hotspots','CT/PT','Toilet Users','Users','Containments', 'KPI Target', 'KPI Dashboard']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures','Building Surveys', 'Sewers',
                    'Applications','Emptyings','Feedbacks','Sludge Collections','Vacutug Types','Treatment Plant Effectiveness'
                    ])->whereNotIn('type',['Edit','Delete']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',[
                    'Treatment Plant Effectiveness','Treatment Plant Test'
                    ])->whereNotIn('type',['Add','Edit','Delete']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])->where('type','View on map'));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->WhereIn('name',['Containments Map Layer',
                    'Buildings Map Layer',
                    'Treatment Plants Map Layer',
                    'Roads Map Layer',
                    'Places Map Layer',
                    'Land Use Map Layer',
                    'Wards Map Laye',
                    'Sewers Line Map Layer',
                    'Sanitation System Map Layer',
                    'Hotspot Identifications Map Layer',
                    'CT/PT General Information Map Layer',
                    'Water Body Map Layer',
                    'Service Delivery Map Tools',
                    'Decision Map Tools',
                    'Export in Decision Map Tools',
                    'Export in KML Drag And Drop',
                    'Public Health Map Layer']));
                    $createdRole->givePermissionTo(Permission::all()
                    ->WhereIn('group',['Dashboard', 'KPI Dashboard'])
                    ->whereIn('name',['Containments Map Layer',
                    'Key Performance Indicators',
                    'FSM Related Chart',
                    'Proposed Emptying Date for Next 4W Chart',
                    'Proposed Emptying Date by wards for next 4W Chart',
                    'Containment type Chart',
                    'Sludge Collections by Treatment Plants Chart',
                    'Emptying service by Year Chart',
                    'FSM Feedback Chart',
                    'Applications, Emptying services, Feedback details by Wards Chart',
                    'Cost Paid for Emptying Services Chart',
                    'Sewer Length Per Ward Chart',
                    'Hotspots Per Ward Chart']));
                    break;

                case 'Municipality - IT Admin':
                    $createdRole->givePermissionTo(Permission::all()->whereNotIn('type',['Edit','Delete']));
                    break;

                case 'Municipality - Public Health Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Yearly Waterborne Cases','Hotspots']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Dashboard'])
                    ->whereIn('name',['Hotspots Per Ward Chart']));
                    break;

                case 'Municipality - Help Desk':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures','Containments'])
                    ->whereIn('type',['View','List']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Applications','Feedbacks']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIn('name',['View Building On Map','View Nearest Road To Containment On Map','View Road On Map','Containments Map Layer',
                    'Buildings Map Layer','Treatment Plants Map Layer','Roads Map Layer','Places Map Layer','Water Body Map Layer','Service Delivery Map Tools']));
                    break;

                case 'Service Provider - Admin':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures','Containments', 'Kpi Target','Feedbacks','Sludge Collections','Applications'])
                    ->whereIn('type',['View','List']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Emptyings','Vacutug Types','Employee Infos','Users']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereNotIn('name',['Tax Payment Status Buildings Map Layer','Water Payment Status Map Layer','Land Use Map Layer',
                    'Wards Map Layer','Summarized Grids Map Layer','Sewers Line Map Layer','Hotspot Identifications Map Layer',
                    'FSM Campaigns Map Layer','CT/PT General Information Map Layer','Water Body Map Layer','General Map Tools',
                    'Data Export Map Tools','Decision Map Tools','Summary Information Map Tools']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Dashboard'])
                    ->whereIn('name',['FSM Related Chart','Proposed Emptying Date for Next 4W Chart','Proposed Emptying Date by wards for next 4W Chart',
                    'Sludge Collections by Treatment Plants Chart', 'Emptying service by Year Chart', 'FSM Feedback Chart','Applications,
                    Emptying services, Feedback details by Wards Chart','Containment type Chart', 'Emptying Requests By Structure Types', 'Monthly Requests By Operators/Service Providers', 'Emptying Requests By Low Income Communities and Other Communities']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['KPI Dashboard'])
                    ->whereIn('name',['Application Response Chart','Safe Desludging Chart','Customer Satisfaction Chart',
                    'PPE Compliance Chart', 'KPI']));
                    break;

                case 'Service Provider - Help Desk':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures', 'Kpi Target','Containments'])
                    ->where('type',['View']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Applications','Feedbacks'])->whereNotIn('type','Edit'));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIn('name',['View Building On Map','View Nearest Road To Containment On Map','View Road On Map','Containments Map Layer',
                    'Buildings Map Layer','Treatment Plants Map Layer','Roads Map Layer','Places Map Layer','Water Body Map Layer','Service Delivery Map Tools']));
                    break;


                case 'Service Provider - Emptying Operator':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['API'])
                    ->where('name','Access Emptying Service API'));
                    break;

                case 'Treatment Plant':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Sludge Collections']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Applications'])
                    ->where('type','List'));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Treatment Plant Effectiveness']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                        ->whereIn('name',['Treatment Plants Map Layer','Buildings Map Layer','Containments Map Layer']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Dashboard'])
                    ->whereIn('name',['Sludge Collections by Treatment Plants Chart', 'Emptying service by Year Chart', 'Applications,
                    Emptying services, Feedback details by Wards Chart',
                    ]));
                    break;

                case 'External':

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures','Roads',
                    'Drains', 'Building Surveys','Employee Infos', 'Public Health','Sanitation System Types', 'Treatment Plant Test',
                    'Sanitation System Technology', 'Property Tax Collection ISS','Water Supply ISS',
                    'Sewers', 'WaterSupply Network', 'Help Desks', 'Service Providers', 'Treatment Plants',
                    'Vacutug Types', 'Treatment Plant Effectiveness', 'CT/PT', 'Toilet Users','Hotspots', 'Emptyings',
                    'Containments','Feedbacks','Sludge Collections','Users', 'Roles','Applications'])
                    ->whereIn('type',['View','List', 'Map']));


                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->WhereIn('name',['Containments Map Layer',
                    'Buildings Map Layer',
                    'Treatment Plants Map Layer',
                    'Roads Map Layer',
                    'Places Map Layer',
                    'Land Use Map Layer',
                    'Wards Map Laye',
                    'Sewers Line Map Layer',
                    'Sanitation System Map Layer',
                    'Hotspot Identifications Map Layer',
                    'CT/PT General Information Map Layer',
                    'Water Body Map Layer',
                    'Public Health Map Layer']));

                        break;


            }
        }
    }
}
