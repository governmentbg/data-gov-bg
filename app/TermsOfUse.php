<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermsOfUse extends Model
{
    protected $guarded = ['id'];
    protected $table = 'terms_of_use';

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }
}
