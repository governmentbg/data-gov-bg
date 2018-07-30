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
     * @param string api_key - required
     * @param array data -required
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
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'locale'                => 'required|string',
            'name'                  => 'required|string',
            'type'                  => 'required|integer',
            'description'           => 'nullable|string',
            'uri'                   => 'nullable|string',
            'logo'                  => 'nullable|string',
            'logo_filename'         => 'nullable|string',
            'logo_mimetype'         => 'nullable|string',
            'logo_data'             => 'nullable|string',
            'activity_info'         => 'nullable|string',
            'contacts'              => 'nullable|string',
            'parent_org_id'         => 'nullable|integer',
            'active'                => 'nullable|bool',
            'approved'              => 'nullable|bool',
            'custom_fields.*.label' => 'nullable|string',
            'custom_fields.*.value' => 'nullable|string',
        ]);

        if (
            !$validator->fails()
            && in_array($data['type'], array_keys(Organisation::getPublicTypes()))
        ) {
            $organisation = new Organisation;
            $locale = \LaravelLocalization::getCurrentLocale();

            $organisation->type = $data['type'];
            $organisation->name = $data['name'];
            $organisation->descript = !empty($data['description'])
                    ? $data['description']
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
            } elseif (isset($data['logo_filename']) && isset($data['logo_mimetype']) && isset($data['logo_data'])) {
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

                DB::commit();

                return $this->successResponse(['org_id' => $organisation->id], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Add organisation failure', $validator->errors()->messages());
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
        $data = $request->all();

        $validator = \Validator::make($data, [
            'data'                          => 'required',
            'org_id'                        => 'required|integer',
            'data.locale'                   => 'required|string',
            'data.name'                     => 'required|string',
            'data.type'                     => 'required|integer',
            'data.description'              => 'nullable|string',
            'data.uri'                      => 'nullable|string',
            'data.logo'                     => 'nullable|string',
            'data.logo_filename'            => 'nullable|string',
            'data.logo_mimetype'            => 'nullable|string',
            'data.logo_data'                => 'nullable|string',
            'data.activity_info'            => 'nullable|string',
            'data.contacts'                 => 'nullable|string',
            'data.parent_org_id'            => 'nullable|integer',
            'data.active'                   => 'nullable|bool',
            'data.approved'                 => 'nullable|bool',
            'data.custom_fields.*.label'    => 'nullable|string',
            'data.custom_fields.*.value'    => 'nullable|string',
        ]);

        if (
            !$validator->fails()
            && !empty($organisation = Organisation::find($request->org_id))
            && (
                empty($data['type'])
                || in_array($data['type'], array_flip(Organisation::getPublicTypes()))
            )
        ) {
            $locale = \LaravelLocalization::getCurrentLocale();
            $orgData = [];

            if (!empty($data['name'])) {
                $orgData['name'] = $data['name'];
            }

            if (!empty($data['description'])) {
                $orgData['descript'] = $data['description'];
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

            DB::beginTransaction();

            try {
                if (!empty($orgData)) {
                    foreach ($orgData as $prop => $val) {
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

                DB::commit();

                return $this->successResponse();
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Edit organisation failure', $validator->errors()->messages());
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

        return ApiController::errorResponse('Delete organisation failure', $validator->errors()->messages());
    }

    /**
     * List organisations by criteria
     *
     * @param array criteria - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[approved] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json lsit with organisations or error
     */
    public function listOrganisations(Request $request)
    {
        $results = [];
        $criteria = [];
        $count = 0;

        $validator = \Validator::make($request->all(), [
            'criteria.locale'       => 'nullable|string',
            'criteria.active'       => 'nullable|bool',
            'criteria.approved'     => 'nullable|bool',
            'criteria.org_id'       => 'nullable|integer',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
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
                    $customFields = [];

                    foreach ($org->customSetting()->get() as $setting) {
                        $customFields[] = [
                            'key'    => $setting->key,
                            'value'  => $setting->value
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
                        'created_at'      => $org->created_at->toDateTimeString(),
                        'updated_at'      => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'      => $org->created_by,
                        'updated_by'      => $org->updated_by,
                    ];
                }

                return $this->successResponse(['organisations' => $results, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('List organisation failure', $validator->errors()->messages());
    }

    /**
     * Search organisations by criteria
     *
     * @param array criteria - required
     * @param string criteria[locale] - optional
     * @param integer criteria[keywords] - required
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json with found organisations or error
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
                    $customFields = [];

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

        return ApiController::errorResponse('Search organisation failure', $validator->errors()->messages());
    }

    /**
     * Get organisation details
     *
     * @param string locale - required
     * @param integer org_id - required
     *
     * @return json with organisatin deetails or error
     */
    public function getOrganisationDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'   => 'required|integer',
            'locale'   => 'nullable|string',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            try {
                $org = Organisation::where('id', $post['org_id'])->first();
                
                if ($org) {
                    $customFields = [];
                    
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
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse('Get Organisation Details Failure', $validator->errors()->messages());
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
            'locale'                => 'required|string',
            'name'                  => 'required|string',
            'description'           => 'nullable|string',
            'uri'                   => 'nullable|string',
            'logo'                  => 'nullable|string',
            'logo_filename'         => 'required_with:logo_mimetype,logo_data|string',
            'logo_mimetype'         => 'required_with:llogo_filename,logo_data|string',
            'logo_data'             => 'required_with:llogo_filename,logo_mimetype|string',
            'activity_info'         => 'nullable|string',
            'active'                => 'nullable|integer',
            'custom_fields.*.label' => 'nullable|string',
            'custom_fields.*.value' => 'nullable|string',
        ]);

        if (!$validator->fails()) {
            $newGroup = new Organisation;

            $newGroup->name =$post['name'];
            $newGroup->descript = !empty($post['description']) ? $post['description'] : null;
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
                } catch (NotReadableException $ex) {
                    Log::error($ex->getMessage());
                }

                if (!empty($img)) {
                    $img->resize(300, 200);
                    $newGroup->logo_file_name = basename($post['logo']);
                    $newGroup->logo_mime_type = $img->mime();
                    $newGroup->logo_data = $img->encode('data-url');
                }
            } else {
                $newGroup->logo_file_name = isset($post['logo_filename']) ? $post['logo_filename'] : null;
                $newGroup->logo_mime_type = isset($post['logo_mimetype']) ? $post['logo_mimetype'] : null;
                $newGroup->logo_data = isset($post['logo_data']) ? $post['logo_data'] : null;
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
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add Group Failure.', $validator->errors()->messages());
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
        $data = $request->data;

        $id = $request->group_id;

        $validator = \Validator::make($request->all(),[
            'group_id'              => 'required',
            'data'                  => 'required|array',
            'data.name'             => 'nullable|string',
            'data.description'      => 'nullable|string',
            'data.locale'           => 'required_with:data.name,data.description',
            'uri'                   => 'nullable|string',
            'logo'                  => 'nullable|string',
            'logo_filename'         => 'nullable|string',
            'logo_mimetype'         => 'nullable|string',
            'logo_data'             => 'nullable|string',
            'custom_fields.*.label' => 'nullable|string',
            'custom_fields.*.value' => 'nullable|string',
        ]);

        if (!$validator->fails()) {

            if (empty($group = Organisation::find($id))) {
                return $this->errorResponse('No Group Found.');
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
                    Log::errror($e->getMessages());

                    DB::rollback();
                }
            }
        }

        return $this->errorResponse('Edit Group Failure', $validator->errors()->messages());
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
            'group_id' => 'required|integer',
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

        return ApiController::errorResponse('Delete organisation failure', $validator->errors()->messages());
    }

    /**
     * List all listGroups
     *
     * @param integer criteria - optional
     * @param integer criteria[dataset_id] - optional
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
        $groups = [];
        $result = [];

        $query = Organisation::where('type', Organisation::TYPE_GROUP);

        if ($criteria) {
            $validator = \Validator::make($post, [
                'criteria.locale'       => 'nullable|string|max:5',
                'criteria.dataset_id'   => 'nullable|integer',
                'criteria.order.type'   => 'nullable|string',
                'criteria.order.field'  => 'nullable|string',
                'records_per_page'      => 'nullable|integer',
                'page_number'           => 'nullable|integer',
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
            return ApiController::errorResponse('No Groups Found.', $validator->errors()->messages());
        }
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
            'group_id'   => 'required|int',
            'locale'     => 'nullable|string',
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

        return $this->errorResponse('Get Group Details Failure', $validator->errors()->messages());
    }

    /**
     * Search group records
     *
     * @param array criteria - required
     * @param integer criteria[keywords] - required
     * @param string criteria[locale] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
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

        return ApiController::errorResponse('Search groups failure', $validator->errors()->messages());
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
            Log::error($ex->getMessage());

            return false;
        }
    }
}
