<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
class HelpDesk extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.help_desks';
    
    
    public function users()
    {
        return $this->hasMany('App\Models\User', 'help_desk_id', 'id');
    }
    
    public static function boot() {
        parent::boot();
        self::deleting(function($helpDesk) {
            $helpDesk->users()->each(function($user) {
                $user->delete();
            });
        });
    }
    
}
