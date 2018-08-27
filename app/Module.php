<?php

namespace App;

use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;


class Module extends Model
{
    /*
     * User actions
     */

    const ORGANISATIONS = 1;
    const GROUPS = 2;
    const USERS = 3;
    const DATA_SETS = 4;
    const RESOURCES = 5;
    const SIGNALS = 6;
    const DATA_REQUESTS = 7;
    const MAIN_CATEGORIES = 8;
    const ACTIONSHISTORY = 9;
    const SECTIONS = 10;
    const NEWS = 11;
    const ROLES = 12;
    const PAGES = 13;
    const DOCUMENTS = 14;
    const TERMS_OF_USE = 15;
    const TERMS_OF_USE_REQUESTS = 16;
    const LOCALE = 17;
    const DATA_CONVERSIONS = 18;
    const HELP = 19;
    const IMAGES = 20;
    const TAGS = 21;
    const HELP_SECTIONS = 22;
    const HELP_PAGES = 23;
    const CUSTOM_SETTINGS = 24;
    const RIGHTS = 25;
    const THEMES = 26;


    /**
     * Gives back the available user actions.
     *
     * @return Array with user actions as key-value pairs
     */
    public static function getModules()
    {
        return [
            self::ORGANISATIONS          => 'Organisations',
            self::GROUPS                 => 'Groups',
            self::USERS                  => 'Users',
            self::DATA_SETS              => 'Data Sets',
            self::RESOURCES              => 'Resources',
            self::SIGNALS                => 'Signals',
            self::DATA_REQUESTS          => 'Data Requests',
            self::MAIN_CATEGORIES        => 'Main Categories',
            self::ACTIONSHISTORY         => 'Actions History',
            self::TAGS                   => 'Tags',
            self::SECTIONS               => 'Section',
            self::NEWS                   => 'News',
            self::ROLES                  => 'Roles',
            self::PAGES                  => 'Pages',
            self::DOCUMENTS              => 'Documents',
            self::TERMS_OF_USE           => 'Terms Of Use',
            self::TERMS_OF_USE_REQUESTS  => 'Terms of Use Requests',
            self::LOCALE                 => 'Locale',
            self::DATA_CONVERSIONS       => 'Data Conversions',
            self::HELP_SECTIONS          => 'Help Sections',
            self::HELP_PAGES             => 'Help Pages',
            self::IMAGES                 => 'Images',
            self::CUSTOM_SETTINGS        => 'Custom Settings',
            self::RIGHTS                 => 'Rights',
            self::THEMES                 => 'Themes'
        ];
    }

    public static function getModuleName($moduleIndex)
    {
        $modules = self::getModules();

        if (in_array($moduleIndex, array_flip($modules))) {
            return $modules[$moduleIndex];
        }

        return false;
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
    public static function add($request)
    {
        $actions = ActionsHistory::getTypes();

        $validator = \Validator::make($request, [
            'module_name'   => 'required|string|max:191',
            'action'        => 'required|int|digits_between:1,3|in:'. implode(',', array_flip($actions)),
            'action_object' => 'max:191',
            'action_msg'    => 'required|string|max:191',
        ]);

        if (isset($request['action_object'])) {
            $actionObject = $request['action_object'];
        } else {
            $actionObject = "";
        }

        if (!$validator->fails()) {
            try {
                $dbData = [
                    'user_id'       => Auth::user()->id,
                    'module_name'   => $request['module_name'],
                    'action'        => $request['action'],
                    'action_object' => $actionObject,
                    'action_msg'    => $request['action_msg'],
                    'ip_address'    => $_SERVER['REMOTE_ADDR'],
                    'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
                    'occurrence'    => date('Y-m-d H:i:s'),
                ];

                $newRecord = ActionsHistory::create($dbData);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }
    }
}
