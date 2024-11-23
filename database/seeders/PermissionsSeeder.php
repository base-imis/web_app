<?php
// Last Modified Date: 07-05-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class PermissionsSeeder extends Seeder
{
    /**
     * HERE ARE THE PERMISSION TYPES CURRENTLY IN THE SYSTEM
     * FEEL FREE TO ADD NEW ONES AND UPDATE THIS LIST RESPECTIVELY AS WELL
     *
     * "Add"
     * "API - Emptying"
     * "API - Supervisor"
     * "API - Survey"
     * "Chart"
     * "Delete"
     * "Download"
     * "Edit"
     * "Export"
     * "History"
     * "Import"
     * "List"
     * "Map Layer"
     * "Map Tool"
     * "Miscellaneous"
     * "View"
     * "View on map"
     *
     */

    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $grouped_permissions = [
            [
                "group" => "Users",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Users"
                    ],
                    [
                        "type" => "View",
                        "name" => "View User"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add User"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit User"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete User"
                    ],
                    [
                        "type" => "Activity",
                        "name" => "Login Activity User"
                    ],
                ]
            ],
            [
                "group" => "Roles",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Roles"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Role"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Role"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Role"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Role"
                    ],
                ]
            ],
            [
                "group" => "Roads",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Roadlines"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Roadline"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Roadline History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Roadline"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Roadline"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Roadline"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Roadlines to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Roadlines to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Roadlines to KML"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Roadline On Map"
                    ],
                ]
            ],
            [
                "group" => "Drain",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Drains"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Drain"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Drain History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Drain"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Drain"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Drain"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Drains to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Drains to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Drains to KML"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Drain On Map"
                    ],
                ]
            ],
            [
                "group" => "Sewers",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Sewers"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Sewer"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Sewer History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Sewer"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Sewer"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Sewer"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sewers to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sewers to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sewers to KML"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Sewer On Map"
                    ],
                ]
            ],
            [
                "group" => "WaterSupply Network",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List WaterSupply Network"
                    ],
                    [
                        "type" => "View",
                        "name" => "View WaterSupply Network"
                    ],
                    [
                        "type" => "History",
                        "name" => "View WaterSupply Network History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add WaterSupply Network"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit WaterSupply Network"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete WaterSupply Network"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export WaterSupply Network to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export WaterSupply Network to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export WaterSupply Network to KML"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View WaterSupply Network On Map"
                    ],
                ]
            ],
            [
                "group" => "Help Desks",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Help Desks"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Help Desk"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Help Desk"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Help Desk"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Help Desk"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Help Desk"
                    ],
                ]
            ],
            [
                "group" => "Service Providers",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Service Providers"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Service Provider"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Service Provider"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Service Provider"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Service Provider"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Service Provider History"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Service Providers to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Service Providers to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Service Providers to KML"
                    ],
                ]
            ],
            [
                "group" => "Treatment Plants",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Treatment Plants"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Treatment Plant"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Treatment Plant History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Treatment Plant"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Treatment Plant"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Treatment Plant"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Treatment Plant on Map"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Treatment Plants to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Treatment Plants to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Treatment Plants to KML"
                    ],
                ]
            ],
            [
                "group" => "Vacutug Types",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Vacutug Types"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Vacutug Type"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Vacutug Type"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Vacutug Type"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Vacutug Type"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Vacutug Type"
                    ],
                ]
            ],
            [
                "group" => "Treatment Plant Effectiveness",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Treatment Plant Effectiveness"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Treatment Plant Effectiveness"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Treatment Plant Effectiveness"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Treatment Plant Effectiveness"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Treatment Plant Effectiveness"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Treatment Plant Effectiveness"
                    ],
                ]
            ],
            [
                "group" => "Containments",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Containments"
                    ],
                    [
                        "type" => "List",
                        "name" => "List Emptying Services"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Containment"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Containment"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Containment"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Containment"
                    ],
                    [
                        "type" => "Import",
                        "name" => "Import Containment from Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Containments to Excel"
                    ],
                    [
                        "type" => "List",
                        "name" => "List Containment Buildings"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Containment Building"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Containment Building"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Make Building of Containment Main"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Delete Building from Containment"
                    ],


                ]
            ],
            [
                "group" => "CT/PT",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List CT/PT General Informations"
                    ],
                    [
                        "type" => "View",
                        "name" => "View CT/PT General Information"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add CT/PT General Information"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit CT/PT General Information"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete CT/PT General Information"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export CT/PT General Informations"
                    ],
                    [
                        "type" => "History",
                        "name" => "View CT/PT History"
                    ],
                    [
                        "type" => "Map",
                        "name" => "View CT/PT General Information on Map"
                    ]
                ]
            ],
            [
                "group" => "Toilet Users",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Male or Female User"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Male or Female User"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Male or Female User"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Male or Female User"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Male or Female User History"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Male or Female User"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Male or Female User"
                    ],
                ]
            ],
            [
                "group" => "Hotspots",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Hotspot Identifications"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Hotspot Identification"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Hotspot Identification"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Hotspot Identification"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Hotspot Identification"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Hotspot Identification"
                    ],
                ],

            ],
            [
                "group" => "Applications",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Applications"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Application"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Application"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Application"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Application"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Applications"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Generate Application Report"
                    ],
                ]
            ],
            [
                "group" => "Emptyings",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Emptyings"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Emptying"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Emptying"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Emptying"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Emptying"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Emptyings"
                    ],
                ]
            ],
            [
                "group" => "Sludge Collections",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Sludge Collections"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Sludge Collection"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Sludge Collection"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Sludge Collection"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Sludge Collection"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sludge Collections"
                    ],
                ]
            ],
            [
                "group" => "API",
                "perms" => [
                    [
                        "type" => "API - Emptying",
                        "name" => "Access Emptying Service API"
                    ],
                    [
                        "type" => "API - Supervisor",
                        "name" => "Access Supervisor API"
                    ],
                    [
                        "type" => "API - Survey",
                        "name" => "Access Building Survey API"
                    ],
                    [
                        "type" => "API - Sewer Connection",
                        "name" => "Access Sewer Connection API"
                    ],
                ]
            ],
            [
                "group" => "Swm Service Payment",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List SWM Service Payment Collection"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export SWM Service Payment Collection From CSV"
                    ],
                    [
                        "type" => "Import",
                        "name" => "Import SWM Service Payment Collection From CSV"
                    ],
                    
                ]
            ],
            [
                "group" => "Building Structures",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Building Structures"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Building Structure"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Building Structure"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Building Structure"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Building Structure"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Building Structures"
                    ],
                ]
            ],
            [
                "group" => "Building Surveys",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Building Surveys"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Approve Building Survey"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Building Survey"
                    ],
                    [
                        "type" => "Download",
                        "name" => "Download Building Survey"
                    ],
                    [
                        "type" => "Miscellaneous",
                        "name" => "Approve Building Survey"
                    ],
                ]
            ],
            [
                "group" => "Sewer Connection",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Sewer Connection"
                    ],
                    [
                        "type" => "Approve",
                        "name" => "Approve Sewer Connection"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Sewer Connection"
                    ],

                ]
            ],
            [
                "group" => "Yearly Waterborne Cases",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Yearly Waterborne Cases"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Yearly Waterborne Cases"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Yearly Waterborne Cases"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Yearly Waterborne Cases"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Yearly Waterborne Cases"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Yearly Waterborne Cases"
                    ],
                ]
            ],



            [
                "group" => "Dashboard",
                "perms" => [

                    [
                        "type" => "Chart",
                        "name" => "FSM Related Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Proposed Emptying Date for Next 4W Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Proposed Emptying Date by wards for next 4W Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Containment type Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Sludge Collections by Treatment Plants Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Emptying service by Year Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "FSM Feedback Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Applications, Emptying services, Feedback details by Wards Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Cost Paid for Emptying Services Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Building Structures by building use Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Building Structures per Ward Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Tax Revenue Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Water Supply Payment Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Sewer Length Per Ward Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Hotspots Per Ward Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Emptying Requests By Structure Types"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Monthly Requests By Operators/Service Providers"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Emptying Requests By Low Income Communities and Other Communities"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Building sanitation systems chart"
                    ]

                ]
            ],
            [
                "group" => "Feedbacks",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Feedbacks"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Feedback"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Feedback"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Feedback"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Feedback"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Feedbacks"
                    ],
                ]
            ],
            [
                "group" => "Employee Infos",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Employee Infos"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Employee Info"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Employee Info"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Employee Info"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Employee Info"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Employee Infos"
                    ],
                ]
            ],
            [
                "group" => "Maps",
                "perms" => [
                    [
                        "type" => "View on map",
                        "name" => "View Building On Map"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Containment On Map"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Nearest Road To Containment On Map"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Nearest Road To Building On Map"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Road On Map"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Containments Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Buildings Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Tax Payment Status Buildings Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Water Payment Status Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Treatment Plants Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Roads Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Places Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Land Use Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Wards Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Summarized Grids Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Sewers Line Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Sanitation System Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Hotspot Identifications Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "FSM Campaigns Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "CT/PT General Information Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Water Body Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Water Samples Map Layer"
                    ],
                    [
                        "type" => "Map Tool",
                        "name" => "Service Delivery Map Tools"
                    ],
                    [
                        "type" => "Map Tool",
                        "name" => "General Map Tools"
                    ],
                    [
                        "type" => "Map Tool",
                        "name" => "Data Export Map Tools"
                    ],
                    [
                        "type" => "Map Tool",
                        "name" => "Decision Map Tools"
                    ],
                    [
                        "type" => "Map Tool",
                        "name" => "Summary Information Map Tools"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export in General Map Tools"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export in Decision Map Tools"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export in Summary Information Map Tools"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export in KML Drag And Drop"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Public Health Map Layer"
                    ],
                    [
                        "type" => "Map Layer",
                        "name" => "Drains Map Layer"
                    ],

                    [
                        "type" => "Map Layer",
                        "name" => "WaterSupply Network Map Layer"
                    ],
                ],

            ],

            [
                "group" => "Water Samples",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Water Samples"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Water Samples"
                    ],
                    [
                        "type" => "History",
                        "name" => "View Water Samples History"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Water Samples"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Water Samples"
                    ],

                    [
                        "type" => "View on map",
                        "name" => "View Water Samples on Map"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Water Samples"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Water Samples to Excel"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Water Samples to Shape"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Water Samples to KML"
                    ],
                ]
            ],

            [
                "group" => "Sanitation System Types",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Sanitation System Types"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Sanitation System Type"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Sanitation System Type"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Sanitation System Type"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Sanitation System Type"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sanitation System Types"
                    ],
                ]
            ],
            [
                "group" => "Sanitation System Technology",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Sanitation System Technology"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Sanitation System Technology"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Sanitation System Technology"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Sanitation System Technology"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Sanitation System Technology"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Sanitation System Technology"
                    ],
                    [
                        "type" => "Approve",
                        "name" => "Approve Sewer Connection"
                    ],
                    [
                        "type" => "Map",
                        "name" => "Map Sewer Connection"
                    ],
                ]
            ],
            [
                "group" => "KPI Dashboard",
                "perms" => [
                    [
                        "type" => "Card",
                        "name" => "KPI"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Application Response Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Safe Desludging Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "Customer Satisfaction Chart"
                    ],
                    [
                        "type" => "Chart",
                        "name" => "PPE Compliance Chart"
                    ]
                ]
            ],
            [
                "group" => "KPI Target",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List KPI Target"
                    ],
                    [
                        "type" => "View",
                        "name" => "View KPI Target"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add KPI Target"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit KPI Target"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete KPI Target"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export KPI Target"
                    ]
                ]
            ],
            [
                "group" => "Property Tax Collection ISS",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Property Tax Collection"
                    ],
                    [
                        "type" => "Import",
                        "name" => "Import Property Tax Collection From CSV"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Property Tax Collection Info"
                    ],

                ],
            ],
            [
                "group" => "Water Supply ISS",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Water Supply Collection"
                    ],
                    [
                        "type" => "Import",
                        "name" => "Import Water Supply Collection From CSV"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Water Supply Collection Info"
                    ],

                ],
            ],
            [
                "group" => "Treatment Plant Test",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Treatment Plant Test"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Treatment Plant Test"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Treatment Plant Test"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Treatment Plant Test"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Treatment Plant Test"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Treatment Plant Test"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Treatment Plant Test History"
                    ],
                ]
            ],
            [
                "group" => "Low Income Communities",
                "perms" => [
                    [
                        "type" => "List",
                        "name" => "List Low Income Communities"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Low Income Community"
                    ],
                    [
                        "type" => "Add",
                        "name" => "Add Low Income Community"
                    ],
                    [
                        "type" => "Edit",
                        "name" => "Edit Low Income Community"
                    ],
                    [
                        "type" => "Delete",
                        "name" => "Delete Low Income Community"
                    ],
                    [
                        "type" => "Export",
                        "name" => "Export Low Income Communities"
                    ],
                    [
                        "type" => "View",
                        "name" => "View Low Income Community History"
                    ],
                    [
                        "type" => "View on map",
                        "name" => "View Low Income Community On Map"
                    ],
                ]
            ],
            
            
            [
                "group" => "Data Import",
                "perms" => [
                    [
                        "type" => "",
                        "name" => ""
                    ]
                ]
            ]
        ];
    

        foreach ($grouped_permissions as $group) {
            foreach ($group['perms'] as $permission){
                $existPermission = DB::table('auth.permissions')
                    ->where('name', $permission['name'])
                    ->first();
                if (!$existPermission) {
                    Permission::create([
                        'name' => $permission['name'],
                        'type' => $permission['type'],
                        'group' => $group['group']
                    ]);
                }
            }
        }

    }
}
