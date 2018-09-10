<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Image extends Model
{
    use RecordSignature;

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';

    const INACTIVE_IMAGE = 0;
    const TYPE_IMAGE = 'item';
    const TYPE_THUMBNAIL = 'thumb';

    protected $guarded = ['id'];
}
