<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Role;
use App\Http\Controllers\Api\ActionsHistoryController;

class ActionsHistory extends Model
{
    /*
     * User action types
     */
    const TYPE_SEE = 1;
    const TYPE_ADD = 2;
    const TYPE_MOD = 3;
    const TYPE_DEL = 4;
    const TYPE_ADD_MEMBER = 5;
    const TYPE_EDIT_MEMBER = 6;
    const TYPE_DEL_MEMBER = 7;
    const TYPE_FOLLOW = 11;
    const TYPE_UNFOLLOW = 12;
    const TYPE_LOGIN = 13;
    const TYPE_CONFIRM_ACCOUNT = 14;
    const TYPE_SEND = 15;

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'actions_history';

    /**
     * Gives back the available user action types.
     *
     * @return Array with user action types as key-value pairs
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SEE              => __('custom.saw'),
            self::TYPE_ADD              => __('custom.added'),
            self::TYPE_MOD              => __('custom.modified'),
            self::TYPE_DEL              => __('custom.deleted'),
            self::TYPE_ADD_MEMBER       => __('custom.add_members'),
            self::TYPE_EDIT_MEMBER      => __('custom.edit_member'),
            self::TYPE_DEL_MEMBER       => __('custom.del_member'),
            self::TYPE_FOLLOW           => __('custom.followed'),
            self::TYPE_UNFOLLOW         => __('custom.unfollowed'),
            self::TYPE_LOGIN            => __('custom.login'),
            self::TYPE_CONFIRM_ACCOUNT  => __('custom.confirm_account'),
            self::TYPE_SEND             => __('custom.send'),
        ];
    }

    public static function getEventNewsletterTypes()
    {
        return [
            self::TYPE_MOD              => __('custom.modified'),
            self::TYPE_DEL              => __('custom.deleted'),
        ];
    }

    /**
     * Gives back the available public user action types.
     *
     * @return Array with user action types as key-value pairs
     */
    public static function getPublicTypes()
    {
        return [
            self::TYPE_ADD  => __('custom.added'),
            self::TYPE_MOD  => __('custom.modified'),
            self::TYPE_DEL  => __('custom.deleted'),
        ];
    }

    public static function getTypesLinkWords()
    {
        return [
            self::TYPE_ADD  => __('custom.into'),
            self::TYPE_MOD  => __('custom.in'),
            self::TYPE_DEL  => __('custom.from'),
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }
}
