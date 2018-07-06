<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionsHistory extends Model
{
    protected $guarded = ['id'];
    protected $table = 'action_history';

    const MODULE_NAMES = [
        'Category',
        'Tag',
        'Organization',
        'Group'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
