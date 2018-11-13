<?php

namespace App\Http\Controllers\Api;

use App\Module;
use App\RoleRight;
use App\HelpPage;
use App\HelpSection;
use App\ActionsHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class HelpController extends ApiController
{

    /**
     * Add a new Help Section
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param boolean data[active] - required
     * @param integer data[oredring] - optional
     * @param integer data[parent_id] - optional
     *
     * @return response with new id or error message
     */
    public function addHelpSection(Request $request)
    {
        $validator = \Validator::make($request->all(), ['data' => 'required|array']);

        if(!$validator->fails()) {
            $data = $request->data;

            $validator = \Validator::make($data, [
                'name'      => 'required|string|unique:help_sections|max:191',
                'title'     => 'required_with:locale|max:191',
                'title.bg'  => 'required_without:locale|string|max:191',
                'title.*'   => 'max:191',
                'locale'    => 'nullable|string|max:5',
                'parent_id' => 'nullable|exists:help_sections,id',
                'active'    => 'nullable|int',
                'ordering'  => 'nullable|int'
            ]);

            if (!$validator->fails()) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::HELP_SECTIONS,
                    RoleRight::RIGHT_EDIT
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $helpSection = new HelpSection;

                $helpSection->name = $data['name'];
                $helpSection->title = $this->trans($data['locale'], $data['title']);
                $helpSection->active = boolval($data['active']);

                if (!empty($data['parent_id'])) {
                    $helpSection->parent_id = $data['parent_id'];
                }

                if (!empty($data['ordering'])) {
                    $helpSection->ordering = $data['ordering'];
                }

                try {
                    $helpSection->save();

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::HELP_SECTIONS),
                        'action'           => ActionsHistory::TYPE_ADD,
                        'action_object'    => $helpSection->id,
                        'action_msg'       => 'Added help section',
                    ];

                    Module::add($logData);

                    return $this->successResponse(['id' => $helpSection->id], true);
                } catch (\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.add_help_section_fail'), $validator->errors()->messages());
    }

    /**
     * Edit Help Section
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param boolean data[active] - required
     * @param integer data[oredring] - optional
     *
     * @return response with success or error message
     */
    public function editHelpSection(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'data' => 'required|array',
            'id'   => 'required|int|exists:help_sections,id'
        ]);

        if(!$validator->fails()) {
            $data = $request->data;

            $validator = \Validator::make($data, [
                'name'      => 'required|string|unique:help_pages|max:191',
                'title'     => 'required_with:locale|max:191',
                'title.bg'  => 'required_without:locale|string|max:191',
                'title.*'   => 'max:191',
                'locale'    => 'nullable|string|max:5',
                'parent_id' => 'nullable|exists:help_sections,id',
                'active'    => 'required|boolean',
                'ordering'  => 'nullable|int'
            ]);

            if (!$validator->fails()) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::HELP_SECTIONS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => \Auth::user()->id
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $section = HelpSection::find($request->id);

                $section->name = $data['name'];
                $section->title = $this->trans($data['locale'], $data['title']);
                $section->active = boolval($data['active']);

                if (!empty($data['parent_id'])) {
                    $section->parent_id = $data['parent_id'];
                }

                if (!empty($data['ordering'])) {
                    $section->ordering = $data['ordering'];
                }

                try {
                    $section->save();

                    $logData = [
                        'module_name'   => Module::getModuleName(Module::HELP_SECTIONS),
                        'action'        => ActionsHistory::TYPE_MOD,
                        'action_object' => $section->id,
                        'action_msg'    => 'Edited help section',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                } catch (\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_help_section_fail'), $validator->errors()->messages());
    }

    /**
     * Delete Help Section
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return response with success or error message
     */
    public function deleteHelpSection(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int|exists:help_sections,id']);

        if (!$validator->fails()) {
            $section = HelpSection::find($request->id);
            $rightCheck = RoleRight::checkUserRight(
                Module::HELP_SECTIONS,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $section->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $section->delete();

                $logData = [
                    'module_name'   => Module::getModuleName(Module::HELP_SECTIONS),
                    'action'        => ActionsHistory::TYPE_DEL,
                    'action_object' => $request->id,
                    'action_msg'    => 'Deleted help section ',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_help_section_fail'), $validator->errors()->messages());
    }

    /**
     * List Help Sections by criteria
     *
     * @param string api_key - optional
     * @param array criteria - optional
     * @param integer criteria[id] - optional
     * @param string criteria[locale] - required
     * @param boolean criteria[active] - required
     *
     * @return response with list of sections or error message
     */
    public function listHelpSections(Request $request)
    {
        $results = [];
        $criteria = !empty($request->criteria) ? $request->criteria : [];

        if (!empty($criteria)) {
            $validator = \Validator::make($criteria, [
                'id'        => 'nullable|int|exists:help_sections,id',
                'locale'    => 'nullable|string|max:5',
                'active'    => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.list_help_section_fail'), $validator->errors()->messages());
            }
        }

        if (!isset($request->api_key)) {
            $criteria['active'] = true;
        }

        $helpSections = HelpSection::select()->where('parent_id', null);

        if (isset($criteria['active'])) {
            $helpSections->where('active', $criteria['active']);
        }

        if (isset($criteria['id'])) {
            $helpSections->where('id', $criteria['id']);
        }

        $sections = $helpSections->get();

        foreach ($sections as $section) {
            $results[] = [
                'id'            => $section->id,
                'name'          => $section->name,
                'title'         => $section->title,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'parent_id'     => $section->parent_id,
                'ordering'      => $section->ordering,
                'active'        => $section->active,
                'created_by'    => $section->created_by,
                'created_at'    => $section->created_at->toDateTimeString(),
                'updated_by'    => $section->updated_by,
                'updated_at'    => isset($section->updated_at) ? $section->updated_at->toDateTimeString() : null,
            ];
        }

        return $this->successResponse(['sections' => $results], true);
    }

    /**
     * List Subsections by criteria
     *
     * @param string api_key - optional
     * @param array criteria - optional
     * @param string criteria[locale] - required
     * @param boolean criteria[active] - required
     * @param integer criteria[section_id] - optional
     *
     * @return response with list of subsections or error message
     */
    public function listHelpSubsections(Request $request)
    {
        $results = [];
        $criteria = !empty($request->criteria) ? $request->criteria : [];

        if (!empty($criteria)) {
            $validator = \Validator::make($criteria, [
                'locale'        => 'nullable|string|max:5',
                'active'        => 'nullable|boolean',
                'section_id'    => 'nullable|int|exists:help_sections,id',
                'id'            => 'nullable|int|exists:help_sections,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.list_help_section_fail'), $validator->errors()->messages());
            }
        }

        if (!isset($request->api_key)) {
            $criteria['active'] = true;
        }

        $subsections = HelpSection::select()->where('parent_id', '!=', null);

        if (isset($criteria['id'])) {
            $subsections->where('id', $criteria['id']);
        }

        if (isset($criteria['active'])) {
            $subsections->where('active', $criteria['active']);
        }

        if (isset($criteria['section_id'])) {
            $subsections->where('parent_id', $criteria['section_id']);
        }

        $sections = $subsections->get();

        foreach ($sections as $section) {
            $results[] = [
                'id'            => $section->id,
                'name'          => $section->name,
                'title'         => $section->title,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'parent_id'     => $section->parent_id,
                'ordering'      => $section->ordering,
                'active'        => $section->active,
                'created_by'    => $section->created_by,
                'created_at'    => $section->created_at->toDateTimeString(),
                'updated_by'    => $section->updated_by,
                'updated_at'    => isset($section->updated_at) ? $section->updated_at->toDateTimeString() : null,
            ];
        }

        return $this->successResponse(['subsections' => $results], true);
    }

    /**
     * Add a new Help Page
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param string data[keywords] - optional
     * @param string data[title] - required
     * @param string data[body] - required
     * @param boolean data[active] - required
     * @param integer data[section_id] - optional
     * @param integer data[oredring] - optional
     *
     * @return response with new id or error message
     */
    public function addHelpPage(Request $request)
    {
        $validator = \Validator::make($request->all(), ['data' => 'required|array']);

        if(!$validator->fails()) {
            $data = $request->data;

            $validator = \Validator::make($data, [
                'section_id'    => 'nullable|exists:help_sections,id',
                'name'          => 'required|string|unique:help_pages|max:191',
                'keywords'      => 'nullable|string|max:191',
                'locale'        => 'nullable|string|max:5',
                'title'         => 'required_with:locale|max:191',
                'title.bg'      => 'required_without:locale|string|max:191',
                'title.*'       => 'max:191',
                'body'          => 'required_with:locale|max:8000',
                'body.bg'       => 'required_without:locale|string|max:8000',
                'body.*'        => 'max:8000',
                'ordering'      => 'nullable|int',
                'active'        => 'required|boolean',
            ]);

            if (!$validator->fails()) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::HELP_PAGES,
                    RoleRight::RIGHT_EDIT
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $helpPage = new HelpPage;

                $helpPage->name = $data['name'];
                $helpPage->title = $this->trans($data['locale'], $data['title']);
                $helpPage->body = $this->trans($data['locale'], $data['body']);
                $helpPage->active = boolval($data['active']);

                if (!empty($data['keywords'])) {
                    $helpPage->keywords = $data['keywords'];
                }

                if (!empty($data['section_id'])) {
                    $helpPage->section_id = $data['section_id'];
                }

                if (!empty($data['ordering'])) {
                    $helpPage->ordering = $data['ordering'];
                }

                try {
                    $helpPage->save();

                    $logData = [
                        'module_name'   => Module::getModuleName(Module::HELP_PAGES),
                        'action'        => ActionsHistory::TYPE_ADD,
                        'action_object' => $helpPage->id,
                        'action_msg'    => 'Added help page',
                    ];

                    Module::add($logData);

                    return $this->successResponse(['id' => $helpPage->id], true);
                } catch (\QueryException $ex) {
                    Log::error($ex->getMessage());
                }

            }
        }

        return $this->errorResponse(__('custom.add_help_page_fail'), $validator->errors()->messages());
    }

     /**
     * Edit Help Page
     *
     * @param string api_key - required
     * @param integer page_id - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param string data[title] - required
     * @param string data[body] - required
     * @param string data[keywords] - optional
     * @param integer data[section_id] - optional
     * @param boolean data[active] - required
     * @param integer data[oredring] - optional
     *
     * @return response with success or error message
     */
    public function editHelpPage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'data'      => 'required|array',
            'page_id'   => 'required|int|exists:help_pages,id'
        ]);

        if(!$validator->fails()) {
            $data = $request->data;

            $validator = \Validator::make($data, [
                'name'          => 'required|string|max:191',
                'section_id'    => 'nullable|exists:help_sections,id',
                'keywords'      => 'nullable|string|max:191',
                'locale'        => 'nullable|string|max:5',
                'title'         => 'required_with:locale|max:191',
                'title.bg'      => 'required_without:locale|string|max:191',
                'title.*'       => 'max:191',
                'body'          => 'required_with:locale|max:8000',
                'body.bg'       => 'required_without:locale|string|max:8000',
                'body.*'        => 'max:8000',
                'ordering'      => 'nullable|int',
                'active'        => 'required|boolean',
            ]);

            if (!$validator->fails()) {
                $helpPage = HelpPage::find($request->page_id);
                $rightCheck = RoleRight::checkUserRight(
                    Module::HELP_PAGES,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $helpPage->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                if ($helpPage->name != $data['name']) {
                    if (!HelpPage::where('name', $data['name'])->count()) {
                        $helpPage->name = $data['name'];
                    } else {
                        return $this->errorResponse(__('custom.edit_help_page_fail'), ['name' => [__('custom.name_exists')]]);
                    }
                }

                $helpPage->title = $this->trans($data['locale'], $data['title']);
                $helpPage->body = $this->trans($data['locale'], $data['body']);
                $helpPage->active = boolval($data['active']);

                if (!empty($data['keywords'])) {
                    $helpPage->keywords = $data['keywords'];
                }

                if (!empty($data['section_id'])) {
                    $helpPage->section_id = $data['section_id'];
                }

                if (!empty($data['ordering'])) {
                    $helpPage->ordering = $data['ordering'];
                }
                try {
                    $helpPage->save();

                    $logData = [
                        'module_name'   => Module::getModuleName(Module::HELP_PAGES),
                        'action'        => ActionsHistory::TYPE_MOD,
                        'action_object' => $helpPage->id,
                        'action_msg'    => 'Added help page',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                } catch (\QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_help_page_fail'), $validator->errors()->messages());
    }

    /**
     * Delete Help Page
     *
     * @param string api_key - required
     * @param integer page_id - required
     *
     * @return response with success or error message
     */
    public function deleteHelpPage(Request $request)
    {
        $validator = \Validator::make($request->all(), ['page_id' => 'required|int|exists:help_pages,id']);

        if (!$validator->fails()) {
            $page = HelpPage::find($request->page_id);
            $rightCheck = RoleRight::checkUserRight(
                Module::HELP_PAGES,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $page->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $page->delete();

                $logData = [
                    'module_name'   => Module::getModuleName(Module::HELP_PAGES),
                    'action'        => ActionsHistory::TYPE_DEL,
                    'action_object' => $request->page_id,
                    'action_msg'    => 'Deleted help page',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_help_page_fail'), $validator->errors()->messages());
    }

    /**
     * List Help Pages by criteria
     *
     * @param integer page_number - optional
     * @param integer records_per_page - optional
     * @param array criteria - optional
     * @param string criteria[locale] - optional
     * @param string criteria[keywords] - optional
     * @param boolean criteria[active] - optional
     * @param integer criteria[section_id] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][field] - optional
     * @param string criteria[order][type] - optional
     *
     * @return response with list of sections or error message
     */
    public function listHelpPages(Request $request)
    {
        $results = [];
        $criteria = $request->offsetGet('criteria');

        if (!empty($criteria)) {
            $validator = \Validator::make($criteria, [
                'locale'        => 'nullable|string|max:5',
                'keywords'      => 'nullable|string',
                'active'        => 'nullable|boolean',
                'section_id'    => 'nullable|int|exists:help_sections,id',
                'order'         => 'nullable|array',
            ]);

            if (!$validator->fails()) {
                $order = isset($criteria['order']) ? $criteria['order'] : [];
                $validator = \Validator::make($order, [
                    'type'   => 'nullable|string|max:191',
                    'field'  => 'nullable|string|max:191',
                ]);
            } else {
                return $this->errorResponse(__('custom.lsit_help_page_fail'), $validator->errors()->messages());
            }
        }

        if (!empty($criteria['keywords'])) {
            $ids = HelpPage::search($criteria['keywords'])->get()->pluck('id');
            $pages = HelpPage::whereIn('id', $ids)->with('section');
        } else {
            $pages = HelpPage::select()->with('section');
        }

        if (!empty($criteria['active'])) {
            $pages->where('active', $criteria['active']);
        }

        if (!empty($criteria['section_id'])) {
            $pages->where('section_id', $criteria['section_id']);
        }

        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'desc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'created_at';

        $columns = [
            'id',
            'name',
            'keywords',
            'title',
            'body',
            'active',
            'ordering',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        if (isset($criteria['order']['field'])) {
            if (!in_array($criteria['order']['field'], $columns)) {
                return $this->errorResponse(__('custom.invalid_sort_field'));
            }
        }

        $pages->orderBy($order['field'], $order['type']);

        $count = $pages->count();

        $pages->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $pagesList = $pages->get();

        foreach($pagesList as $page) {
            $results[] = [
                'id'            => $page->id,
                'name'          => $page->name,
                'keywords'      => $page->keywords,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'section_id'    => $page->section_id,
                'title'         => $page->title,
                'body'          => $page->body,
                'active'        => $page->active,
                'ordering'      => $page->ordering,
                'created_by'    => $page->created_by,
                'created_at'    => $page->created_at->toDateTimeString(),
                'updated_by'    => $page->updated_by,
                'updated_at'    => isset($page->updated_at) ? $page->updated_at->toDateTimeString() : null,
                'section_name'  => isset($page->section->title) ? $page->section->title : null,
            ];
        }

        return $this->successResponse(['total_records' => $count, 'pages' => $results], true);
    }

    /**
     * Get Help Page details
     *
     * @param string criteria[locale] - optional
     * @param integer criteria[page_id] - optional
     * @param string criteria[name] - optional
     *
     * @return response with list of sections or error message
     */
    public function getHelpPageDetails(Request $request)
    {
        $result = [];
        $criteria = $request->all();

        $validator = \Validator::make($criteria, [
            'locale'    => 'nullable|string|max:5',
            'page_id'   => 'required_without:name|int|exists:help_pages,id',
            'name'      => 'required_without:page_id|string|max:191|exists:help_pages,name',
        ]);

        if (!$validator->fails()) {
            if (!empty($criteria['page_id'])) {
                $page = HelpPage::with('section')->find($criteria['page_id']);
            } else {
                $page = HelpPage::with('section')->where('name', $criteria['name'])->first();
            }

            if (!empty($page)) {
                $result = [
                    'id'            => $page->id,
                    'name'          => $page->name,
                    'keywords'      => $page->keywords,
                    'locale'        => \LaravelLocalization::getCurrentLocale(),
                    'section_id'    => $page->section_id,
                    'title'         => $page->title,
                    'body'          => $page->body,
                    'active'        => $page->active,
                    'ordering'      => $page->ordering,
                    'created_by'    => $page->created_by,
                    'created_at'    => $page->created_at->toDateTimeString(),
                    'updated_by'    => $page->updated_by,
                    'updated_at'    => isset($page->updated_at) ? $page->updated_at->toDateTimeString() : null,
                    'section_name'  => isset($page->section->name) ? $page->section->name : null,
                ];

                return $this->successResponse(['page' => $result], true);
            }
        }

        return $this->errorResponse(__('custom.lsit_help_page_fail'), $validator->errors()->messages());
    }

    /**
     * Check if a Help section is a parent
     *
     * @param string criteria[api_key] - required
     * @param integer criteria[id] - required
     *
     * @return response with list of sections or error message
     */
    public function isParent(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int|exists:help_sections,id']);

        if (!$validator->fails()) {
            $parent = HelpSection::where('parent_id', $request->id)->count();

            return $this->successResponse(['is_parent' => boolval($parent)], true);
        }

        return $this->errorResponse(__('custom.error'), $validator->errors()->messages());
    }
}
