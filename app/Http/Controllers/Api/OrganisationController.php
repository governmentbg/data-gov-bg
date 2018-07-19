<?php

namespace App\Http\Controllers\Api;

use App\Locale;
use App\Organisation;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Intervention\Image\Exception\NotReadableException;

class OrganisationController extends ApiController
{
    /**
     * Add new organisation record
     *
     * @param object $request - POST request
     * @return json $response - response with status and org id if successfull
     */
    public function addOrganisation(Request $request)
    {
        $data = $request->get('data');
        $validator = \Validator::make($data, [
            'locale' => 'required|string',
            'name'   => 'required|string',
            'type'   => 'required|integer',
        ]);

        if (
            !$validator->fails()
            && in_array($data['type'], array_keys(Organisation::getPublicTypes()))
            && (
                isset($data['logo'])
                || (
                    isset($data['logo_filename'])
                    && isset($data['logo_mimetype'])
                    && isset($data['logo_data'])
                )
            )
        ) {
            $organisation = new Organisation;
            $locale = \LaravelLocalization::getCurrentLocale();

            $organisation->type = $data['type'];
            $organisation->name = [$locale => $data['name']];
            $organisation->descript = !empty($data['description'])
                    ? [$locale => $data['description']]
                    : null;

            $organisation->uri = !empty($request->data['uri'])
                ? $request->data['uri']
                : $this->generateOrgUri($request->data['name']);

            if (!empty($data['logo'])) {
                try {
                    $img = \Image::make($data['logo']);
                } catch (NotReadableException $ex) {}

                if (!empty($img)) {
                    $img->resize(300, 200);
                    $organisation->logo_file_name = basename($data['logo']);
                    $organisation->logo_mime_type = $img->mime();
                    $organisation->logo_data = $img->encode('data-url');
                }
            } else {
                $organisation->logo_file_name = $data['logo_filename'];
                $organisation->logo_mime_type = $data['logo_mimetype'];
                $organisation->logo_data = $data['logo_data'];
            }

            $organisation->activity_info = !empty($data['activity_info']) ? [$locale => $data['activity_info']] : null;
            $organisation->contacts = !empty($data['contacts']) ? [$locale => $data['contacts']] : null;
            $organisation->parent_org_id = !empty($data['parent_org_id']) ? $data['parent_org_id'] : null;
            $organisation->active = isset($data['active']) ? $data['active'] : 1;
            $organisation->approved = isset($data['approved']) && $data['type'] == Organisation::TYPE_CIVILIAN
                ? $data['approved']
                : 0;

            if (!empty($data['custom_fields'])) {
                $customFields = $data['custom_fields'];
            }

            DB::beginTransaction();

            try {
                $organisation->save();


                if (!empty($customFields)) {
                    if (!$this->checkAndCreateCustomSettings($customFields, $organisation->id)) {
                        DB::rollback();

                        return $this->errorResponse('Add Organisation Failure.');
                    }
                }
//                if (isset($data['custom_fields']) && is_array($data['custom_fields'])) { // TODO (waiting for details)
//
//                    foreach ($data['custom_fields'] as $custom) {
//                        if (empty($custom['label']) || empty($custom['value'])) {
//                            return ApiController::errorResponse('Label and value for custom fields are required.');
//                        } else {
//                            $customSettings = new CustomSetting;
//
//                            $customSettings->key = [$locale => $custom['label']];
//                            $customSettings->value = [$locale => $custom['value']];
//                            $customSettings->org_id = $organisation->id;
//                            $customSettings->created_by = \Auth::user()->id;
//
//
//                            $customSettings->save();
//                        }
//                    }
//                }

                DB::commit();

                return $this->successResponse(['org_id' => $organisation->id], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Add organisation failure');
    }

    /**
     * Edit organisation record
     *
     * @param object $request - POST request
     * @return json $response - response with status and success flag or error
     */
    public function editOrganisation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'data'  => 'required',
            'org_id'=> 'required|integer',
        ]);

        if (
            !$validator->fails()
            && !empty($organisation = Organisation::find($request->org_id))
            && (
                empty($request->data['type'])
                || in_array($request->data['type'], array_flip(Organisation::getPublicTypes()))
            )
        ) {
            $locale = \LaravelLocalization::getCurrentLocale();
            $orgData = [];
            $data = $request->data;

            if (!empty($data['name'])) {
                $orgData['name'] = [$locale => $data['name']];
            }

            if (!empty($data['description'])) {
                $orgData['descript'] = [$locale => $data['description']];
            }

            if (!empty($data['uri'])) {
                $orgData['uri'] = $data['uri'];
            }

            if (!empty($data['type'])) {
                $orgData['type'] = $data['type'];
            }

            if (!empty($data['logo'])) {
                try {
                    $img = \Image::make($data['logo']);
                } catch (NotReadableException $ex) {}

                if (!empty($img)) {
                    $img->resize(300, 200);
                    $orgData['logo_file_name'] = basename($data['logo']);
                    $orgData['logo_mime_type'] = $img->mime();
                    $orgData['logo_data'] = $img->encode('data-url');
                }
            }

            if (!empty($data['logo_filename'])) {
                $orgData['logo_file_name'] = $data['logo_filename'];
            }

            if (!empty($data['logo_mimetype'])) {
                $orgData['logo_mime_type'] = $data['logo_mimetype'];
            }

            if (!empty($data['logo_data'])) {
                $orgData['logo_data'] = $data['logo_data'];
            }

            if (!empty($data['activity_info'])) {
                $orgData['activity_info'] = [$locale => $data['activity_info']];
            }

            if (!empty($data['contacts'])) {
                $orgData['contacts'] = [$locale => $data['contacts']];
            }

            if (!empty($data['parent_org_id'])) {
                $orgData['parent_org_id'] = $data['parent_org_id'];
            }

            if (isset($data['active'])) {
                $orgData['active'] = $data['active'];
            }

            if (isset($data['approved'])) {
                $orgData['approved'] = $data['approved'];
            }

            if (!empty($data['custom_fields'])) {
                $customFields = $data['custom_fields'];
            }

            $newOrgSettingsData = [];
//            if (!empty($data['custom_fields']) && is_array($data['custom_fields'])) { // TODO (waiting for details)
//                foreach ($data['custom_fields'] as $custom) {
//                    if (empty($custom['label']) || empty($custom['value'])) {
//                        return ApiController::errorResponse('Edit organisation failure');
//                    } else {
//                        $newOrgSettingsData[] = [
//                            'key'        => [$locale => $custom['label']],
//                            'value'      => [$locale => $custom['value']],
//                            'org_id'     => $request->org_id,
//                        ];
//                    }
//                }
//            }

            DB::beginTransaction();

            try {
                if (!empty($orgData)) {
                    foreach($orgData as $prop => $val) {
                        $organisation->$prop = $val;
                    }

                    $organisation->save();
                }

                if (!empty($customFields)) {
                    if (!$this->checkAndCreateCustomSettings($customFields, $organisation->id)) {
                        DB::rollback();

                        return $this->errorResponse('Add organisation failure.');
                    }
                }
//                if (!empty($newOrgSettingsData)) {
//                    foreach ($newOrgSettingsData as $setting) {
//                        $customSettings = new CustomSetting;
//
//                        $customSettings->key = $setting['key'];
//                        $customSettings->value = $setting['value'];
//                        $customSettings->org_id = $setting['org_id'];
//
//                        if (
//                            !empty($settingsToEdit = CustomSetting::search([
//                                'org_id' => $customSettings->org_id,
//                                'key'    => $customSettings->key
//                            ])->first()) // to do this query with search library
//                        ) {
//                            $settingsToEdit->value = $setting['value'];
//
//                            $settingsToEdit->save();
//                        } else {
//                            $customSettings->save();
//                        }
//                    }
//                }

                DB::commit();

                return $this->successResponse();
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Edit organisation failure');
    }


    /**
     * Delete organisation record
     *
     * @param object $request - POST request
     * @return json $response - response with status and success flag or error
     */
    public function deleteOrganisation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'org_id' => 'required',
        ]);

        if (
            !$validator->fails()
            && !empty($organisation = Organisation::find($request->org_id))
        ) {
            try {
                $organisation->delete();
                $organisation->deleted_by = \Auth::id();
                $organisation->save();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Delete organisation failure');
    }

    /**
     * List organisation records
     *
     * @param object $request - POST request
     * @return json $response - response with results or empty array
     */
    public function listOrganisations(Request $request)
    {
        $results = [];
        $criteria = [];
        $count = 0;

        if (isset($request->criteria['active'])) {
            $criteria['active'] = $request->criteria['active'];
        }

        if (isset($request->criteria['approved'])) {
            $criteria['approved'] = $request->criteria['approved'];
        }

        if (isset($request->criteria['org_id'])) {
            $criteria['parent_org_id'] = $request->criteria['org_id'];
        }

        try {
            $query = Organisation::select();

            if (!empty($criteria)) {
                $query->where($criteria);
            }

            $count = $query->count();
            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
            $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

            $query->orderBy($field, $type);

            foreach ($query->get() as $org) {
                foreach ($org->customSetting()->get() as $setting) {
                    $customFields[] = [
                        'key'    =>$setting->key,
                        'value'  =>$setting->value
                    ];
                }

                $results[] = [
                    'name'            => $org->name,
                    'description'     => $org->descript,
                    'locale'          => $org->locale,
                    'uri'             => $org->uri,
                    'type'            => $org->type,
                    'logo'            => $org->logo,
                    'activity_info'   => $org->activity_info,
                    'contacts'        => $org->contacts,
                    'parent_org_id'   => $org->parent_org_id,
                    'approved'        => $org->approved,
                    'active'          => $org->active,
                    'custom_fields'   => $customFields,
                    'datasets_count'  => $org->dataSet()->count(),
                    'followers_count' => $org->userFollow()->count(),
                    'created_at'      => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                    'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                    'created_by'      => $org->created_by,
                    'updated_by'      => $org->updated_by,
                ];
            }

            return $this->successResponse(['organisations'=> $results, 'total_records' => $count], true);
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());
        }

        return ApiController::errorResponse('List organisation failure');
    }

    /**
     * Search organisation records
     *
     * @param object $request - POST request
     * @return json $response - response with results or empty array
     */
    public function searchOrganisations(Request $request)
    {
        $results = [];
        $count = 0;
        $validator = \Validator::make($request->all(), [
            'criteria.locale'       => 'nullable|string|max:5',
            'criteria.keywords'     => 'required|string',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
            try {
                $criteria = $request->criteria;

                $ids = Organisation::search($criteria['keywords'])->get()->pluck('id');
                $query = Organisation::whereIn('id', $ids);

                $count = $query->count();
                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
                $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

                $query->orderBy($field, $type);

                foreach ($query->get() as $org) {
                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    =>$setting->key,
                            'value'  =>$setting->value
                        ];
                    }

                    $results[] = [
                        'name'            => $org->name,
                        'description'     => $org->descript,
                        'locale'          => $org->locale,
                        'uri'             => $org->uri,
                        'type'            => $org->type,
                        'logo'            => $org->logo,
                        'activity_info'   => $org->activity_info,
                        'contacts'        => $org->contacts,
                        'parent_org_id'   => $org->parent_org_id,
                        'approved'        => $org->approved,
                        'active'          => $org->active,
                        'custom_fields'   => $customFields,
                        'datasets_count'  => $org->dataSet()->count(),
                        'followers_count' => $org->userFollow()->count(),
                        'created_at'      => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                        'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'      => $org->created_by,
                        'updated_by'      => $org->updated_by,
                    ];
                }

                return $this->successResponse(['organisations'=> $results, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Search organisation failure');
    }

    /**
     * Get organisation details
     *
     * @param Request $request - POST request
     * @return json $response - response with status and organisation info if successfull
     */
    public function getOrganisationDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'   => 'required|int',
            'locale'   => 'string',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            try {
                $org = Organisation::where('id', $post['org_id'])->first();
                $customFields = [];

                if ($org) {
                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    =>$setting->key,
                            'value'  =>$setting->value
                        ];
                    }

                    $result = [
                        'name'            => $org->name,
                        'description'     => $org->descript,
                        'locale'          => $locale,
                        'uri'             => $org->uri,
                        'type'            => $org->type,
                        'logo'            => $org->logo,
                        'activity_info'   => $org->activity_info,
                        'contacts'        => $org->contacts,
                        'parent_org_id'   => $org->parent_org_id,
                        'active'          => $org->active,
                        'approved'        => $org->approved,
                        'custom_fields'   => $customFields,
                        'datasets_count'  => $org->dataSet()->count(),
                        'followers_count' => $org->userFollow()->count(),
                        'created_at'      => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                        'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'      => $org->created_by,
                        'updated_by'      => $org->updated_by,
                    ];

                    return $this->successResponse($result);
                }
            } catch (QueryException $e) {
                return $this->errorResponse($e->getMessage());
            }
        }

        return $this->errorResponse('Get Organisation Details Failure');
    }

    /************************   MANAGE GROUPS   ************************/

    /**
     * Add new Group
     *
     * @param object $request - POST request
     * @return json $response - response with status and group id if successful
     */
    public function addGroup(Request $request)
    {
        $post = $request->get('data');

        if (!isset($post)) {
             return ApiController::errorResponse('No data provided.');
        }

        $validator = \Validator::make($post, [
            'name'    => 'required',
            'locale'  => 'required|string|max:5'
        ]);


        if (!isset($post['name']) || $post['name'] == '') {
            return ApiController::errorResponse('Group name is required.');
        }

        if (!isset($post['locale']) || $post['locale'] == '') {
            return ApiController::errorResponse('Locale is required.');
        }

        if (!$validator->fails() && !empty($post)) {
            $locale = $post['locale'];
            $newGroup = new Organisation;

            $newGroup->name = [$locale => $post['name']];
            $newGroup->descript = !empty($post['description']) ? [$locale => $post['description']] : null;
            $newGroup->uri = !empty($post['uri'])
                ? $post['uri']
                : $this->generateOrgUri($post['name']);

            $newGroup->type = Organisation::TYPE_GROUP;
            $newGroup->active = Organisation::ACTIVE_FALSE;
            $newGroup->approved = Organisation::APPROVED_FALSE;
            $newGroup->parent_org_id = null;


            if (!empty($post['logo'])) {
                try {
                    $img = \Image::make($post['logo']);
                } catch (NotReadableException $ex) {}

                if (!empty($img)) {
                    $img->resize(300, 200);
                    $newGroup->logo_file_name = basename($post['logo']);
                    $newGroup->logo_mime_type = $img->mime();
                    $newGroup->logo_data = $img->encode('data-url');
                }
            } else {
                $newGroup->logo_file_name = $post['logo_filename'];
                $newGroup->logo_mime_type = $post['logo_mimetype'];
                $newGroup->logo_data = $post['logo_data'];
            }

            if (!empty($post['custom_fields'])) {
                $customFields = $post['custom_fields'];
            }

            DB::beginTransaction();

            try {
                $newGroup->save();

                if ($newGroup) {
                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $newGroup->id)) {
                            DB::rollback();

                            return $this->errorResponse('Add Group Failure.');
                        }
                    }
                    DB::commit();

                    return $this->successResponse(['id' => $newGroup->id], true);
                } else {
                    DB::rollback();

                    return $this->errorResponse('Add Group Failure.');
                }
            } catch (QueryException $ex) {
                dd($ex->getMessage());

                return $this->errorResponse($ex->getMessage());
            }
        }
    }

    /**
     * Edit Group
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function editGroup(Request $request)
    {
        $data = $request->data;

        $id = $request->group_id;

        $validator = \Validator::make(
            $request->all(),
            [
                'data'          => 'required',
                'group_id'      => 'required',
                'data.locale'   => 'required_with:data.name, data.descript',
            ]
        );

        if ($validator->fails()) {
            if (!isset($id) || $id == '') {
                return ApiController::errorResponse('Id is required.');
            }

            if (isset($data['name']) && $data['name'] != '' && (!isset($data['locale']) || $data['locale'] == '')) {
                return ApiController::errorResponse('Group name requiures locale.');
            }
        }

        if (empty($group = Organisation::find($id))) {
            return ApiController::errorResponse('No Group Found.');
        }

        $newGroupData = [];

        if (!empty($data['name'])) {
            $newGroupData['name'] = $data['name'];
        }

        if (!empty($data['description'])) {
            $newGroupData['descript'] = $data['description'];
        }

        if (!empty($data['uri'])) {
            $newGroupData['uri'] = $data['uri'];
        }

        if (!empty($data['logo'])) {
            try {
                $img = \Image::make($data['logo']);
            } catch (NotReadableException $ex) {}

            if (!empty($img)) {
                $img->resize(300, 200);
                $newGroupData['logo_file_name'] = basename($data['logo']);
                $newGroupData['logo_mime_type'] = $img->mime();
                $newGroupData['logo_data'] = $img->encode('data-url');
            }
        }

        if (!empty($data['logo_filename'])) {
            $newGroupData['logo_filename'] = $data['logo_filename'];
        }

        if (!empty($data['logo_mimetype'])) {
            $newGroupData['logo_mimetype'] = $data['logo_mimetype'];
        }

        if (!empty($data['logo_data'])) {
            $newGroupData['logo_data'] = $data['logo_data'];
        }

        if (!empty($data['custom_fields'])) {
            $customFields = $data['custom_fields'];
        }

        if (!empty($newGroupData)) {
            $newGroupData['updated_by'] = \Auth::id();

            foreach ($newGroupData as $prop => $val) {
                $group->$prop = $val;
            }

            DB::beginTransaction();

            try {
                $group->save();

                if (!empty($customFields)) {
                    if (!$this->checkAndCreateCustomSettings($customFields, $group->id)) {
                        DB::rollback();

                        return $this->errorResponse('Add Group Failure.');
                    }
                }
                DB::commit();

                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();

                return ApiController::errorResponse('Edit Group Failure');
            }
        }

        return $this->successResponse();
    }

    /**
     * Delete existing group record
     *
     * @param object $request - POST request
     * @return json $response - response with status
     */
    public function deleteGroup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'group_id' => 'required',
        ]);

        $group = Organisation::find($request->group_id);

        if (empty($group)) {
            return ApiController::errorResponse('No Group Found.');
        }

        if (!$validator->fails()) {
            try {
                $group->delete();
                $group->deleted_by = \Auth::id();
                $group->save();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Delete organisation failure');
    }

    /**
     * List all groups
     *
     * @param Request $request - POST request
     * @return json $response - response with status and list of Data Sets from selected criteria
     */
    public function listGroups(Request $request)
    {
        $post = $request->all();
        $criteria = !empty($post['criteria']) ? $post['criteria'] : false;
        $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : 15;
        $page = !empty($post['page_number']) ? $post['page_number'] : 1;
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
        $locale = !empty($post['criteria']['locale'])
                ? $post['criteria']['locale']
                : \LaravelLocalization::getCurrentLocale();
        $groups = [];
        $result = [];

        $query = Organisation::where('type', Organisation::TYPE_GROUP);

        if ($criteria) {
            $validator = \Validator::make($post, [
                'criteria.locale'       => 'string|max:5',
                'criteria.dataset_id'   => 'integer',
                'criteria.order.type'   => 'string',
                'criteria.order.field'  => 'string',
                'records_per_page'      => 'integer',
                'page_number'           => 'integer',
            ]);

            if (!$validator->fails()) {
                if (!empty($criteria['dataset_id'])) {
                    $query->whereHas('dataSet', function($q) use ($criteria) {
                        $q->where('id', $criteria['dataset_id']);
                    });
                }
            }
        }

        if (!empty($order)) {
            $query->orderBy($order['field'], $order['type']);
        }

        $count = $query->count();

        if (!empty($pagination) && !empty($page)) {
            $query->paginate($pagination, ['*'], 'page', $page);
        }

        $groups = $query->get();

        if (!empty($groups)) {
            foreach ($groups as $group) {
                $customFields = [];

                //get custom fields
                foreach ($group->customSetting()->get() as $setting) {
                    $customFields[] = [
                        'key'    =>$setting->key,
                        'value'  =>$setting->value
                    ];
                }

                $result[] = [
                    'name'              => $group->name,
                    'description'       => $group->descript,
                    'locale'            => $locale,
                    'uri'               => $group->uri,
                    'logo'              => $group->logo,
                    'custom_fields'     => $customFields,
                    'datasets_count'    => $group->dataSet()->count(),
                    'followers_count'   => $group->userFollow()->count(),
                    'created_at'        => $group->created,
                    'updated_at'        => $group->updated_at,
                    'created_by'        => $group->created_by,
                    'updated_by'        => $group->updated_by,
                ];
            }

            return $this->successResponse(['groups'=> $result, 'total_records' => $count], true);
        } else {
            return ApiController::errorResponse('No Groups Found.');
        }
    }

    /**
     * Get group details
     *
     * @param Request $request - POST request
     * @return json $response - response with status and group info if successful
     */
    public function getGroupDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'group_id'   => 'required|int',
            'locale'     => 'string',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            try {
                $group = Organisation::where('id', $post['group_id'])->first();
                $customFields = [];

                if ($group) {
                    foreach ($group->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    =>$setting->key,
                            'value'  =>$setting->value
                        ];
                    }

                    $result = [
                        'name'          => $group->name,
                        'description'   => $group->descript,
                        'locale'        => $locale,
                        'uri'           => $group->uri,
                        'logo'          => $group->logo,
                        'custom_fields' => $customFields
                    ];

                    return $this->successResponse($result);
                }
            } catch (QueryException $e) {
                return $this->errorResponse($e->getMessage());
            }
        }

        return $this->errorResponse('Get Group Details Failure');
    }

    /**
     * Search group records
     *
     * @param object $request - POST request
     * @return json $response - response with results or empty array
     */
    public function searchGroups(Request $request)
    {
        $count = 0;

        $post = $request->all();

        $criteria = !empty($post['criteria']) ? $post['criteria'] : false;
        $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : 15;
        $page = !empty($post['page_number']) ? $post['page_number'] : 1;
        $locale = !empty($post['criteria']['locale'])
                ? $post['criteria']['locale']
                : \LaravelLocalization::getCurrentLocale();
        $result = [];
        $customFields = [];

        $validator = \Validator::make($request->all(), [
            'criteria.locale'       => 'nullable|string|max:5',
            'criteria.keywords'     => 'required|string',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
            try {
                $criteria = $request->criteria;

                $ids = Organisation::search($criteria['keywords'])->get()->pluck('id');

                $query = Organisation::where('type', Organisation::TYPE_GROUP);
                $query = Organisation::whereIn('id', $ids);

                $count = $query->count();
                $query->forPage(
                    $page,
                    $this->getRecordsPerPage($pagination)
                );

                $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
                $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

                $query->orderBy($field, $type);

                foreach ($query->get() as $group) {

                    //get custom fields
                    foreach ($group->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    =>$setting->key,
                            'value'  =>$setting->value
                        ];
                    }

                    $result[] = [
                        'name'              => $group->name,
                        'type'               => $group->type,
                        'description'       => $group->descript,
                        'locale'            => $group->locale,
                        'uri'               => $group->uri,
                        'logo'              => $group->logo,
                        'custom_fields'     => $customFields,
                        'datasets_count'    => $group->dataSet()->count(),
                        'followers_count'   => $group->userFollow()->count(),
                        'created_at'        => isset($group->created_at) ? $group->created_at->toDateTimeString() : null,
                        'updated_at'        => isset($group->updated_at) ? $group->updated_at->toDateTimeString() : null,
                        'created_by'        => $group->created_by,
                        'updated_by'        => $group->updated_by,
                    ];
                }

                return $this->successResponse(['groups'=> $result, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Search groups failure');
    }


    /**
     * Get unique uri for organisation (group) using the name
     *
     * @param string $name
     * @return string $uri
     */
    public function generateOrgUri($name)
    {
        $uri = $name . rand();

        while (Organisation::where('uri', $uri)->count()) {
            $uri = $name . rand();
        }

        return $uri;
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
        try {
            if (count($customFields) <= 3) {
                if ($orgId) {
                    DB::beginTransaction();
                    $deletedRows = CustomSetting::where('org_id', $orgId)->delete();

                    foreach ($customFields as $field) {
                        if ($field) {
                            if ($field['label'] != '' && $field['value'] != '') {
                                $customField = [
                                    'org_id'      => $orgId,
                                    'key'         => $field['label'],
                                    'value'       => $field['value'],
                                    'created_by'  => \Auth::user()->id,
                                ];

                                $saveField = CustomSetting::create($customField);
                            } else {
                                DB::rollback();

                                return false;
                            }
                        }
                    }
                    DB::commit();

                    return true;
                }
            }
        } catch (QueryException $ex) {
            return false;
        }
    }
}
