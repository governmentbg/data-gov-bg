<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Document extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];
}
