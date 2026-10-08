<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Fsm\Application;

class Feedback extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table name along with the schema.
     *
     * @var String
     */
    protected $table= 'fsm.feedbacks';
     /**
     * Get the application associated with the application.
     *
     *
     * @return BelongsTo
     */
    public function application(){
        return $this->belongsTo(Application::class,'id','application_id');
    }
}
