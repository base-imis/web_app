<?php

namespace App\Models\Cwis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataJmp extends Model
{
    // use HasFactory;
    protected $table = 'cwis.data_jmp';
    protected $fillable = [
        'sub_category_id',
        'parameter_id',
        'assmntmtrc_dtpnt',
        'unit',
        'co_cf',
        'data_value',
        'data_type',
        'sym_no',
        'year',
        'source_id'];
}
