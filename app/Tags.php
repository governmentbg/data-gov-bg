<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Tags extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];
    protected $table = 'tags';
    protected $hidden = ['pivot'];

    public function dataSetTags()
    {
        return $this->belongsToMany('App\DataSet', 'data_set_tags', 'tag_id', 'data_set_id');
    }

    public static function checkName($query, $name, $exceptId = false)
    {
        $query->where(DB::raw('lower(name)'), mb_strtolower($name));

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query;
    }
}
