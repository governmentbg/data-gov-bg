<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Organisation;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class OrganisationController extends AdminController
{
    /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getTransFields()
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
                'label'    => 'custom.activity',
                'name'     => 'activity_info',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.contact',
                'name'     => 'contacts',
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
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 6;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            if (isset($request->active)) {
                $params['criteria']['active'] = (bool) $request->active;
            }

            if (isset($request->approved)) {
                $params['criteria']['approved'] = (bool) $request->approved;
            }

            if (isset($request->parent)) {
                $parent = Organisation::where('uri', $request->parent)->first();

                if (isset($parent->id)) {
                    $params['criteria']['org_id'] = $parent->id;
                }
            }

            $request = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->listOrganisations($request)->getData();

            $paginationData = $this->getPaginationData(
                $result->organisations,
                $result->total_records,
                array_except(app('request')->input(), ['q', 'page',]),
                $perPage
            );

            return view(
                'admin/organisations',
                [
                    'class'         => 'user',
                    'organisations' => $paginationData['items'],
                    'pagination'    => $paginationData['paginate'],
                    'selectedOrg'   => isset($parent) && !empty($parent->id)
                        ? $parent
                        : null
                ]
            );
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for searching organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations or
     * a list of filtered organisations if search string is provided
     */
    public function search(Request $request)
    {
        if (Role::isAdmin()) {
            $search = $request->q;

            if (empty(trim($search))) {
                return redirect('/admin/organisations');
            }

            $perPage = 6;
            $params = [
                'api_key'          => \Auth::user()->api_key,
                'criteria'         => [
                    'keywords' => $search,
                ],
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            $request = Request::create('/api/searchOrganisations', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->searchOrganisations($request)->getData();
            $organisations = !empty($result->organisations) ? $result->organisations : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;

            $getParams = ['q' => $search];

            $paginationData = $this->getPaginationData(
                $organisations,
                $count,
                $getParams,
                $perPage
            );

            return view(
                'admin/organisations',
                [
                    'class'         => 'user',
                    'organisations' => $paginationData['items'],
                    'pagination'    => $paginationData['paginate'],
                    'search'        => $search
                ]
            );
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for registering an organisation
     *
     * @param Request $request
     *
     * @return view to register an organisation or
     * a view to view the registered organisation
     */
    public function register(Request $request)
    {
        if (Role::isAdmin()) {
            $post = [
                'data' => $request->all()
            ];

            if (!empty($post['data']['logo'])) {
                try {
                    $img = \Image::make($post['data']['logo']);

                    $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                    $post['data']['logo_mimetype'] = $img->mime();
                    $post['data']['logo_data'] = file_get_contents($post['data']['logo']);

                    unset($post['data']['logo']);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }

            $post['data']['description'] = $post['data']['descript'];
            $request = Request::create('/api/addOrganisation', 'POST', $post);
            $api = new ApiOrganisation($request);
            $result = $api->addOrganisation($request)->getData();

            if ($result->success) {
                session()->flash('alert-success', __('custom.add_org_success'));
            } else {
                session()->flash(
                    'alert-danger',
                    isset($result->error) ? $result->error->message : __('custom.add_org_error')
                );
            }

            return $result->success
                ? redirect('admin/organisations/view/'. Organisation::where('id', $result->org_id)->value('uri'))
                : redirect('admin/organisations/register')->withInput(Input::all())->withErrors($result->errors);
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for registering an organisations
     *
     * @return view login on success or error on fail
     */
    public function showOrgRegisterForm() {

        if (Role::isAdmin()) {
            $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

            $query->whereHas('userToOrgRole', function($q) {
                $q->where('user_id', \Auth::user()->id);
            });

            $parentOrgs = $query->get();

            return view(
                'admin/orgRegister',
                [
                    'class'      => 'user',
                    'fields'     => self::getTransFields(),
                    'parentOrgs' => $parentOrgs
                ]
            );
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for viewing an organisation
     *
     * @param Request $request
     *
     * @return view to view the a registered organisation
     */
    public function view(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_id' => $orgId]);
            $api = new ApiOrganisation($request);
            $result = $api->getOrganisationDetails($request)->getData();

            if ($result->success) {
                return view('admin/orgView', ['class' => 'user', 'organisation' => $result->data]);
            }

            return redirect('/admin/organisations');
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for editing an organisation
     *
     * @param Request $request
     *
     * @return view for editing org details
     */
    public function edit(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

            $parentOrgs = $query->get();

            if (isset($request->view)) {
                $orgModel = Organisation::with('CustomSetting')->find($orgId)->loadTranslations();
                $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
                $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

                return view(
                    'admin/orgEdit',
                    [
                        'class'      => 'user',
                        'model'      => $orgModel,
                        'withModel'  => $customModel,
                        'fields'     => self::getTransFields(),
                        'parentOrgs' => $parentOrgs
                    ]
                );
            }

            $post = [
                'data'          => $request->all(),
                'org_id'        => $orgId,
                'parentOrgs'    => $parentOrgs,
            ];

            if (!empty($post['data']['logo'])) {
                try {
                    $img = \Image::make($post['data']['logo']);

                    $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                    $post['data']['logo_mimetype'] = $img->mime();
                    $post['data']['logo_data'] = file_get_contents($post['data']['logo']);

                    unset($post['data']['logo']);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }

            $post['data']['description'] = $post['data']['descript'];
            $request = Request::create('/api/editOrganisation', 'POST', $post);
            $api = new ApiOrganisation($request);
            $result = $api->editOrganisation($request)->getData();
            $errors = !empty($result->errors) ? $result->errors : [];

            $orgModel = Organisation::with('CustomSetting')->find($orgId)->loadTranslations();
            $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
            $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

            if ($result->success) {
                session()->flash('alert-success', __('custom.edit_success'));
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));
            }

            return !$result->success
                ? view(
                    'admin/orgEdit',
                    [
                        'class'      => 'user',
                        'model'      => $orgModel,
                        'withModel'  => $customModel,
                        'fields'     => self::getTransFields(),
                        'parentOrgs' => $parentOrgs
                    ]
                )->withErrors($result->errors)
                : view(
                    'admin/orgEdit',
                    [
                        'class'      => 'user',
                        'model'      => $orgModel,
                        'withModel'  => $customModel,
                        'fields'     => self::getTransFields(),
                        'parentOrgs' => $parentOrgs
                    ]
                );

            return redirect('/admin/organisations');
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }

    /**
     * Loads a view for deleting organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations and request success message
     */
    public function delete(Request $request, $id)
    {
        $orgId = Organisation::where('id', $id)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $params = [
                'api_key' => \Auth::user()->api_key,
                'org_id'  => $id,
            ];

            $request = Request::create('/api/deleteOrganisation', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->deleteOrganisation($request)->getData();

            if ($result->success) {
                session()->flash('alert-success', __('custom.delete_success'));

                return back();
            }

            session()->flash('alert-danger', __('custom.delete_error'));

            return back();
        }

        return redirect()->back()->with('alert-danger', 'Нямате права за достъп до тази страница');
    }
}
