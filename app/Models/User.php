<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models;

use App\Models\Fsm\HelpDesk;
use App\Models\Fsm\ServiceProvider;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use \App\Models\Fsm\TreatmentPlant;
use Yadahan\AuthenticationLog\AuthenticationLogable;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use HasApiTokens;
    use SoftDeletes;
    use AuthenticationLogable;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'auth.users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'username', 'password', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function wards()
    {
        return $this->belongsTo('App\Ward');
    }

    public function treatment_plant()
    {
        return $this->belongsTo(TreatmentPlant::class, 'treatment_plant_id');
    }

    public function help_desk()
    {
        return $this->belongsTo(HelpDesk::class, 'help_desk_id');
    }

    public function service_provider()
    {
        return $this->belongsTo('App\Models\Fsm\ServiceProvider', 'service_provider_id');
    }

    public function assessments()
    {
        return $this->hasMany('App\Models\Fsm\Assessment');

    }
    public function emptyingServices()
    {
        return $this->hasMany('App\Models\Fsm\EmptyingService');

    }
    public function feedbacks()
    {
        return $this->hasMany('App\Models\Fsm\Feedback');
    }
}

