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
    const TOOL_DBMS = 27;
    const TOOL_FILE = 28;


    /**
     * Gives back the available user actions.
     *
     * @return Array with user actions as key-value pairs
     */
    public static function getModules()
    {
        return [
            self::ORGANISATIONS          => 'Organisation',
            self::GROUPS                 => 'Group',
            self::USERS                  => 'User',
            self::DATA_SETS              => 'Dataset',
            self::RESOURCES              => 'Resource',
            self::SIGNALS                => 'Signal',
            self::DATA_REQUESTS          => 'DataRequest',
            self::MAIN_CATEGORIES        => 'MainCategories',
            self::ACTIONSHISTORY         => 'ActionsHistory',
            self::TAGS                   => 'Tags',
            self::SECTIONS               => 'Section',
            self::NEWS                   => 'News',
            self::ROLES                  => 'Role',
            self::PAGES                  => 'Page',
            self::DOCUMENTS              => 'Document',
            self::TERMS_OF_USE           => 'TermsOfUse',
            self::TERMS_OF_USE_REQUESTS  => 'TermsofUseRequests',
            self::LOCALE                 => 'Locale',
            self::DATA_CONVERSIONS       => 'DataConversion',
            self::HELP_SECTIONS          => 'HelpSection',
            self::HELP_PAGES             => 'HelpPage',
            self::IMAGES                 => 'Image',
            self::CUSTOM_SETTINGS        => 'CustomSetting',
            self::RIGHTS                 => 'Right',
            self::THEMES                 => 'Theme',
        ];
    }

    public static function getToolModules()
    {
        return [
            self::TOOL_DBMS => 'DBMS',
            self::TOOL_FILE => 'File',
        ];
    }

    public static function getModuleName($moduleIndex)
    {
        $modules = env('IS_TOOL') ? self::getToolModules() : self::getModules();

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
        if (Auth::check() || env('IS_TOOL')) {
            $actions = ActionsHistory::getTypes();

            $validator = \Validator::make($request, [
                'module_name'   => 'required|string|max:191',
                'action'        => 'required|int|digits_between:1,3|in:'. implode(',', array_flip($actions)),
                'action_msg'    => 'required|string|max:191',
            ]);

            $actionObject = isset($request['action_object']) ? $request['action_object'] : '';

            if (!$validator->fails()) {
                try {
                    $dbData = [
                        'module_name'   => $request['module_name'],
                        'action'        => $request['action'],
                        'action_object' => $actionObject,
                        'action_msg'    => $request['action_msg'],
                        'ip_address'    => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A',
                        'user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A',
                        'occurrence'    => date('Y-m-d H:i:s'),
                    ];

                    if (!env('IS_TOOL')) {
                        $dbData['user_id'] = Auth::user()->id;
                    }

                    if (isset($request['status'])) {
                        $dbData['status'] = $request['status'];
                    }

                    ActionsHistory::create($dbData);
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }
    }
}
