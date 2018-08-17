<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Organisation;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class GroupController extends AdminController
{
    /**
     * Function for getting an array of translatable fields for groups
     *
     * @return array of fields
     */
    public static function getGroupTransFields()
    {
        return [
            [
                'label'    => 'custom.label_name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['custom.title', 'custom.value'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

    /**
     * Lists the groups in which the user is a member of
     *
     * @param Request $request
     *
     * @return view with list of groups
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $perPage = 6;
            $params = [
                'api_key'          => \Auth::user()->api_key,
                'criteria'         => [],
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            $orgReq = Request::create('/api/listGroups', 'POST', $params);
            $api = new ApiOrganisation($orgReq);
            $result = $api->listGroups($orgReq)->getData();

            $groups = !empty($result->groups) ? $result->groups : [];

            $paginationData = $this->getPaginationData($groups, count($groups), [], $perPage);

            return view('/admin/groups', [
                'class'         => 'user',
                'groups'        => $paginationData['items'],
                'pagination'    => $paginationData['paginate']
            ]);
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Registers a group
     *
     * @param Request $request
     *
     * @return view with registered group
     */
    public function register(Request $request)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getGroupTransFields();

            if ($request->has('create')) {
                $data = $request->all();
                $data['description'] = $data['descript'];

                if (!empty($data['logo'])) {
                    try {
                        $img = \Image::make($data['logo']);

                        $data['logo_filename'] = $data['logo']->getClientOriginalName();
                        $data['logo_mimetype'] = $img->mime();
                        $data['logo_data'] = file_get_contents($data['logo']);

                        unset($data['logo']);
                    } catch (\Exception $ex) {
                        Log::error($ex->getMessage());
                    }
                }

                $params = [
                    'api_key'   => \Auth::user()->api_key,
                    'data'      => $data,
                ];

                $groupReq = Request::create('api/addGroup', 'POST', $params);
                $orgApi = new ApiOrganisation($groupReq);
                $result = $orgApi->addGroup($groupReq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.successful_group_creation'));

                    return redirect('/admin/groups/view/'. Organisation::where('id', $result->id)->value('uri'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.failed_group_creation'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view('/admin/groupRegistration', compact('class', 'fields'));
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Displays information for a given group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $request = Request::create('/api/getGroupDetails', 'POST', [
                'group_id'  => $orgId,
                'locale'    => \LaravelLocalization::getCurrentLocale(),
            ]);
            $api = new ApiOrganisation($request);
            $result = $api->getGroupDetails($request)->getData();

            if ($result->success) {
                return view('admin/groupView', ['class' => 'user', 'group' => $result->data, 'id' => $orgId]);
            }

            return redirect('/admin/groups');
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Deletes a group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view to previous page
     */
    public function delete(Request $request, $id)
    {
        $orgId = Organisation::where('id', $id)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $delArr = [
                'api_key'   => \Auth::user()->api_key,
                'group_id'  => $id,
            ];

            $delReq = Request::create('/api/deleteGroup', 'POST', $delArr);
            $api = new ApiOrganisation($delReq);
            $result = $api->deleteGroup($delReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return back();
            }


            $request->session()->flash('alert-danger', __('custom.delete_error'));

            return back();
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Edit a group based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $class = 'user';
            $fields = self::getGroupTransFields();

            $model = Organisation::find($orgId)->loadTranslations();
            $withModel = CustomSetting::where('org_id', $orgId)->get()->loadTranslations();
            $model->logo = $this->getImageData($model->logo_data, $model->logo_mime_type, 'group');

            if ($request->has('edit')) {
                $data = $request->all();

                $data['description'] = isset($data['descript']) ? $data['descript'] : null;

                if (!empty($data['logo'])) {
                    try {
                        $img = \Image::make($data['logo']);

                        $data['logo_file_name'] = $data['logo']->getClientOriginalName();
                        $data['logo_mime_type'] = $img->mime();
                        $data['logo_data'] = file_get_contents($data['logo']);

                        unset($data['logo']);
                    } catch (\Exception $ex) {}
                }

                $params = [
                    'api_key'   => \Auth::user()->api_key,
                    'group_id'  => $orgId,
                    'data'      => $data,
                ];

                $editReq = Request::create('/api/editGroup', 'POST', $params);
                $api = new ApiOrganisation($editReq);
                $result = $api->editGroup($editReq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors(isset($result->errors) ? $result->errors : []);
            }

            return view('admin/groupEdit', compact('class', 'fields', 'model', 'withModel'));
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Filters groups based on search string
     *
     * @param Request $request
     *
     * @return view with filtered group list
     */
    public function search(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 6;
            $search = $request->offsetGet('q');

            if (empty($search)) {
                return redirect('admin/groups');
            }

            $params = [
                'records_per_page'  => $perPage,
                'criteria'          => [
                    'keywords'          => $search,
                ]
            ];

            $searchRq = Request::create('/api/searchGroups', 'POST', $params);
            $api = new ApiOrganisation($searchRq);
            $grpData = $api->searchGroups($searchRq)->getData();

            $groups = !empty($grpData->groups) ? $grpData->groups : [];
            $count = !empty($grpData->total_records) ? $grpData->total_records : 0;

            $getParams = [
                'search' => $search
            ];

            $paginationData = $this->getPaginationData($groups, $count, $getParams, $perPage);

            return view('admin/groups', [
                'class'         => 'user',
                'groups'        => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search,
            ]);
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }
}
