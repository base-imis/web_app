<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContainmentInspection extends Model
{
    use SoftDeletes;

    protected $table = 'fsm.containment_inspections';

    // Primary key is ebps_id (string)
    protected $primaryKey = 'ebps_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ebps_id',
        'septic_tank_sealed',
        'septic_compartments',
        'depth_of_septic_tank',
        'length_in_design',
        'width_in_design',
        'range_of_septic_tank',
        'septic_tank_chamber_requirement',
        'holes_in_partition_wall',
        'septic_tank_outlet_pipe',
        'septic_tank_location',
        'septic_tank_manhole',
        'date_of_manhole',
        'compliance_status',
        'outlet_connection_design',
        'outlet_connection_field',
    ];

    protected $casts = [
        'septic_tank_sealed' => 'boolean',
        'septic_compartments' => 'boolean',
        'depth_of_septic_tank' => 'boolean',
        'length_in_design' => 'boolean',
        'width_in_design' => 'boolean',
        'range_of_septic_tank' => 'boolean',
        'septic_tank_chamber_requirement' => 'boolean',
        'holes_in_partition_wall' => 'boolean',
        'compliance_status' => 'boolean',
        'date_of_manhole' => 'date',
    ];
}
