<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use App\Module;
use App\Section;
use App\RoleRight;
use Illuminate\Http\Request;
use DevDojo\Chatter\Models\Models;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Api\ThemeController as ApiTheme;
use DevDojo\Chatter\Controllers\ChatterDiscussionController;
use App\Http\Controllers\Api\SectionController as ApiSection;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get array with results for current page and paginator
     *
     * @param array result
     * @param integer totalCount
     * @param array params - array with GET parameters
     * @param integer perPage
     *
     * @return array with results for the current page and paginator object
     */
    public function getPaginationData($result = [], $totalCount = 0, $params = [], $perPage = 1, $pageName = 'page')
    {
        $path = !empty($params)
            ? LengthAwarePaginator::resolveCurrentPath() .'?'. http_build_query($params)
            : LengthAwarePaginator::resolveCurrentPath();

        $options = ['path' => $path, 'pageName' => $pageName];

        $paginator = new LengthAwarePaginator(
            $result,
            $totalCount,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage($pageName),
            $options
        );

        return [
            'items'    => $result,
            'paginate' => $paginator,
        ];
    }

    /**
     * Get image data
     *
     * @param binary $data
     * @param string $mime
     *
     * @return string
     */
    protected function getImageData($data, $mime, $type = 'org')
    {
        if (empty($data) || empty($mime)) {
            return asset('img/default-'. $type .'.svg');
        }

        return 'data:'. $mime .';base64,'. base64_encode($data);
    }

    /**
     * Returns model with usernames instead of user ids for record signatures
     *
     * @param Model $model
     *
     * @return view
     */
    public function getModelUsernames($model) {
        if (isset($model)) {
            if (is_object($model)) {
                if (
                    $model->updated_by == $model->created_by
                    && !is_null($model->created_by)
                ) {
                    $user = User::find($model->created_by);
                    $username = !empty($user) ? $user->username : null;
                    $model->updated_by = $username;
                    $model->created_by = $username;
                } else {
                    $user = is_null($model->updated_by) ? null : User::find($model->updated_by);
                    $model->updated_by = empty($user) ? null : $user->username;
                    $user = is_null($model->created_by) ? null : User::find($model->created_by);
                    $model->created_by = empty($user) ? null : $user->username;
                }
            } elseif (is_array($model)) {
                $storage = [];

                foreach ($model as $key => $item) {
                    $createdId = $item->created_by;
                    $updatedId = $item->updated_by;

                    if (
                        $item->updated_by == $item->created_by
                        && !is_null($item->created_by)
                    ) {
                        if (!empty($storage[$item->created_by])) {
                            $model[$key]->updated_by = $storage[$item->created_by];
                            $model[$key]->created_by = $storage[$item->created_by];
                        } else {
                            $user = User::find($item->created_by);
                            $username = !empty($user) ? $user->username : null;
                            $model[$key]->updated_by = $username;
                            $model[$key]->created_by = $username;
                            $storage[$createdId] = $username;
                        }
                    } else {
                        if (!empty($storage[$item->created_by])) {
                            $model[$key]->created_by = $storage[$item->created_by];
                        } else {
                            $user = is_null($item->created_by) ? null : User::find($item->created_by);
                            $username = empty($user) ? null : $user->username;
                            $model[$key]->created_by = $username;

                            if (!is_null($username)) {
                                $storage[$createdId] = $username;
                            }
                        }

                        if (!empty($storage[$item->updated_by])) {
                            $model[$key]->updated_by = $storage[$item->updated_by];
                        } else {
                            $user = is_null($item->updated_by) ? null : User::find($item->updated_by);
                            $username = empty($user) ? null : $user->username;
                            $model[$key]->updated_by = $username;

                            if (!is_null($username)) {
                                $storage[$updatedId] = $username;
                            }
                        }
                    }
                }
            }
        }

        return $model;
    }

    /**
     * Prepares an array of categories
     *
     * @return array categories
     */
    public function prepareMainCategories()
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['active'] = 1;
        $request = Request::create('/api/listMainCategories', 'POST', $params);
        $api = new ApiCategory($request);
        $result = $api->listMainCategories($request)->getData();
        $categories = [];

        foreach ($result->categories as $row) {
            $categories[$row->id] = $row->name;
        }

        return $categories;
    }

    /**
     * Prepares an array of terms of use
     *
     * @return array termsOfUse
     */
    protected function prepareTermsOfUse()
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['active'] = 1;
        $request = Request::create('/api/listTermsOfUse', 'POST', $params);
        $api = new ApiTermsOfUse($request);
        $result = $api->listTermsOfUse($request)->getData();
        $termsOfUse = [];

        if (isset($result->terms_of_use)) {
            foreach ($result->terms_of_use as $row) {
                $termsOfUse[$row->id] = $row->name;
            }
        }

        return $termsOfUse;
    }

    public function prepareTags($data)
    {
        if (isset($data['tags'])) {
            $data['tags'] = array_values(explode(',', $data['tags']));
        }

        return $data;
    }

    /**
     * Prepares an array of organisations
     *
     * @return array organisations
     */
    protected function prepareOrganisations()
    {
        $params = [];

        $params['api_key'] = \Auth::user()->api_key;

        if (!Role::isAdmin()) {
            $params['criteria']['user_id'] = \Auth::user()->id;
        }

        $request = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = [];

        foreach ($result->organisations as $row) {
            $organisations[$row->id] = $row->name;
        }

        return $organisations;
    }

    /**
     * Prepares an array of groups
     *
     * @return array groups
     */
    protected function prepareGroups()
    {
        $params = [];

        if (!Role::isAdmin()) {
            $params['criteria']['user_id'] = \Auth::user()->id;
        }

        $request = Request::create('/api/listGroups', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listGroups($request)->getData();
        $groups = [];

        foreach ($result->groups as $row) {
            $check = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'group_id' => $row->id
                    ],
                    [
                        'group_ids' => [$row->id]
                    ]
                );

            if ($check) {
                $groups[$row->id] = $row->name;
            }
        }

        return $groups;
    }

    /**
     * Attempts to delete a dataset based on uri
     *
     * @param Request $request
     * @return true on success and false on failure
     *
     */
    protected function datasetDelete($uri)
    {
        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $uri]);
        $apiDatasets = new ApiDataset($datasetReq);
        $dataset = $apiDatasets->getDatasetDetails($datasetReq)->getData();
        $datasetData = !empty($dataset->data) ? $dataset->data : null;

        if (!isset($datasetData)) {
            return back();
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_ALL,
            [],
            [
                'created_by' => $datasetData->created_by
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $params['api_key'] = \Auth::user()->api_key;
        $params['dataset_uri'] = $uri;

        $request = Request::create('/api/deleteDataset', 'POST', $params);
        $api = new ApiDataSet($request);
        $datasets = $api->deleteDataset($request)->getData();

        return $datasets->success;
    }

    /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    protected function getTransFields()
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
     * Function for getting an array of translatable fields for datasets
     *
     * @return array of fields
     */
    protected function getDatasetTransFields()
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
                'name'     => 'description',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.sla_agreement',
                'name'     => 'sla',
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
     * Function for getting an array of translatable fields for groups
     *
     * @return array of fields
     */
    protected function getGroupTransFields()
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
     * Function for getting an array of translatable fields for resources
     *
     * @return array of fields
     */
    protected function getResourceTransFields()
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

    public function getActiveSections()
    {
        $criteria = ['criteria' => ['active' => 1, 'order' => ['type' => 'asc', 'field' => 'ordering']]];
        $rq = Request::create('/api/listSections', 'POST', $criteria);
        $api = new ApiSection($rq);
        $result = $api->listSections($rq)->getData();
        $sections = !empty($result->sections) ? $result->sections : [];
        $themeClasses = ApiTheme::getThemeClasses();

        foreach ($sections as $key => $section) {
            $sections[$key]->class = isset($themeClasses[$section->theme])
                ? $themeClasses[$section->theme]
                : null;
        }

        return $sections;
    }

    public function getForumDiscussion($link)
    {
        if ($link) {
            $segments = explode('/', parse_url($link)['path']);

            if (count($segments) > 2) {
                $discSlug = $segments[count($segments) - 1];
                $categorySlug = $segments[count($segments) - 2];

                $disc = Models::discussion()->where('slug', $discSlug)->count();
                $discCategory = Models::category()->where('slug', $categorySlug)->count();

                if ($disc && $discCategory) {
                    $rq = Request::create($link, 'GET', []);
                    $chatterDisc = new ChatterDiscussionController($rq);
                    $discussion = $chatterDisc->show($categorySlug, $discSlug)->getData();

                    return $discussion;
                }
            }
        }

        return [];
    }
}
