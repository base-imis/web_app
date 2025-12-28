<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Model;

class STMonitoringDashboardCount extends Model
{
    /**
     * If your table is NOT pluralised automatically
     * (very common in FSM schemas)
     */
    protected $table = 'fsm.st_monitoring_dashboard_counts';

    /**
     * Primary key
     */
    protected $primaryKey = 'id';

    /**
     * No timestamps unless your table has them
     */
    public $timestamps = false;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'upto_plinth',
        'above_plinth',
        'completion',
        'inspection_requested',
        'inspection_completed',
    ];
}
