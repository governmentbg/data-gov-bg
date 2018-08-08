<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionsHistory extends Model
{
    const TYPE_SEE = 1; // View record details
    const TYPE_ADD = 2; // Add new record
    const TYPE_MOD = 3; // Modify existing record
    const TYPE_DEL = 4; // Delete a record

    const MODULE_NAMES = [
        'Category',
        'Tag',
        'Organisation',
        'Group',
        'User',
        'Dataset',
        'Resource'
    ];

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'actions_history';

    public static function getModuleNames()
    {
        return self::MODULE_NAMES;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_SEE     => 'See',
            self::TYPE_ADD     => 'Add',
            self::TYPE_MOD     => 'Modify',
            self::TYPE_DEL     => 'Delete',
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
