<?php

namespace App\Models\Fsm;

use App\Models\Fsm\Ctpt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class CtptUsers extends Model
{
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.ctpt_users';
    protected $primaryKey = 'id';


    public function toilet()
    {
        return $this->belongsTo('App\Models\Fsm\Ctpt','toilet_id','id');
    }
}
