<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Http\Controllers\Traits\RecordSignature;

class TermsOfUseRequest extends Model
{
    use RecordSignature;
    use Searchable;

    protected $guarded = ['id'];

    const STATUS_NEW = 1;
    const STATUS_PROCESSED = 2;

    public function toSearchableArray()
    {
        return [
            'descript'  => $this->descript,
            'id'        => $this->id,
        ];
    }

    public function searchableAs()
    {
        return 'terms_of_use_requests';
    }
}
