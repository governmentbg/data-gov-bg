<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class TermsOfUseRequest extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];
}
