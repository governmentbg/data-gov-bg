<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\User;
use App\Locale;
use App\Module;
use App\DataSet;
use App\Resource;
use App\RoleRight;
use App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class OrganisationController extends ApiController
{
    /**
     * Add new organisation record
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param integer data[type] - optional
     * @param string data[description] - optional
     * @param string data[uri] - optional
     * @param string data[logo] - optional
     * @param string data[logo_filename] - optional
     * @param string data[logo_mimetype] - optional
     * @param string data[logo_data] - optional
     * @param string data[activity_info] - optional
     * @param string data[contacts] - optional
     * @param integer data[parent_org_id] - optional
     * @param integer data[active] - optional
     * @param integer data[approved] - optional
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with organisation id or error
     */
    public function addOrganisation(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        $data = $request->get('data', []);
        $publicTypes = Organisation::getPublicTypes();

        $validator = \Validator::make($data, [
            'locale'                => 'nullable|string|max:5',
            'name'                  => 'required_with:locale|max:191',
            'name.bg'               => 'required_without:locale|string|max:191',
            'type'                  => 'required|int|max:191|in:'. implode(',', array_keys($publicTypes)),
            'description'           => 'nullable|max:8000',
            'uri'                   => 'nullable|string|unique:organisations,uri|max:191',
            'logo'                  => 'nullable|string|max:191',
            'logo_filename'         => 'nullable|string|max:191',
            'logo_mimetype'         => 'nullable|string|max:191',
            'logo_data'             => 'nullable|max:16777215',
            'activity_info'         => 'nullable|max:8000',
            'contacts'              => 'nullable|max:8000',
            'parent_org_id'         => 'nullable|int|digits_between:1,10',
            'active'                => 'nullable|bool',
            'approved'              => 'nullable|bool',
            'custom_fields.*.label' => 'nullable|max:191',
            'custom_fields.*.value' => 'nullable|max:8000',
        ]);

        $organisation = new Organisation;
        $imageError = false;

        if (!empty($data['logo'])) {
            try {
                $img = \Image::make($data['logo']);

                $organisation->logo_file_name = empty($data['logo_filename'])
                    ? basename($data['logo'])
                    : $data['logo_filename'];
                $organisation->logo_mime_type = $img->mime();

                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                $img->save($path);
                $organisation->logo_data = file_get_contents($path);

                fclose($temp);
            } catch (\Exception $ex) {
                $imageError = true;

                if (
                    isset($data['migrated_data'])
                    && Auth::user()->username == 'migrate_data'
                ) {
                    $imageError = false;
                    $data['logo'] = null;
                }

                $validator->errors()->add('logo', $this->getImageTypeError());
            }

            if (isset($data['logo_filename']) && isset($data['logo_mimetype']) && isset($data['logo_data'])) {
                $organisation->logo_file_name = $data['logo_filename'];
                $organisation->logo_mime_type = $data['logo_mimetype'];
                $organisation->logo_data = $data['logo_data'];
            }

            if (isset($organisation->logo_data) && !$this->checkImageSize($organisation->logo_data)) {
                $imageError = true;

                $validator->errors()->add('logo', $this->getImageSizeError());
            }
        }

        $errors = $validator->errors()->messages();

        if ($validator->passes() && !$imageError) {
            $locale = isset($data['locale']) ? $data['locale'] : null;
            $organisation->type = $data['type'];

            DB::beginTransaction();

            try {
                $organisation->name = $this->trans($data['locale'], $data['name']);
                $organisation->descript = !empty($data['description'])
                    ? $this->trans($data['locale'], $data['description'])
                    : null;

                $organisation->uri = !empty($request->data['uri'])
                    ? $request->data['uri']
                    : $this->generateOrgUri();

                $organisation->activity_info = !empty($data['activity_info']) ? $this->trans($data['locale'], $data['activity_info']) : null;
                $organisation->contacts = !empty($data['contacts']) ? $this->trans($data['locale'], $data['contacts']) : null;
                $organisation->parent_org_id = !empty($data['parent_org_id']) ? $data['parent_org_id'] : null;
                $organisation->active = isset($data['active']) ? $data['active'] : 0;

                if (
                    isset($data['migrated_data'])
                    && Auth::user()->username == 'migrate_data'
                ) {
                    if (!empty($data['created_by'])) {
                        $organisation->created_by = $data['created_by'];
                    }
                }

                if (!empty($data['approved'])) {
                    $organisation->approved = $data['approved'];
                } else {
                    $organisation->approved = \Auth::user()->approved;
                }

                if (!empty($data['custom_fields'])) {
                    foreach ($data['custom_fields'] as $fieldSet) {
                        if (is_array($fieldSet['value']) && is_array($fieldSet['label'])) {
                            if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                                $customFields[] = [
                                    'value' => $fieldSet['value'],
                                    'label' => $fieldSet['label'],
                                ];
                            }
                        } elseif (!empty($fieldSet['label'])) {
                            $customFields[] = [
                                'value' => [
                                    $locale => $fieldSet['value']
                                ],
                                'label' =>[
                                    $locale => $fieldSet['label']
                                ]
                            ];
                        }
                    }
                }

                $organisation->save();

                if (\Auth::user()) {
                    $role = Role::getOrgAdminRole();

                    if (!isset($role)) {
                        return $this->errorResponse(__('custom.add_role_fail'));
                    }

                    $userToOrgRole = new UserToOrgRole;
                    $userToOrgRole->org_id = $organisation->id;
                    $userToOrgRole->user_id = \Auth::user()->id;
                    $userToOrgRole->role_id = $role->id;

                    try {
                        $userToOrgRole->save();
                    } catch (QueryException $ex) {
                        Log::error($ex->getMessage());
                        DB::rollback();

                        return $this->errorResponse(__('custom.add_org_fail'));
                    }
                }

                if (!empty($customFields)) {
                    if (!$this->checkAndCreateCustomSettings($customFields, $organisation->id)) {
                        DB::rollback();

                        return $this->errorResponse(__('custom.add_org_fail'));
                    }
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $organisation->id,
                    'action_msg'       => 'Added organisation',
                ];

                Module::add($logData);

                DB::commit();

                return $this->successResponse(['org_id' => $organisation->id], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_org_fail'), $errors);
    }

    /**
     * Edit organisation record
     *
     * @param string api_key - required
     * @param integer org_id - required
     * @param array data -required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param integer data[type] - required
     * @param string data[description] - optional
     * @param string data[uri] - optional
     * @param string data[logo] - optional
     * @param string data[logo_filename] - optional
     * @param string data[logo_mimetype] - optional
     * @param string data[logo_data] - optional
     * @param string data[activity_info] - optional
     * @param string data[contacts] - optional
     * @param integer data[parent_org_id] - optional
     * @param integer data[active] - optional
     * @param integer data[approved] - optional
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function editOrganisation(Request $request)
    {
        $data = $request->get('data', []);
        $data['org_id'] = $request->org_id ? $request->org_id : null;

        $validator = \Validator::make($data, [
            'org_id'                   => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'locale'                   => 'nullable|string|max:5',
            'name'                     => 'required_with:locale|max:191',
            'name.bg'                  => 'required_without:locale|string|max:191',
            'type'                     => 'required|int|in:'. implode(',', array_keys(Organisation::getPublicTypes())),
            'description'              => 'nullable|max:8000',
            'uri'                      => 'nullable|string|max:191',
            'logo'                     => 'nullable|string|max:191',
            'logo_filename'            => 'nullable|string|max:191',
            'logo_mimetype'            => 'nullable|string|max:191',
            'logo_data'                => 'nullable|string|max:16777215',
            'activity_info'            => 'nullable|max:8000',
            'contacts'                 => 'nullable|max:8000',
            'parent_org_id'            => 'nullable|int|digits_between:1,10',
            'active'                   => 'nullable|bool',
            'approved'                 => 'nullable|bool',
            'custom_fields.*.label'    => 'nullable|max:191',
            'custom_fields.*.value'    => 'nullable|max:8000',
        ]);

        $organisation = Organisation::find($data['org_id']);

        if ($organisation) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'created_by' => $organisation->created_by,
                    'org_id'     => $organisation->id
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }
        }

        $imageError = false;

        if (!empty($data['logo'])) {
            try {
                $img = \Image::make($data['logo']);

                $organisation->logo_file_name = empty($data['logo_filename'])
                    ? basename($data['logo'])
                    : $data['logo_filename'];
                $organisation->logo_mime_type = $img->mime();

                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                $img->save($path);
                $organisation->logo_data = file_get_contents($path);

                fclose($temp);
            } catch (\Exception $ex) {
                $imageError = true;
                $validator->errors()->add('logo', $this->getImageTypeError());
            }
        }

        if (isset($data['logo_filename']) && isset($data['logo_mimetype']) && isset($data['logo_data'])) {
            $orgData['logo_file_name'] = $data['logo_filename'];
            $orgData['logo_mime_type'] = $data['logo_mimetype'];
            $orgData['logo_data'] = $data['logo_data'];
        }

        if (
            isset($organisation->type)
            && !in_array($organisation->type, array_keys(Organisation::getPublicTypes()))
        ) {
            return $this->errorResponse(__('custom.no_org_found'));
        }

        $validator->after(function ($validator) use ($data) {
            if (!empty($data['uri'])) {
                if (Organisation::where('uri', $data['uri'])->where('id', '!=', $data['org_id'])->value('name')) {
                    $validator->errors()->add('uri', __('custom.taken_uri'));
                }
            }
        });

        if (isset($organisation->logo_data)) {
            if (!$this->checkImageSize($organisation->logo_data)) {
                $validator->errors()->add('logo', $this->getImageSizeError());
            }
        }

        $errors = $validator->errors()->messages();

        if (
            !$validator->fails() && !$imageError
        ) {
            if (!isset($orgData['logo_data'])) {
                DB::beginTransaction();

                $orgData = [];

                if (!empty($data['uri'])) {
                    $orgData['uri'] = $data['uri'];
                }

                if (!empty($data['type'])) {
                    $orgData['type'] = $data['type'];
                }

                if (!empty($data['parent_org_id'])) {
                    $orgData['parent_org_id'] = $data['parent_org_id'];
                } else {
                    $orgData['parent_org_id'] = null;
                }

                if (!empty($data['active'])) {
                    $orgData['active'] = $data['active'];
                } else {
                    $orgData['active'] = Organisation::ACTIVE_FALSE;
                }

                if (!empty($data['approved'])) {
                    $orgData['approved'] = $data['approved'];
                } else {
                    $orgData['approved'] = Organisation::APPROVED_FALSE;
                }

                if (!empty($data['custom_fields'])) {
                    $customFields = $data['custom_fields'];
                }

                try {
                    if (!empty($data['name'])) {
                        $orgData['name'] = $this->trans($data['locale'], $data['name']);
                    }

                    if (!empty($data['description'])) {
                        $orgData['descript'] = $this->trans($data['locale'], $data['description']);
                    } else {
                        $orgData['descript'] = null;
                    }

                    if (!empty($data['activity_info'])) {
                        $orgData['activity_info'] = $this->trans($data['locale'], $data['activity_info']);
                    } else {
                        $orgData['activity_info'] = null;
                    }

                    if (!empty($data['contacts'])) {
                        $orgData['contacts'] = $this->trans($data['locale'], $data['contacts']);
                    } else {
                        $orgData['contacts'] = null;
                    }

                    if (!empty($orgData)) {
                        foreach ($orgData as $prop => $val) {
                            $organisation->$prop = $val;
                        }

                        $organisation->save();
                    }

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $organisation->id)) {
                            DB::rollback();

                            return $this->errorResponse(__('custom.edit_org_fail'));
                        }
                    }

                    DB::commit();

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                        'action'           => ActionsHistory::TYPE_MOD,
                        'action_object'    => $organisation->id,
                        'action_msg'       => 'Edited organisation',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    DB::rollback();

                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_org_fail'), $errors);
    }

    /**
     * Delete organisation record
     *
     * @param string api_key - required
     * @param integer org_id - required
     *
     * @return json with success or error
     */
    public function deleteOrganisation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'org_id' => 'required|exists:organisations,id,deleted_at,NULL|max:110',
        ]);

        if (
            !$validator->fails()
            && !empty($organisation = Organisation::find($request->org_id))
        ) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_ALL,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'created_by' => $organisation->created_by,
                    'org_id'     => $organisation->id
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            if (!in_array($organisation->type, array_keys(Organisation::getPublicTypes()))) {
                return $this->errorResponse(__('custom.no_org_found'));
            }

            try {
                $organisation->delete();
                $organisation->deleted_by = \Auth::id();
                $organisation->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $request->org_id,
                    'action_msg'       => 'Deleted organisation',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_org_fail'), $validator->errors()->messages());
    }

    /**
     * List organisations by criteria
     *
     * @param array criteria - optional
     * @param array criteria[org_ids] - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[user_id] - optional
     * @param boolean criteria[active] - optional
     * @param boolean criteria[approved] - optional
     * @param int criteria[type] - optional
     * @param string criteria[keywords] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list with organisations or error
     */
    public function listOrganisations(Request $request)
    {
        $results = [];
        $count = 0;

        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|max:191',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'org_ids'      => 'nullable|array',
                'org_ids.*'    => 'nullable|int|digits_between:1,10',
                'locale'       => 'nullable|string|max:5',
                'active'       => 'nullable|bool',
                'approved'     => 'nullable|bool',
                'org_id'       => 'nullable|int|digits_between:1,10',
                'user_id'      => 'nullable|int|exists:users,id|digits_between:1,10',
                'type'         => 'nullable|int|max:191|in:'. implode(',', array_keys(Organisation::getPublicTypes())),
                'keywords'     => 'nullable|string|max:191',
                'order'        => 'nullable|array'
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $criteria = [];

            if (isset($post['api_key'])) {
                $user = \App\User::where('api_key', $post['api_key'])->first();
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_VIEW,
                    [
                        'user' => $user
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                if (isset($request->criteria['active'])) {
                    $criteria['active'] = $request->criteria['active'];
                }

                if (isset($request->criteria['approved'])) {
                    $criteria['approved'] = $request->criteria['approved'];
                }
            } else {
                $criteria['active'] = 1;
                $criteria['approved'] = 1;
            }

            if (isset($request->criteria['org_id'])) {
                $criteria['parent_org_id'] = $request->criteria['org_id'];
            }

            if (isset($request->criteria['user_id'])) {
                $criteria['user_id'] = $request->criteria['user_id'];
            }

            $locale = \LaravelLocalization::getCurrentLocale();

            try {
                $query = Organisation::with('CustomSetting');

                if (isset($request->criteria['type'])) {
                    $query->where('type', $request->criteria['type']);
                } else {
                    $query->where('type', '!=', Organisation::TYPE_GROUP);
                }

                if (!empty($request->criteria['org_ids'])) {
                    $query->whereIn('organisations.id', $request->criteria['org_ids']);
                }

                if (!empty($request->criteria['keywords'])) {
                    $ids = Organisation::search($request->criteria['keywords'])->get()->pluck('id');
                    $query->whereIn('organisations.id', $ids);
                }

                if (!empty($criteria['user_id'])) {
                    $query->whereHas('userToOrgRole', function($q) use ($criteria) {
                        $q->where('user_id', $criteria['user_id']);
                    });
                    unset($criteria['user_id']);
                }

                if (!empty($criteria)) {
                    $query->where($criteria);
                }

                $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
                $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

                $columns = [
                    'id',
                    'name',
                    'type',
                    'descript',
                    'contacts',
                    'activity_info',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                ];

                if (isset($request->criteria['order']['field'])) {
                    if (!in_array($request->criteria['order']['field'], $columns)) {
                        return $this->errorResponse(__('custom.invalid_sort_field'));
                    }
                }

                $count = $query->count();

                $transFields = ['name', 'descript'];

                $transCols = Organisation::getTransFields();

                if (in_array($field, $transFields)) {
                    $col = $transCols[$field];
                    $query->select('translations.label', 'translations.group_id', 'translations.text', 'organisations.*')
                        ->leftJoin('translations', 'translations.group_id', '=', 'organisations.' . $field)->where('translations.locale', $locale)
                        ->orderBy('translations.' . $col, $type);
                } else {
                    $query->orderBy($field, $type);
                }

                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                foreach ($query->get() as $org) {
                    $customFields = [];

                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    => $setting->key,
                            'value'  => $setting->value
                        ];
                    }

                    $results[] = [
                        'id'                => $org->id,
                        'name'              => $org->name,
                        'description'       => $org->descript,
                        'locale'            => $locale,
                        'uri'               => $org->uri,
                        'type'              => $org->type,
                        'logo'              => $this->getImageData($org->logo_data, $org->logo_mime_type),
                        'activity_info'     => $org->activity_info,
                        'contacts'          => $org->contacts,
                        'parent_org_id'     => $org->parent_org_id,
                        'approved'          => $org->approved,
                        'active'            => $org->active,
                        'custom_fields'     => $customFields,
                        'datasets_count'    => $org->dataSet()->count(),
                        'followers_count'   => $org->userFollow()->count(),
                        'created_at'        => $org->created_at->toDateTimeString(),
                        'updated_at'        => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'        => $org->created_by,
                        'updated_by'        => $org->updated_by,
                    ];
                }

                return $this->successResponse(['organisations' => $results, 'total_records' => $count], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_org_fail'), $validator->errors()->messages());
    }

    /**
     * List organisations by criteria
     *
     * @param array criteria -optional
     * @param string criteria[locale] - optional
     * @param integer criteria[id] - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[approved] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list with organisations or error
     */
    public function getUserOrganisations(Request $request)
    {
        $results = [];
        $criteria = [];
        $count = 0;
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'id'           => 'nullable|integer|exists:users,id',
                'locale'       => 'nullable|string|max:5',
                'active'       => 'nullable|bool',
                'approved'     => 'nullable|bool',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_VIEW
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $columns = [
                'id',
                'name',
                'type',
                'descript',
                'contacts',
                'activity_info',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            if (isset($request->criteria['order']['field'])) {
                if (!in_array($request->criteria['order']['field'], $columns)) {
                    return $this->errorResponse(__('custom.invalid_sort_field'));
                }
            }

            $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
            $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

            $userId = \Auth::user()->id;

            if (!empty($criteria['id'])) {
                $userId = $criteria['id'];
            }

            $transFields = ['name', 'descript'];

            $transCols = Organisation::getTransFields();

            try {
                $query = Organisation::with('UserToOrgRole')->with('dataSet')
                    ->where('organisations.type', '!=', Organisation::TYPE_GROUP);
                $query->whereIn('organisations.type', array_flip(Organisation::getPublicTypes()));

                if (isset($criteria['active'])) {
                    $query->where('active', $criteria['active']);
                }

                if (isset($criteria['approved'])) {
                    $query->where('approved', $criteria['approved']);
                }

                $query->whereHas('UserToOrgRole', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                });

                $count = $query->count();

                if (in_array($field, $transFields)) {
                    $col = $transCols[$field];

                    $query->select('translations.group_id', 'translations.label', 'translations.text', 'organisations.*')
                        ->leftJoin('translations', 'translations.group_id', '=', 'organisations.' . $field)
                        ->where('translations.locale', $locale)
                        ->orderBy('translations.' . $col, $type);
                } else {
                    $query->orderBy($field, $type);
                }

                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                foreach ($query->get() as $org) {
                    $customFields = [];

                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    => $setting->key,
                            'value'  => $setting->value
                        ];
                    }

                    $results[] = [
                        'id'              => $org->id,
                        'name'            => $org->name,
                        'description'     => $org->descript,
                        'locale'          => $locale,
                        'uri'             => $org->uri,
                        'type'            => $org->type,
                        'logo'            => $this->getImageData($org->logo_data, $org->logo_mime_type),
                        'activity_info'   => $org->activity_info,
                        'contacts'        => $org->contacts,
                        'parent_org_id'   => $org->parent_org_id,
                        'approved'        => $org->approved,
                        'active'          => $org->active,
                        'custom_fields'   => $customFields,
                        'datasets_count'  => $org->dataSet()->count(),
                        'followers_count' => $org->userFollow()->count(),
                        'created_at'      => $org->created_at->toDateTimeString(),
                        'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'      => $org->created_by,
                        'updated_by'      => $org->updated_by,
                    ];
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_msg'       => 'Got user organisations',
                ];

                Module::add($logData);

                return $this->successResponse(['organisations' => $results, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_org_fail'), $validator->errors()->messages());
    }

    /**
     * Get organisation details
     *
     * @param string locale - optional
     * @param integer org_id - required without org_uri
     * @param string org_uri - required without org_id
     *
     * @return json with organisation details or error
     */
    public function getOrganisationDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'   => 'required_without:org_uri|nullable|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'org_uri'  => 'required_without:org_id|nullable|string|exists:organisations,uri,deleted_at,NULL|max:191',
            'locale'   => 'nullable|string|max:5',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            try {
                if (isset($post['org_id'])) {
                    $orgKey = 'id';
                    $orgVal = $post['org_id'];
                } else {
                    $orgKey = 'uri';
                    $orgVal = $post['org_uri'];
                }
                $org = Organisation::with('dataSet')->where($orgKey, $orgVal)
                    ->where('type', '!=', Organisation::TYPE_GROUP)
                    ->first();

                if ($org) {
                    $customFields = [];

                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    => $setting->key,
                            'value'  => $setting->value
                        ];
                    }

                    $result = [
                        'id'              => $org->id,
                        'name'            => $org->name,
                        'description'     => $org->descript,
                        'locale'          => $locale,
                        'uri'             => $org->uri,
                        'type'            => $org->type,
                        'logo'            => $this->getImageData($org->logo_data, $org->logo_mime_type),
                        'activity_info'   => $org->activity_info,
                        'contacts'        => $org->contacts,
                        'parent_org_id'   => $org->parent_org_id,
                        'active'          => $org->active,
                        'approved'        => $org->approved,
                        'custom_fields'   => $customFields,
                        'followers_count' => $org->userFollow()->count(),
                        'created_at'      => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                        'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'      => $org->created_by,
                        'updated_by'      => $org->updated_by,
                    ];

                    $ids = [];
                    $relations = $org->dataSet()->get();
                    foreach ($relations as $v) {
                        $ids[] = $v->id;
                    }
                    $count = DataSet::whereIn('id', $ids)
                        ->where('status', DataSet::STATUS_PUBLISHED)
                        ->count();
                    $result['datasets_count'] = $count;

                    return $this->successResponse($result);
                }
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_org_fail'), $validator->errors()->messages());
    }

    /**
     * Get organisation members
     *
     * @param integer org_id - required
     * @param array role_ids - optional
     * @param string keywords - optional
     * @param boolean for_approval - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json with organisaion member details or error
     */
    public function getMembers(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'            => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'role_ids'          => 'nullable|array',
            'keywords'          => 'nullable|string|max:191',
            'for_approval'      => 'nullable|bool',
            'records_per_page'  => 'nullable|int|digits_between:1,10',
            'page_number'       => 'nullable|int|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            try {
                $query = User::select('id', 'username', 'firstname', 'lastname', 'email')
                    ->with(['UserToOrgRole' => function ($q) use($post) {
                        $q->where('org_id', $post['org_id']);
                    }]);

                if (isset($post['for_approval'])) {
                    $query->where('approved', $post['for_approval'] ? false : true);
                }

                if (isset($post['keywords'])) {
                    $ids = User::search($post['keywords'])->get()->pluck('id');
                    $query = $query->whereIn('id', $ids);
                }

                $query->whereHas('UserToOrgRole', function($q) use ($post) {
                    $q->where('org_id', $post['org_id']);

                    if (isset($post['role_ids'])) {
                        if (is_array($post['role_ids'])) {
                            $q->whereIn('role_id', $post['role_ids']);
                        } else {
                            $q->where('role_id', $post['role_ids']);
                        }
                    }
                });

                $count = $query->count();
                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                $members = [];

                foreach ($query->get()->toArray() as $member) {
                    $roles = [];

                    if (!empty($member['user_to_org_role'])) {
                        foreach ($member['user_to_org_role'] as $role) {
                            $roles[] = $role['role_id'];
                        }
                    }

                    $members[] = [
                        'id'        => $member['id'],
                        'username'  => $member['username'],
                        'firstname' => $member['firstname'],
                        'lastname'  => $member['lastname'],
                        'email'     => $member['email'],
                        'role_id'   => $roles,
                    ];
                }

                return $this->successResponse([
                    'members'       => $members,
                    'total_records' => $count,
                ], true);
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_members_fail'), $validator->errors()->messages());
    }

    /**
     * Add organisation member
     *
     * @param integer org_id - required
     * @param integer user_id - required
     *
     * @return json success or error
     */
    public function addMember(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'        => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'user_id'       => 'required|int|exists:users,id,deleted_at,NULL|digits_between:1,10',
            'role_id'       => 'nullable|array',
            'role_id.*'     => 'required|int|exists:roles,id|digits_between:1,10'
        ]);

        $organisation = Organisation::where('id', $post['org_id'])->first();

        if ($organisation) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'created_by' => $organisation->created_by,
                    'org_id'     => $organisation->id
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }
        }

        if (!$validator->fails()) {
            try {
                if (isset($post['role_id']) || isset($post['org_id'])) {
                    foreach ($post['role_id'] as $role) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $post['user_id'];
                        $userToOrgRole->org_id = !empty($post['org_id']) ? $post['org_id'] : null;
                        $userToOrgRole->role_id = $role;

                        $userToOrgRole->save();
                    }
                }

                $username = User::where('id', $post['user_id'])->first();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_ADD_MEMBER,
                    'action_object'    => $post['org_id'],
                    'action_msg'       => 'Added member '. $username->username . ' (' . $post['user_id'].') to organisation ',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_member_fail'), $validator->errors()->messages());
    }

    /**
     * Delete organisation member
     *
     * @param integer org_id - required
     * @param integer user_id - required
     *
     * @return json success or error
     */
    public function delMember(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'        => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'user_id'       => 'required|int|exists:users,id,deleted_at,NULL|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $organisation = Organisation::where('id', $post['org_id'])->first();

            if ($organisation) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_ALL,
                    [
                        'org_id'       => $organisation->id
                    ],
                    [
                        'created_by' => $organisation->created_by,
                        'org_id'     => $organisation->id
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }
            }

            try {
                UserToOrgRole::where([
                    'org_id'    => $post['org_id'],
                    'user_id'   => $post['user_id'],
                ])->delete();

               $username = User::where('id', $post['user_id'])->first();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_DEL_MEMBER,
                    'action_object'    => $post['org_id'],
                    'action_msg'       => 'Deleted member '. $username->username . ' (' . $post['user_id'].') from organisation ',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_member_fail'), $validator->errors()->messages());
    }

    /**
     * Edit organisation member role
     *
     * @param integer org_id - required
     * @param integer user_id - required
     * @param integer role_id - required
     *
     * @return json success or error
     */
    public function editMember(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'        => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'user_id'       => 'required|int|exists:users,id,deleted_at,NULL|digits_between:1,10',
            'role_id'       => 'nullable|array',
            'role_id.*'     => 'int|exists:roles,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $organisation = Organisation::where('id', $post['org_id'])->first();

            if ($organisation) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id'       => $organisation->id
                    ],
                    [
                        'created_by' => $organisation->created_by,
                        'org_id'     => $organisation->id
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }
            }

            try {
                if (isset($post['role_id']) || isset($post['org_id'])) {
                    UserToOrgRole::where('org_id', $post['org_id'])->where('user_id', $post['user_id'])->delete();
                    foreach ($post['role_id'] as $role) {
                        $user = UserToOrgRole::updateOrCreate(
                            [
                                'user_id' => $post['user_id'],
                                'org_id'  => $post['org_id'],
                                'role_id' => isset($post['role_id']) ? $role : null
                            ])
                              ->where('org_id', $post['org_id'])
                              ->where('user_id', $post['user_id']);
                    }
                }

                $username = User::where('id', $post['user_id'])->first();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ORGANISATIONS),
                    'action'           => ActionsHistory::TYPE_EDIT_MEMBER,
                    'action_object'    => $post['org_id'],
                    'action_msg'       => 'Edited member '. $username->username . ' (' . $post['user_id'].') for organisation ',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_member_fail'), $validator->errors()->messages());
    }

    /************************   MANAGE GROUPS   ************************/
    /**
     * Add new Group
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[locale] - required
     * @param string data[name] - required
     * @param string data[description] - optional
     * @param string data[uri] - optional
     * @param string data[logo] - optional
     * @param string data[logo_filename] - optional
     * @param string data[logo_mimetype] - optional
     * @param string data[logo_data] - optional
     * @param string data[activity_info] - optional
     * @param integer data[active] - optional
     * @param array custom_fields - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json response with group id or error
     */
    public function addGroup(Request $request)
    {
        $post = $request->offsetGet('data');

        $validator = \Validator::make($post, [
            'locale'                => 'nullable|string|max:5',
            'name'                  => 'required_with:locale|max:191',
            'name.bg'               => 'required_without:locale|string|max:191',
            'description'           => 'nullable|max:8000',
            'uri'                   => 'nullable|string|unique:organisations,uri|max:191',
            'logo'                  => 'nullable|string|max:191',
            'logo_filename'         => 'nullable|string|max:191',
            'logo_mimetype'         => 'nullable|string|max:191',
            'logo_data'             => 'nullable|string|max:16777215 ',
            'activity_info'         => 'nullable|string|max:8000',
            'active'                => 'nullable|bool',
            'custom_fields.*.label' => 'nullable|max:191',
            'custom_fields.*.value' => 'nullable|max:8000',
        ]);

        $newGroup = new Organisation;
        $imageError = false;

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        if (!empty($post['logo'])) {
            try {
                $img = \Image::make($post['logo']);
                $newGroup->logo_file_name = empty($post['logo_filename'])
                    ? basename($post['logo'])
                    : $post['logo_filename'];
                $newGroup->logo_mime_type = $img->mime();

                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                $img->save($path);
                $newGroup->logo_data = file_get_contents($path);

                fclose($temp);
            } catch (\Exception $ex) {
                $imageError = true;
                $validator->errors()->add('logo', $this->getImageTypeError());
            }
        }

        if (isset($post['logo_filename']) && isset ($post['logo_mimetype']) && isset($post['logo_data'])) {
            $newGroup->logo_file_name = $post['logo_filename'];
            $newGroup->logo_mime_type = $post['logo_mimetype'];
            $newGroup->logo_data = $post['logo_data'];
        }

        if (isset($newGroup->logo_data)) {
            if (!$this->checkImageSize($newGroup->logo_data)) {
                $imageError = true;
                $validator->errors()->add('logo', $this->getImageSizeError());
            }
        }

        $errors = $validator->errors()->messages();

        if (
            isset($post['migrated_data'])
            && Auth::user()->username == 'migrate_data'
        ) {
            if (!empty($post['created_by'])) {
                $newGroup->created_by = $post['created_by'];
            }
        }

        if (!$validator->fails()) {

            if (!isset($newGroup->logo_data) || !$imageError) {
                $locale = isset($post['locale']) ? $post['locale'] : null;

                DB::beginTransaction();

                try {
                    $newGroup->name = $this->trans($post['locale'], $post['name']);
                    $newGroup->descript = !empty($post['description']) ? $this->trans($post['locale'], $post['description']) : null;
                    $newGroup->uri = !empty($post['uri'])
                        ? $post['uri']
                        : $this->generateOrgUri();

                    $newGroup->type = Organisation::TYPE_GROUP;
                    $newGroup->active = Organisation::ACTIVE_FALSE;
                    $newGroup->approved = Organisation::APPROVED_FALSE;
                    $newGroup->parent_org_id = null;

                    if (!empty($post['custom_fields'])) {
                        foreach ($post['custom_fields'] as $fieldSet) {
                            if (is_array($fieldSet['value']) && is_array($fieldSet['label'])) {
                                if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                                    $customFields[] = [
                                        'value' => $fieldSet['value'],
                                        'label' => $fieldSet['label'],
                                    ];
                                }
                            } elseif (!empty($fieldSet['label'])) {
                                $customFields[] = [
                                    'value' => [
                                        $locale => $fieldSet['value']
                                    ],
                                    'label' =>[
                                        $locale => $fieldSet['label']
                                    ]
                                ];
                            }
                        }
                    }

                    $newGroup->save();

                    if ($newGroup) {
                        $role = Role::getGroupAdminRole();
                        if (!isset($role)) {
                            return $this->errorResponse(__('custom.add_role_fail'));
                        }

                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->org_id = $newGroup->id;
                        $userToOrgRole->user_id = \Auth::user()->id;
                        $userToOrgRole->role_id = $role->id;

                        $userToOrgRole->save();

                        if (!empty($customFields)) {
                            if (!$this->checkAndCreateCustomSettings($customFields, $newGroup->id)) {
                                DB::rollback();

                                return $this->errorResponse(__('custom.add_group_fail'));
                            }
                        }

                        DB::commit();

                        $logData = [
                            'module_name'      => Module::getModuleName(Module::GROUPS),
                            'action'           => ActionsHistory::TYPE_ADD,
                            'action_object'    => $newGroup->id,
                            'action_msg'       => 'Added group',
                        ];

                        Module::add($logData);

                        return $this->successResponse(['id' => $newGroup->id], true);
                    }
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.add_group_fail'), $errors);
    }

    /**
     * Edit Group
     *
     * @param string api_key - required
     * @param integer group_id - required
     * @param array data - required
     * @param string data[locale] - required if name or description is present
     * @param string data[name] - optional
     * @param string data[description] - optional
     * @param string data[uri] - optional
     * @param string data[logo] - optional
     * @param string data[logo_filename] - optional
     * @param string data[logo_mimetype] - optional
     * @param string data[logo_data] - optional
     * @param string data[activity_info] - optional
     * @param integer data[active] - optional
     * @param array custom_fields - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function editGroup(Request $request)
    {
        $data = $request->get('data', []);
        $id = $request->get('group_id');
        $data['group_id'] = $request->group_id ? $request->group_id : null;

        $validator = \Validator::make($data,[
            'group_id'              => 'required|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'locale'                => 'nullable|string|max:5',
            'name'                  => 'required_with:locale|max:191',
            'name.bg'               => 'required_without:locale|string|max:191',
            'description'           => 'nullable|max:8000',
            'uri'                   => 'nullable|string|max:191',
            'logo'                  => 'nullable|string|max:191',
            'logo_filename'         => 'nullable|string|max:191',
            'logo_mimetype'         => 'nullable|string|max:191',
            'logo_data'             => 'nullable|string|max:191',
            'custom_fields.*.label' => 'nullable|max:191',
            'custom_fields.*.value' => 'nullable|max:8000',
        ]);

        $group = Organisation::find($id);
        $newGroupData = [];
        $imageError = false;

        if ($group) {
            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_EDIT,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'      => [$group->id]
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }
        }

        if (!empty($data['logo'])) {
            try {
                $img = \Image::make($data['logo']);
                $group->logo_file_name = empty($data['logo_filename'])
                    ? basename($data['logo'])
                    : $data['logo_filename'];
                $group->logo_mime_type = $img->mime();

                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                $img->save($path);
                $group->logo_data = file_get_contents($path);

                fclose($temp);
            } catch (\Exception $ex) {
                $imageError = true;
                $validator->errors()->add('logo', $this->getImageTypeError());
            }
        }

        if (isset($data['logo_file_name']) && isset($data['logo_mime_type']) && isset($data['logo_data'])) {
            $newGroupData['logo_file_name'] = $data['logo_file_name'];
            $newGroupData['logo_mime_type'] = $data['logo_mime_type'];
            $newGroupData['logo_data'] = $data['logo_data'];
        }

        if (isset($group->logo_data)) {
            if (!$this->checkImageSize($group->logo_data)) {
                $validator->errors()->add('logo', $this->getImageSizeError());
            }
        }

        $validator->after(function ($validator) use ($data) {
            if (!empty($data['uri'])) {
                if (Organisation::where('uri', $data['uri'])->where('id', '!=', $data['group_id'])->value('name')) {
                    $validator->errors()->add('uri', __('custom.taken_uri'));
                }
            }
        });

        $errors = $validator->errors()->messages();

        if ($group->type != Organisation::TYPE_GROUP) {
            return $this->errorResponse(__('custom.no_group_found'));
        }

        if (!$validator->fails()) {

            if (!empty($data['uri'])) {
                $newGroupData['uri'] = $data['uri'];
            }

            if (!empty($data['custom_fields'])) {
                $customFields = $data['custom_fields'];
            }

            if (!empty($newGroupData)) {
                $newGroupData['updated_by'] = \Auth::id();

                if (!isset($newGroupData['logo_data']) && !$imageError) {
                    DB::beginTransaction();

                    try {
                        if (!empty($data['name'])) {
                            $newGroupData['name'] = $this->trans($data['locale'], $data['name']);
                        }

                        if (!empty($data['description'])) {
                            $newGroupData['descript'] = $this->trans($data['locale'], $data['description']);
                        }

                        foreach ($newGroupData as $prop => $val) {
                            $group->$prop = $val;
                        }

                        $group->save();

                        if (!empty($customFields)) {
                            if (!$this->checkAndCreateCustomSettings($customFields, $group->id)) {
                                DB::rollback();

                                return $this->errorResponse(__('custom.add_group_fail'));
                            }
                        }

                        DB::commit();

                        $logData = [
                            'module_name'      => Module::getModuleName(Module::GROUPS),
                            'action'           => ActionsHistory::TYPE_MOD,
                            'action_object'    => $group->id,
                            'action_msg'       => 'Edited group',
                        ];

                        Module::add($logData);

                        return $this->successResponse();
                    } catch (QueryException $e) {
                        Log::error($e->getMessage());

                        DB::rollback();
                    }
                }
            }
        }

        return $this->errorResponse(__('custom.edit_group_fail'), $errors);
    }

    /**
     * Delete existing group record
     *
     * @param string api_key - required
     * @param integer group_id - required
     *
     * @return json with success or error
     */
    public function deleteGroup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'group_id' => 'required|int|digits_between:1,10',
        ]);

        $group = Organisation::find($request->group_id);

        if ($group) {
            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_ALL,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'      => [$group->id]
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }
        }

        if (empty($group) || $group->type != Organisation::TYPE_GROUP) {
            return $this->errorResponse(__('custom.no_group_found'));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'      => $group->id
            ],
            [
                'created_by'    => $group->created_by,
                'group_ids'     => [$group->id]
            ]
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        if (!$validator->fails()) {
            try {
                $group->delete();
                $group->deleted_by = \Auth::id();
                $group->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::GROUPS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $group->id,
                    'action_msg'       => 'Deleted group',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_group_fail'), $validator->errors()->messages());
    }

    /**
     * List all listGroups
     *
     * @param integer criteria - optional
     * @param array criteria[group_ids] - optional
     * @param integer criteria[dataset_id] - optional
     * @param integer criteria[user_id] - optional
     * @param string criteria[keywords] - optional
     * @param string criteria[locale] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json with list of groups
     */
    public function listGroups(Request $request)
    {
        $post = $request->all();
        $criteria = !empty($post['criteria']) ? $post['criteria'] : false;
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
        $locale = !empty($post['criteria']['locale'])
            ? $post['criteria']['locale']
            : \LaravelLocalization::getCurrentLocale();

        $validator = \Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'group_ids'    => 'nullable|array',
                'locale'       => 'nullable|string|max:5',
                'dataset_id'   => 'nullable|int|digits_between:1,10',
                'user_id'      => 'nullable|int|exists:users,id|digits_between:1,10',
                'keywords'     => 'nullable|string|max:191',
                'order'        => 'nullable|array'
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $groups = [];
                $result = [];

                $query = Organisation::where('type', Organisation::TYPE_GROUP);

                if (!empty($criteria['group_ids'])) {
                    $query->whereIn('organisations.id', $criteria['group_ids']);
                }

                if (!empty($criteria['keywords'])) {
                    $ids = Organisation::search($criteria['keywords'])->get()->pluck('id');
                    $query->whereIn('organisations.id', $ids);
                }

                if (!empty($criteria['dataset_id'])) {
                    $query->whereHas('dataSetGroup', function($q) use ($criteria) {
                        $q->where('data_set_id', $criteria['dataset_id']);
                    });
                }

                if (!empty($criteria['user_id'])) {
                    $query->whereHas('userToOrgRole', function($q) use ($criteria) {
                        $q->where('user_to_org_role.user_id', $criteria['user_id']);
                    });
                }

                $orderColumns = [
                    'id',
                    'type',
                    'name',
                    'descript',
                    'activity_info',
                    'contacts',
                    'active',
                    'approved',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                ];

                if (isset($criteria['order']['field'])) {
                    if (!in_array($criteria['order']['field'], $orderColumns)) {
                        return $this->errorResponse(__('custom.invalid_sort_field'));
                    }
                }

                $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
                $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

                $transFields = ['name', 'descript'];

                $transCols = Organisation::getTransFields();

                if (in_array($field, $transFields)) {

                    $col = $transCols[$field];
                    $query->select('translations.label', 'translations.group_id', 'translations.text', 'organisations.*')
                        ->leftJoin('translations', 'translations.group_id', '=', 'organisations.' . $field)->where('translations.locale', $locale)
                        ->orderBy('translations.' . $col, $type);
                } else {
                    $query->orderBy($field, $type);
                }

                $count = $query->count();

                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                $groups = $query->get();

                if (!empty($groups)) {
                    foreach ($groups as $group) {
                        $customFields = [];

                        //get custom fields
                        foreach ($group->customSetting()->get() as $setting) {
                            $customFields[] = [
                                'key'    => $setting->key,
                                'value'  => $setting->value
                            ];
                        }

                        $result[] = [
                            'id'                => $group->id,
                            'name'              => $group->name,
                            'description'       => $group->descript,
                            'locale'            => $locale,
                            'uri'               => $group->uri,
                            'logo'              => $this->getImageData($group->logo_data, $group->logo_mime_type, 'group'),
                            'custom_fields'     => $customFields,
                            'datasets_count'    => $group->dataSet()->count(),
                            'followers_count'   => $group->userFollow()->count(),
                            'created_at'        => $group->created,
                            'updated_at'        => $group->updated_at,
                            'created_by'        => $group->created_by,
                            'updated_by'        => $group->updated_by,
                        ];
                    }

                    $transFields = ['name', 'description'];

                    return $this->successResponse(['groups' => $result, 'total_records' => $count], true);
                }

                return $this->errorResponse(__('custom.no_groups_found'), $validator->errors()->messages());
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_groups_fail'), $validator->errors()->messages());
    }

    /**
     * Get group details
     *
     * @param integer group_id - required
     * @param string locale - optional
     *
     * @return json with success or error
     */
    public function getGroupDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'group_id'   => 'required_without:group_uri|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'group_uri'  => 'required_without:group_id|nullable|string|exists:organisations,uri,deleted_at,NULL|max:191',
            'locale'     => 'nullable|string|max:5',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            try {
                if (!empty($post['group_id'])) {
                    $group = Organisation::where('id', $post['group_id'])->first();
                } else {
                    $group = Organisation::where('uri', $post['group_uri'])->first();
                }

                $customFields = [];

                if ($group) {
                    if ($group->type != Organisation::TYPE_GROUP) {
                        return $this->errorResponse(__('custom.no_group_found'));
                    }

                    foreach ($group->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    => $setting->key,
                            'value'  => $setting->value
                        ];
                    }

                    $result = [
                        'id'              => $group->id,
                        'name'            => $group->name,
                        'description'     => $group->descript,
                        'locale'          => $locale,
                        'uri'             => $group->uri,
                        'logo'            => $this->getImageData($group->logo_data, $group->logo_mime_type, 'group'),
                        'custom_fields'   => $customFields,
                        'followers_count' => $group->userFollow()->count(),
                    ];

                    $ids = [];
                    $relations = $group->dataSetGroup()->get();
                    foreach ($relations as $v) {
                        $ids[] = $v->data_set_id;
                    }
                    $count = DataSet::whereIn('id', $ids)
                        ->where('status', DataSet::STATUS_PUBLISHED)
                        ->count();
                    $result['datasets_count'] = $count;

                    return $this->successResponse($result);
                }
            } catch (QueryException $e) {
                return $this->errorResponse($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_group_fail'), $validator->errors()->messages());
    }

    /**
     * Get unique uri for organisation (group) using the name
     *
     * @param string $name
     * @return string $uri
     */
    public function generateOrgUri()
    {
        return \Uuid::generate(4)->string;
    }

    /*
     * Check and create custom settings
     *
     * @param array $customFields
     * @param int   $orgId
     *
     * @return true if successful, false otherwise
     */
    public function checkAndCreateCustomSettings($customFields, $orgId)
    {
        if (!empty($orgId)) {
            try {
                DB::beginTransaction();

                CustomSetting::where('org_id', $orgId)->delete();

                foreach ($customFields as $field) {
                    if (!empty($field['label']) && !empty($field['value'])) {
                        foreach ($field['label'] as $locale => $label) {
                            if (
                                (empty($field['label'][$locale]) && !empty($field['value'][$locale]))
                                || (!empty($field['label'][$locale]) && empty($field['value'][$locale]))

                            ) {
                                DB::rollback();

                                return false;
                            }
                        }

                        $saveField = new CustomSetting;
                        $saveField->org_id = $orgId;
                        $saveField->created_by = \Auth::user()->id;
                        $saveField->key = $this->trans($empty, $field['label']);
                        $saveField->value = $this->trans($empty, $field['value']);

                        $saveField->save();
                    } else {
                        DB::rollback();

                        return false;
                    }
                }

                DB::commit();

                return true;
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return false;
    }

    /**
     * List organisation types
     *
     * @param string locale - optional
     * @return json list with organisation types or error
     */
    public function listOrganisationTypes(Request $request)
    {
        $results = [];

        $post = $request->all();

        $validator = \Validator::make($post, [
            'locale' => 'nullable|string|max:5|exists:locale,locale,active,1',
        ]);

        if (!$validator->fails()) {
            try {
                if (isset($post['locale'])) {
                    $locale = $post['locale'];
                } else {
                    $locale = \LaravelLocalization::getCurrentLocale();
                }

                $orgTypes = Organisation::getPublicTypes();
                krsort($orgTypes);

                foreach ($orgTypes as $typeId => $typeName) {
                    $results[] = [
                        'id'     => $typeId,
                        'name'   => ultrans($typeName, 2, [], $locale),
                        'locale' => $locale,
                    ];
                }

                return $this->successResponse(['types' => $results], true);

            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_org_types_fail'), $validator->errors()->messages());
    }

    /**
     * Lists the count of the datasets per organisation
     *
     * @param array criteria - optional
     * @param array criteria[dataset_criteria] - optional
     * @param array criteria[dataset_criteria][user_ids] - optional
     * @param array criteria[dataset_criteria][org_ids] - optional
     * @param array criteria[dataset_criteria][group_ids] - optional
     * @param array criteria[dataset_criteria][category_ids] - optional
     * @param array criteria[dataset_criteria][tag_ids] - optional
     * @param array criteria[dataset_criteria][formats] - optional
     * @param array criteria[dataset_criteria][terms_of_use_ids] - optional
     * @param boolean criteria[dataset_criteria][reported] - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[type] - optional
     * @param string criteria[locale] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataOrganisations(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria' => 'nullable|array'
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'dataset_criteria'  => 'nullable|array',
                'dataset_ids'       => 'nullable|array',
                'dataset_ids.*'     => 'int|exists:data_sets,id|digits_between:1,10',
                'type'              => 'nullable|int|in:'. implode(',', array_keys(Organisation::getPublicTypes())),
                'locale'            => 'nullable|string|max:5|exists:locale,locale,active,1',
                'records_limit'     => 'nullable|int|digits_between:1,10|min:1',
            ]);
        }

        if (!$validator->fails()) {
            $dsCriteria = isset($criteria['dataset_criteria']) ? $criteria['dataset_criteria'] : [];
            $validator = \Validator::make($dsCriteria, [
                'user_ids'            => 'nullable|array',
                'user_ids.*'          => 'int|digits_between:1,10|exists:users,id',
                'org_ids'             => 'nullable|array',
                'org_ids.*'           => 'int|digits_between:1,10|exists:organisations,id',
                'group_ids'           => 'nullable|array',
                'group_ids.*'         => 'int|digits_between:1,10|exists:organisations,id,type,'. Organisation::TYPE_GROUP,
                'category_ids'        => 'nullable|array',
                'category_ids.*'      => 'int|digits_between:1,10|exists:categories,id,parent_id,NULL',
                'tag_ids'             => 'nullable|array',
                'tag_ids.*'           => 'int|digits_between:1,10|exists:tags,id',
                'terms_of_use_ids'    => 'nullable|array',
                'terms_of_use_ids.*'  => 'int|digits_between:1,10|exists:terms_of_use,id',
                'formats'             => 'nullable|array|min:1',
                'formats.*'           => 'string|in:'. implode(',', Resource::getFormats()),
                'reported'            => 'nullable|boolean',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($criteria['locale']) ? $criteria['locale'] : \LaravelLocalization::getCurrentLocale();

                $data = Organisation::join('data_sets', 'organisations.id', '=', 'org_id');
                $data->select('organisations.id', 'organisations.name', DB::raw('count(distinct data_sets.id, data_sets.org_id) as total'));

                $data->where('organisations.active', 1);
                $data->where('organisations.approved', 1);
                if (isset($criteria['type'])) {
                    $data->where('organisations.type', $criteria['type']);
                } else {
                    $data->where('organisations.type', '!=', Organisation::TYPE_GROUP);
                }
                $data->where('data_sets.status', DataSet::STATUS_PUBLISHED);
                $data->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC);
                $data->whereNull('data_sets.deleted_at');

                if (!empty($dsCriteria['user_ids'])) {
                    $data->whereIn('data_sets.created_by', $dsCriteria['user_ids']);
                }
                if (!empty($dsCriteria['org_ids'])) {
                    $data->whereIn('org_id', $dsCriteria['org_ids']);
                }
                if (!empty($dsCriteria['group_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_groups')->select('data_set_id')->distinct()->whereIn('group_id', $dsCriteria['group_ids'])
                    );
                }
                if (!empty($dsCriteria['category_ids'])) {
                    $data->whereIn('category_id', $dsCriteria['category_ids']);
                }
                if (!empty($dsCriteria['tag_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_tags')->select('data_set_id')->distinct()->whereIn('tag_id', $dsCriteria['tag_ids'])
                    );
                }
                if (!empty($dsCriteria['terms_of_use_ids'])) {
                    $data->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                }
                if (!empty($dsCriteria['formats'])) {
                    $fileFormats = [];
                    foreach ($dsCriteria['formats'] as $format) {
                        $fileFormats[] = Resource::getFormatsCode($format);
                    }
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)
                    );
                }
                if (isset($dsCriteria['reported']) && $dsCriteria['reported']) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->where('is_reported', Resource::REPORTED_TRUE)
                    );
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_sets.id', $criteria['dataset_ids']);
                }

                $data->groupBy(['organisations.id', 'organisations.name'])->orderBy('total', 'desc');

                if (!empty($criteria['records_limit'])) {
                    $data->take($criteria['records_limit']);
                }
                $data = $data->get();

                $results = [];
                if (!empty($data)) {
                    foreach ($data as $item) {
                        $results[] = [
                            'id'             => $item->id,
                            'name'           => $item->name,
                            'locale'         => $locale,
                            'datasets_count' => $item->total,
                        ];
                    }
                }

                return $this->successResponse(['organisations' => $results], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_organisations_fail'), $validator->errors()->messages());
    }

    /**
     * Lists the count of the datasets per group
     *
     * @param array criteria - optional
     * @param array criteria[dataset_criteria] - optional
     * @param array criteria[dataset_criteria][user_ids] - optional
     * @param array criteria[dataset_criteria][org_ids] - optional
     * @param array criteria[dataset_criteria][group_ids] - optional
     * @param array criteria[dataset_criteria][category_ids] - optional
     * @param array criteria[dataset_criteria][tag_ids] - optional
     * @param array criteria[dataset_criteria][formats] - optional
     * @param array criteria[dataset_criteria][terms_of_use_ids] - optional
     * @param boolean criteria[dataset_criteria][reported] - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[locale] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataGroups(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria' => 'nullable|array'
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'dataset_criteria'  => 'nullable|array',
                'dataset_ids'       => 'nullable|array',
                'dataset_ids.*'     => 'int|exists:data_sets,id|digits_between:1,10',
                'locale'            => 'nullable|string|max:5|exists:locale,locale,active,1',
                'records_limit'     => 'nullable|int|digits_between:1,10|min:1',
            ]);
        }

        if (!$validator->fails()) {
            $dsCriteria = isset($criteria['dataset_criteria']) ? $criteria['dataset_criteria'] : [];
            $validator = \Validator::make($dsCriteria, [
                'user_ids'            => 'nullable|array',
                'user_ids.*'          => 'int|digits_between:1,10|exists:users,id',
                'org_ids'             => 'nullable|array',
                'org_ids.*'           => 'int|digits_between:1,10|exists:organisations,id',
                'group_ids'           => 'nullable|array',
                'group_ids.*'         => 'int|digits_between:1,10|exists:organisations,id,type,'. Organisation::TYPE_GROUP,
                'category_ids'        => 'nullable|array',
                'category_ids.*'      => 'int|digits_between:1,10|exists:categories,id,parent_id,NULL',
                'tag_ids'             => 'nullable|array',
                'tag_ids.*'           => 'int|digits_between:1,10|exists:tags,id',
                'terms_of_use_ids'    => 'nullable|array',
                'terms_of_use_ids.*'  => 'int|digits_between:1,10|exists:terms_of_use,id',
                'formats'             => 'nullable|array|min:1',
                'formats.*'           => 'string|in:'. implode(',', Resource::getFormats()),
                'reported'            => 'nullable|boolean',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($criteria['locale']) ? $criteria['locale'] : \LaravelLocalization::getCurrentLocale();

                $data = Organisation::join('data_set_groups', 'organisations.id', '=', 'group_id')->join('data_sets', 'data_set_id', '=', 'data_sets.id');
                $data->select('organisations.id', 'organisations.name', DB::raw('count(distinct data_set_id, group_id) as total'));

                $data->where('organisations.type', Organisation::TYPE_GROUP);
                $data->where('data_sets.status', DataSet::STATUS_PUBLISHED);
                $data->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC);
                $data->whereNull('data_sets.deleted_at');

                if (!empty($dsCriteria['user_ids'])) {
                    $data->whereIn('data_sets.created_by', $dsCriteria['user_ids']);
                }
                if (!empty($dsCriteria['org_ids'])) {
                    $data->whereIn('org_id', $dsCriteria['org_ids']);
                }
                if (!empty($dsCriteria['group_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_groups')->select('data_set_id')->distinct()->whereIn('group_id', $dsCriteria['group_ids'])
                    );
                }
                if (!empty($dsCriteria['category_ids'])) {
                    $data->whereIn('category_id', $dsCriteria['category_ids']);
                }
                if (!empty($dsCriteria['tag_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_tags')->select('data_set_id')->distinct()->whereIn('tag_id', $dsCriteria['tag_ids'])
                    );
                }
                if (!empty($dsCriteria['terms_of_use_ids'])) {
                    $data->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                }
                if (!empty($dsCriteria['formats'])) {
                    $fileFormats = [];
                    foreach ($dsCriteria['formats'] as $format) {
                        $fileFormats[] = Resource::getFormatsCode($format);
                    }
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)
                    );
                }
                if (isset($dsCriteria['reported']) && $dsCriteria['reported']) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->where('is_reported', Resource::REPORTED_TRUE)
                    );
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_sets.id', $criteria['dataset_ids']);
                }

                $data->groupBy(['organisations.id', 'organisations.name'])->orderBy('total', 'desc');

                if (!empty($criteria['records_limit'])) {
                    $data->take($criteria['records_limit']);
                }
                $data = $data->get();

                $results = [];
                if (!empty($data)) {
                    foreach ($data as $item) {
                        $results[] = [
                            'id'             => $item->id,
                            'name'           => $item->name,
                            'locale'         => $locale,
                            'datasets_count' => $item->total,
                        ];
                    }
                }

                return $this->successResponse(['groups' => $results], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_groups_fail'), $validator->errors()->messages());
    }

    /**
     * Get most active organisation
     *
     * @param string criteria[locale] - optional
     *
     * @return json response
     */
    public function getMostActiveOrganisation(Request $request)
    {
        $validator = \Validator::make($request->all(), ['locale' => 'nullable|string|max:5']);

        if (!$validator->fails()) {
            try {
                $result = DB::table('actions_history')
                    ->select('user_to_org_role.org_id', DB::raw('count(user_to_org_role.org_id) as count'))
                    ->leftJoin('user_to_org_role', 'user_to_org_role.user_id', '=', 'actions_history.user_id')
                    ->whereNotIn('user_to_org_role.org_id', Organisation::where('type', '=', Organisation::TYPE_GROUP)->get()->pluck('id'))
                    ->whereMonth('actions_history.occurrence', '=', Carbon::now()->subMonth()->month)
                    ->groupBy('user_to_org_role.org_id')
                    ->orderBy('count', 'desc')
                    ->limit(1)
                    ->first();

                if (!empty($result)) {
                    $org = Organisation::where('id', $result->org_id)->first();
                    $result->uri = $org->uri;
                    $result->name = $org->name;
                    $result->logo = $this->getImageData($org->logo_data, $org->logo_mime_type);
                } else {
                    $result = ['uri' => null, 'name' => __('custom.missing_most_active_org'), 'count' => 0];
                }

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_most_active_org_fail'), $validator->errors()->messages());
    }
}
