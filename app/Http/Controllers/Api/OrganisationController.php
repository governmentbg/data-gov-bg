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
            $organisation->descript = !empty($data['description']) ? [$locale => $data['description']] : null;

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

            DB::beginTransaction();

            try {
                $organisation->save();

                if (!empty($data['custom_fields']) && is_array($data['custom_fields'])) { // TODO (waiting for details)
                    $data = [];

                    foreach ($data['custom_fields'] as $custom) {
                        if (empty($custom['label']) || empty($custom['value'])) {
                            return ApiController::errorResponse('Add organisation failure');
                        } else {
                            $customSettings = new CustomSetting;

                            $customSettings->key = [$locale => $custom['label']];
                            $customSettings->value = [$locale => $custom['value']];
                            $customSettings->org_id = $organisation->id;

                            $customSettings->save();
                        }
                    }
                }

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

            $newOrgSettingsData = [];

            if (!empty($data['custom_fields']) && is_array($data['custom_fields'])) { // TODO (waiting for details)
                foreach ($data['custom_fields'] as $custom) {
                    if (empty($custom['label']) || empty($custom['value'])) {
                        return ApiController::errorResponse('Edit organisation failure');
                    } else {
                        $newOrgSettingsData[] = [
                            'key'        => [$locale => $custom['label']],
                            'value'      => [$locale => $custom['value']],
                            'org_id'     => $request->org_id,
                        ];
                    }
                }
            }

            DB::beginTransaction();

            try {
                if (!empty($orgData)) {
                    foreach($orgData as $prop => $val) {
                        $organisation->$prop = $val;
                    }

                    $organisation->save();
                }

                if (!empty($newOrgSettingsData)) {
                    foreach ($newOrgSettingsData as $setting) {
                        $customSettings = new CustomSetting;

                        $customSettings->key = $setting['key'];
                        $customSettings->value = $setting['value'];
                        $customSettings->org_id = $setting['org_id'];

                        if (
                            !empty($settingsToEdit = CustomSetting::search([
                                'org_id' => $customSettings->org_id,
                                'key'    => $customSettings->key
                            ])->first()) // to do this query with search library
                        ) {
                            $settingsToEdit->value = $setting['value'];

                            $settingsToEdit->save();
                        } else {
                            $customSettings->save();
                        }
                    }
                }

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
                $results[] = [
                    'name'          => $org->name,
                    'description'   => $org->description,
                    'locale'        => $org->locale,
                    'type'          => $org->type,
                    'logo'          => $org->logo,
                    'activity_info' => $org->activity_info,
                    'contacts'      => $org->contacts,
                    'parent_org_id' => $org->parent_org_id,
                    'approved'      => $org->approved,
                    'active'        => $org->active,
                    'custom_fields' => '', // TODO get custom fields
                    'created_at'    => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                    'updated_at'    => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                    'created_by'    => $org->created_by,
                    'updated_by'    => $org->updated_by,
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
        $result = [];
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
                    $results[] = [
                        'name'          => $org->name,
                        'description'   => $org->description,
                        'locale'        => $org->locale,
                        'type'          => $org->type,
                        'logo'          => $org->logo,
                        'activity_info' => $org->activity_info,
                        'contacts'      => $org->contacts,
                        'parent_org_id' => $org->parent_org_id,
                        'approved'      => $org->approved,
                        'active'        => $org->active,
                        'custom_fields' => '', // TODO get custom fields
                        'created_at'    => isset($org->created_at) ? $org->created_at->toDateTimeString() : null,
                        'updated_at'    => isset($org->updated_at) ? $org->updated_at->toDateTimeString() : null,
                        'created_by'    => $org->created_by,
                        'updated_by'    => $org->updated_by,
                    ];
                }

                return $this->successResponse(['organisations'=> $results, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return ApiController::errorResponse('Search organisation failure');
    }
}
