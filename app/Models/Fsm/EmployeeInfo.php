<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
class EmployeeInfo extends Model
{
    use HasFactory;
    use SoftDeletes;
    use RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.employees';
    protected $primaryKey = 'id';
    
    /**
     * Scope a query to only include operational status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
