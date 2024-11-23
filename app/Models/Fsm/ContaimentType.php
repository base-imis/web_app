<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaimentType extends Model
{
    use HasFactory;
    protected $table = 'fsm.containment_types';
    protected $primaryKey = 'id';
}
