<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildToilet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table= 'fsm.build_toilets';
    protected $primaryKey = 'id';
      /**
     * Enable revisions/history
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;
}
