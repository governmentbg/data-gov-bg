<?php

namespace App\Http\Controllers\Api;

use App\Locale;
use App\Module;
use App\Section;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class SectionController extends ApiController
{
    /**
     * API function for creates new section
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param integer data[parent_id] - oprional
     * @param integer data[active] - required
     * @param integer data[ordering] - optional
     * @param integer data[read_only] - optional
     * @param integer data[theme] - optional
     * @param string data[forum_link] - optional
     *
     * @return JsonResponse - with section id or error
     */
    public function addSection(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'name'          => 'required|string|max:191',
            'locale'        => 'required|string|max:5',
            'active'        => 'required|boolean',
            'parent_id'     => 'nullable|integer|exists:sections,id|digits_between:1,10',
            'ordering'      => 'nullable|integer|digits_between:1,3',
            'read_only'     => 'nullable|boolean',
            'theme'         => 'nullable|integer|digits_between:1,3',
            'forum_link'    => 'nullable|string|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $locale = Locale::where('locale', $data['locale'])->value('locale');

            //prepare section data
            $newSection = new Section;
            $newSection->created_by = \Auth::user()->id;

            $newSection->name = $data['name'];
            unset($data['locale']);
            $newSection->fill($data);

            try {
                $newSection->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::SECTIONS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $newSection->id,
                    'action_msg'       => 'Added section',
                ];

                Module::add($logData);

                return $this->successResponse(['id' => $newSection->id], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_section_fail'), $validator->errors()->messages());
    }

    /**
     * API function for editing section records
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param integer data[parent_id] - oprional
     * @param integer data[active] - required
     * @param integer data[ordering] - optional
     * @param integer data[theme] - optional
     * @param string data[forum_link] - optional
     *
     * @return JsonResponse - with success or error
     */
    public function editSection(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'id'                => 'required|numeric|exists:sections,id|digits_between:1,10',
            'data'              => 'required|array'
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'name'         => 'required|string|max:191',
                'locale'       => 'required|string|max:5',
                'active'       => 'required|boolean',
                'parent_id'    => 'nullable|integer|exists:sections,id|digits_between:1,10',
                'ordering'     => 'nullable|integer|digits_between:1,3',
                'read_only'    => 'nullable|boolean',
                'theme'        => 'nullable|integer|digits_between:1,3',
                'forum_link'   => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $data = $request->data;
            $locale = Locale::where('locale', $data['locale'])->value('locale');

            // if request locale not found set default
            if (is_null($locale)) {
                $data['locale'] = config('app.locale');
            }

            //prepare section data
            $section = Section::find($post['id']);

            if ($section) {
                $section->updated_by = \Auth::user()->id;
                $section->name = $data['name'];
                unset($data['locale']);
                $section->fill($data);

                try {
                    $section->save();

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::SECTIONS),
                        'action'           => ActionsHistory::TYPE_MOD,
                        'action_object'    => $section->id,
                        'action_msg'       => 'Edited section',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_section_fail'), $validator->errors()->messages());
    }

    /**
     * API function for deleting section records
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return JsonResponse - with success or error
     */
    public function deleteSection(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['id' => 'required|integer|exists:sections,id|digits_between:1,10']);

        if (!$validator->fails()) {
            if (Section::find($post['id'])->delete()) {
                $logData = [
                    'module_name'      => Module::getModuleName(Module::SECTIONS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['id'],
                    'action_msg'       => 'Deleted section',
                ];

                Module::add($logData);

                return $this->successResponse();
            }
        }

        return $this->errorResponse(__('custom.delete_section_fail'), $validator->errors()->messages());
    }

    /**
     * API function for listing multiple section records
     *
     * @param array criteria - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[id] - optional
     * @param integer criteria[active] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return JsonResponse - with list of sections or error
     */
    public function listSections(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|max:191',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'id'       => 'nullable|integer|digits_between:1,10',
                'active'   => 'nullable|boolean',
                'locale'   => 'nullable|string|max:5',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_sections_fail'), $validator->errors()->messages());
        }

        $query = Section::where('parent_id', null);
        $criteria = [];

        if (isset($post['criteria'])) {
            if (isset($post['criteria']['locale'])) {
                $criteria['locale'] = $post['criteria']['locale'];
            }

            if (isset($post['criteria']['active'])) {
                $criteria['active'] = $post['criteria']['active'];
            }

            if (isset($post['criteria']['id'])) {
                $criteria['id'] = $post['criteria']['id'];
            }
        }

        if (!empty($criteria)) {
            $query->where($criteria);
        }

        $count = $query->count();
        $query->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $result = [];

        foreach ($query->get() as $section) {
            $result[] = [
                'id'            => $section->id,
                'name'          => $section->name,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'parent_id'     => $section->parent_id,
                'active'        => $section->active,
                'ordering'      => $section->ordering,
                'read_only'     => $section->read_only,
                'forum_link'    => $section->forum_link,
                'theme'         => $section->theme,
                'created_at'    => $section->created_at,
                'updated_at'    => $section->updated_at,
                'created_by'    => $section->created_by,
                'updated_by'    => $section->updated_by,
            ];
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::SECTIONS),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed sections',
        ];

        Module::add($logData);

        return $this->successResponse(['sections' => $result, 'total_records' => $count], true);
    }

    /**
     * API function for listing multiple subsection records
     *
     * @param array criteria - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[section_id] - optional
     * @param integer criteria[active] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return JsonResponse - with list of subsections or error
     */
    public function listSubsections(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|max:191',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'id'         => 'nullable|integer|digits_between:1,10',
                'active'     => 'nullable|boolean',
                'locale'     => 'nullable|string|max:5',
                'section_id' => 'nullable|int|digits_between:1,10',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_sections_fail'), $validator->errors()->messages());
        }

        $query = isset($criteria['section_id'])
            ? Section::where('parent_id', $criteria['section_id'])
            : Section::where('parent_id', '!=', null);

        $criteria = [];

        if (isset($post['criteria'])) {
            if (isset($post['criteria']['locale'])) {
                $criteria['locale'] = $post['criteria']['locale'];
            }

            if (isset($post['criteria']['active'])) {
                $criteria['active'] = $post['criteria']['active'];
            }

            if (isset($post['criteria']['id'])) {
                $criteria['id'] = $post['criteria']['id'];
            }
        }

        if (!empty($criteria)) {
            $query->where($criteria);
        }

        $count = $query->count();
        $query->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $result = [];

        foreach ($query->get() as $section) {
            $result[] = [
                'id'            => $section->id,
                'name'          => $section->name,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'parent_id'     => $section->parent_id,
                'active'        => $section->active,
                'ordering'      => $section->ordering,
                'read_only'     => $section->read_only,
                'forum_link'    => $section->forum_link,
                'theme'         => $section->theme,
                'created_at'    => $section->created_at,
                'updated_at'    => $section->updated_at,
                'created_by'    => $section->created_by,
                'updated_by'    => $section->updated_by,
            ];
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::SECTIONS),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed sub section',
        ];

        Module::add($logData);

        return $this->successResponse(['subsections' => $result, 'total_records' => $count], true);
    }

    public function isParent(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['id' => 'required|int|exists:sections,id|digits_between:1,10']);

        if (!$validator->fails()) {
            $section = Section::find($post['id']);

            if ($section) {
                if (Section::where('parent_id', $section->id)->count()) {

                    return $this->successResponse(['data' => true], true);
                }

                return $this->successResponse(['data' => false], true);
            }
        }

        return $this->errorResponse(__('custom.error'), $validator->errors()->messages());
    }
}
