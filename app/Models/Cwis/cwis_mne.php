<?php

namespace App\Models\Cwis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cwis_mne extends Model
{
    //Old Data
    // use HasFactory;
    // protected $table = 'cwis.data_mne';
   

    protected $table = 'cwis.data_cwis';
  
        protected $fillable = [
    
        'year',
       ];
}
