<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
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

    /*
     * User actions
     */
    const USER = 1;
    const PASSWORD_RESET = 2;
    const ROLE = 3;
    const ROLE_RIGHT = 4;
    const NEWSLETTER_DIGEST_LOG = 5;
    const LOCALE = 6;
    const ORGANISATION = 7;
    const TERMS_OF_USE = 8;
    const CATEGORY = 9;
    const SECTION = 10;
    const PAGE = 11;
    const DATA_REQUEST = 12;
    const DATA_SET = 13;
    const DATA_SET_SUB_CATEGORY = 14;
    const DATA_SET_GROUP = 15;
    const RESOURCE = 16;
    const SIGNAL = 17;
    const ELASTIC_DATA_SET = 18;
    const TERMS_OF_USE_REQUEST = 19;
    const USER_FOLLOW = 20;
    const USER_SETTING = 21;
    const USER_TO_ORG_ROLE = 22;
    const CUSTOM_SETTING = 23;

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'actions_history';

    /**
     * Gives back the available user actions.
     *
     * @return Array with user actions as key-value pairs
     */
    public static function getActionObjects()
    {
        return [
            self::USER                  => 'User',
            self::PASSWORD_RESET        => 'Password Reset',
            self::ROLE                  => 'Role',
            self::ROLE_RIGHT            => 'Role Right',
            self::NEWSLETTER_DIGEST_LOG => 'Newsletter Digest Log',
            self::LOCALE                => 'Locale',
            self::ORGANISATION          => 'Organisation',
            self::TERMS_OF_USE          => 'Terms Of Use',
            self::CATEGORY              => 'Category',
            self::SECTION               => 'Section',
            self::PAGE                  => 'Page',
            self::DATA_REQUEST          => 'Data Request',
            self::DATA_SET              => 'Data Set',
            self::DATA_SET_SUB_CATEGORY => 'Data Set Sub Category',
            self::DATA_SET_GROUP        => 'Data Set Group',
            self::RESOURCE              => 'Resource',
            self::SIGNAL                => 'Signal',
            self::ELASTIC_DATA_SET      => 'Elastic Data Set',
            self::TERMS_OF_USE_REQUEST  => 'Terms Of Use Request',
            self::USER_FOLLOW           => 'User Follow',
            self::USER_SETTING          => 'User Setting',
            self::USER_TO_ORG_ROLE      => 'User To Org Role',
            self::CUSTOM_SETTING        => 'Custom Setting',
        ];
    }

    /**
     * Gives back the available user action types.
     *
     * @return Array with user action types as key-value pairs
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SEE     => __('custom.saw'),
            self::TYPE_ADD     => __('custom.added'),
            self::TYPE_MOD     => __('custom.modified'),
            self::TYPE_DEL     => __('custom.deleted'),
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
            self::TYPE_ADD     => __('custom.added'),
            self::TYPE_MOD     => __('custom.modified'),
            self::TYPE_DEL     => __('custom.deleted'),
        ];
    }

    public static function getTypesLinkWords()
    {
        return [
            self::TYPE_ADD     => __('custom.into'),
            self::TYPE_MOD     => __('custom.in'),
            self::TYPE_DEL     => __('custom.from'),
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Record action history by module and action for a logged user
     *
     * @param string moduleName - comming from MODULE_NAMES (required)
     * @param integer type - comming from TYPE_ constants (required)
     * @param string|integer object - comming from the action object constants or is custom string (required)
     * @param string message - used to describe the taken action (required)
     *
     * @return boolean wheather user is authorized or not
     */
    public static function add($moduleName, $type, $object, $message) {
        if (Auth::check()) {
            $post = [
                'api_key'       => Auth::user()->api_key,
                'module_name'   => $moduleName,
                'action'        => $type,
                'action_object' => $object,
                'action_msg'    => $message,
                'ip_address'    => $_SERVER['REMOTE_ADDR'],
                'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
            ];

            $rq = Request::create('/api/addActionHistory', 'POST', $post);
            $api = new ActionsHistoryController($rq);
            $result = $api->addActionHistory($rq)->getData();
        }

        return !empty($result->success);
    }
}
