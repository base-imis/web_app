<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainmentType extends Model
{
    use HasFactory;
    protected $table = 'fsm.containment_types';
    protected $primaryKey = 'id';

    public $timestamps = false; // Disable automatic timestamps
}
