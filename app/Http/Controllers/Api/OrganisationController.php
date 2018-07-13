<?php

namespace App\Http\Controllers\Api;

use App\Locale;
use App\Organisation;
use App\OrgCustomSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;

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
        $validator = \Validator::make(
            $request->all(),
            [
                'data.name'   => 'required',
                'data.locale' => 'required',
                'data.type'   => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Add organisation failure');
        }

        if (!in_array($request->data['type'], array_flip(Organisation::getPublicTypes()))) {

            return ApiController::errorResponse('Add organisation failure');
        }

        $orgLocale = !empty(Locale::where('locale', $request->data['locale'])->value('locale'))
            ? $request->data['locale']
            : config('app.locale');

        $organisation = new Organisation;

        $UrlLogoData = $this->getImgDataFromUrl(isset($request->data['logo']) ? $request->data['logo'] : null);

        $organisation->type = $request->data['type'];
        $organisation->name = [$orgLocale => $request->data['name']];
        $organisation->descript = !empty($request->data['description'])
            ? [$orgLocale => $request->data['description']]
            : [$orgLocale => null];
        $organisation->logo_file_name = !empty($request->data['logo_filename'])
            ? $request->data['logo_filename']
            : $UrlLogoData['name'];
        $organisation->logo_mime_type = !empty($request->data['logo_mimetype'])
            ? $request->data['logo_mimetype']
            : $UrlLogoData['mime'];
        $organisation->logo_data = !empty($request->data['logo_data'])
            ? $request->data['logo_data']
            : $UrlLogoData['data'];
        $organisation->activity_info = !empty($request->data['activity_info'])
            ? [$orgLocale => $request->data['activity_info']]
            : [$orgLocale => null];
        $organisation->contacts = !empty($request->data['contacts'])
            ? [$orgLocale => $request->data['contacts']]
            : [$orgLocale => null];
        $organisation->parent_org_id = !empty($request->data['parent_org_id'])
            ? $request->data['parent_org_id']
            : null;
        $organisation->active = isset($request->data['active'])
            ? $request->data['active']
            : 1;
        $organisation->approved = isset($request->data['approved']) && $request->data['type'] == Organisation::TYPE_CIVILIAN
            ? $request->data['approved']
            : 0;
        $organisation->created_by = \Auth::id();

        try {
            $organisation->save();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Add organisation failure');
        }

        if (!empty($request->data['custom_fields']) && is_array($request->data['custom_fields'])) {
            $data = [];
            foreach ($request->data['custom_fields'] as $custom) {
                if (empty($custom['label']) || empty($custom['value'])) {

                    return ApiController::errorResponse('Add organisation failure');
                } else {
                    $customSettings = new OrgCustomSetting;

                    $customSettings->key = [$orgLocale => $custom['label']];
                    $customSettings->value = [$orgLocale => $custom['value']];
                    $customSettings->org_id = $organisation->id;
                    $customSettings->created_by = \Auth::id();

                    try {
                        $customSettings->save();
                    } catch (QueryException $e) {

                        return ApiController::errorResponse('Add organisation failure');
                    }
                }
            }
        }

        return $this->successResponse(['org_id' => $organisation->id], true);
    }

    public function editOrganisation(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'data'  => 'required',
                'org_id'=> 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Edit organisation failure');
        }

        if (empty($organisation = Organisation::find($request->org_id))) {

            return ApiController::errorResponse('Edit organisation failure');
        }

        $newOrgData = [];
        $orgLocale = !empty($request->data['locale']) && !empty(Locale::where('locale', $request->data['locale'])->value('locale'))
            ? $request->data['locale']
            : config('app.locale');

        if (!empty($request->data['name'])) {
            $newOrgData['name'] = [$orgLocale => $request->data['name']];
        }

        if (!empty($request->data['description'])) {
            $newOrgData['descript'] = [$orgLocale => $request->data['description']];
        }

        if (!empty($request->data['type'])) {
            if (!in_array($request->data['type'], array_flip(Organisation::getPublicTypes()))) {

                return ApiController::errorResponse('Edit organisation failure');
            }
            $newOrgData['type'] = $request->data['type'];
        }

        if (!empty($request->data['logo'])) {
            $UrlLogoData = $this->getImgDataFromUrl($request->data['logo']);
            $newOrgData['logo_file_name'] = $UrlLogoData['name'];
            $newOrgData['logo_mime_type'] = $UrlLogoData['mime'];
            $newOrgData['logo_data'] = $UrlLogoData['data'];
        }

        if (!empty($request->data['logo_filename'])) {
            $newOrgData['logo_file_name'] = $request->data['logo_filename'];
        }

        if (!empty($request->data['logo_mimetype'])) {
            $newOrgData['logo_mime_type'] = $request->data['logo_mimetype'];
        }

        if (!empty($request->data['logo_data'])) {
            $newOrgData['logo_data'] = $request->data['logo_data'];
        }

        if (!empty($request->data['activity_info'])) {
            $newOrgData['activity_info'] = [$orgLocale => $request->data['activity_info']];
        }

        if (!empty($request->data['contacts'])) {
            $newOrgData['contacts'] = [$orgLocale => $request->data['contacts']];
        }

        if (!empty($request->data['parent_org_id'])) {
            $newOrgData['parent_org_id'] = $request->data['parent_org_id'];
        }

        if (isset($request->data['active'])) {
            $newOrgData['active'] = $request->data['active'];
        }

        if (isset($request->data['approved'])) {
            $newOrgData['approved'] = $request->data['approved'];
        }

        $newOrgSettingsData = [];

        if (!empty($request->data['custom_fields']) && is_array($request->data['custom_fields'])) {
            foreach ($request->data['custom_fields'] as $custom) {
                if (empty($custom['label']) || empty($custom['value'])) {

                    return ApiController::errorResponse('Edit organisation failure');
                } else {
                    $newOrgSettingsData[] = [
                        'key'        => [$orgLocale => $custom['label']],
                        'value'      => [$orgLocale => $custom['value']],
                        'org_id'     => $request->org_id,
                    ];
                }
            }
        }

        if (empty($newOrgData) && empty($newOrgSettingsData)) {

            return ApiController::errorResponse('Edit organisation failure');
        }

        if (!empty($newOrgData)) {
            $newOrgData['updated_by'] = \Auth::id();
            foreach($newOrgData as $prop => $val) {
                $organisation->$prop = $val;
            }

            try {
                $organisation->save();
            } catch (QueryException $e) {

                return ApiController::errorResponse('Edit organisation failure');
            }
        }

        foreach($newOrgSettingsData as $setting) {
            $customSettings = new OrgCustomSetting;

            $customSettings->key = $setting['key'];
            $customSettings->value = $setting['value'];
            $customSettings->org_id = $setting['org_id'];
            $customSettings->created_by = \Auth::id();

            if (
                !empty($settingsToEdit = OrgCustomSetting::where(
                        [
                            'org_id' => $customSettings->org_id,
                            'key'    => $customSettings->key
                        ]
                    )->first() // to do this query with search library
                )
            ) {
                $settingsToEdit->value = $setting['value'];
                $settingsToEdit->updated_by = \Auth::id();

                try {
                    $settingsToEdit->save();
                } catch (QueryException $e) {

                    return ApiController::errorResponse('Edit organisation failure');
                }
            } else {
                try {
                    $customSettings->save();
                } catch (QueryException $e) {

                    return ApiController::errorResponse('Edit organisation failure');
                }
            }
        }

        return $this->successResponse();
    }

    public function deleteOrganisation(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'org_id' => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Delete organisation failure');
        }

        if (empty($organisation = Organisation::find($request->org_id))) {
            return ApiController::errorResponse('Delete organisation failure');
        }

        try {
            $organisation->delete();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Delete organisation failure');
        }

        try {
            $organisation->deleted_by = \Auth::id();
            $organisation->save();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Delete organisation failure');
        }

        return $this->successResponse();
    }

    public function listOrganisations(Request $request)
    {
        $result = [];
        $criteria = [];
        $orderType = !empty($request->criteria['order']['type']) ? $request->criteria['order']['type'] : null;
        $orderField = !empty($request->criteria['order']['field']) ? $request->criteria['order']['field'] : null;
        $pagination = is_numeric($request->records_per_page)
            ? $request->records_per_page
            : null;
        $page = is_numeric($request->page_number)
            ? $request->page_number
            : null;


        if (isset($request->criteria['active'])) {
            $criteria['active'] = $request->criteria['active'];
        }

        if (isset($request->criteria['approved'])) {
            $criteria['approved'] = $request->criteria['approved'];
        }

        if (isset($request->criteria['org_id'])) {
            $criteria['parrent_org_id'] = $request->criteria['org_id'];
        }

        try {
            $query = Organisation::select();

            if (!empty($criteria)) {
                $query->where($criteria);
            }

            if ($pagination) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            if ($orderType && $orderField) {
                $query->orderBy($orderField, $orderType);
            }

            $organisations = $query->get(); // to do // with search library
        } catch (QueryException $e) {

            return ApiController::errorResponse('Get organisations list failure');
        }

        if (!empty($organisations)) {
            foreach ($organisations as $org) {
                $result[] = [
                    'name'           => $org->name,
                    'description'    => $org->description,
                    'locale'         => $org->locale,
                    'type'           => $org->type,
                    'logo'           => $org->logo,
                    'activity_info'  => $org->activity_info,
                    'contacts'       => $org->contacts,
                    'parrent_org_id' => $org->parent_org_id,
                    'approved'       => $org->approved,
                    'active'         => $org->active,
                    'custom_fields'  => '',
                    'created_at'     => $org->created,
                    'updated_at'     => $org->updated_at,
                    'created_by'     => $org->created_by,
                    'updated_by'     => $org->updated_by,
                ];
            }
        }

        return $this->successResponse(['organisations'=> $result, 'total_records' => count($organisations)], true);
    }

    public function  searchOrganisations(Request $request)
    {
        $result = [];
        $order = !empty($request->criteria['order']['type']) && !empty($request->criteria['order']['field']);
        $search = !empty($request->criteria['keywords'])
            ? $request->criteria['keywords']
            : null;
        $pagination = is_numeric($request->records_per_page)
            ? $request->records_per_page
            : null;
        $page = is_numeric($request->page_number)
            ? $request->page_number
            : null;

        try {
            $query = Organisation::select();

            if (!is_null($search)) {
                $query->where(function ($qr) use ($search) {
                    $qr->where('name', 'like', '%'. $search .'%')
                        ->orWhere('descript', 'like', '%'. $search .'%')
                        ->orWhere('activity_info', 'like', '%'. $search .'%')
                        ->orWhere('contacts', 'like', '%'. $search .'%');
                }); // to do // search library
            }

            if ($pagination) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            if ($order) {
                $query->orderBy($request->criteria['order']['field'], $request->criteria['order']['type']);
            }

            $organisations = $query->get();

        } catch (QueryException $e) {

            return ApiController::errorResponse('Search organisations failure');
        }

        if (!empty($organisations)) {
            foreach ($organisations as $org) {
                $result[] = [
                    'id'            => $org->id,
                    'username'      => $org->username,
                    'email'         => $org->email,
                    'firstname'     => $org->firstname,
                    'lastname'      => $org->lastname,
                    'add_info'      => $org->add_info,
                    'is_admin'      => $org->is_admin,
                    'active'        => $org->active,
                    'approved'      => $org->approved,
                    'api_key'       => $org->api_key,
                    'hash_id'       => $org->hash_id,
                    'created_at'    => $org->created,
                    'updated_at'    => $org->updated_at,
                    'created_by'    => $org->created_by,
                    'updated_by'    => $org->updated_by,
                ];
            }
        }

        return $this->successResponse(['organisations'=> $result, 'total_records' => count($organisations)], true);
    }
}
