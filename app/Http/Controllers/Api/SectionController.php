<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use App\Section;
use App\Locale;

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
     *
     * @return JsonResponse - with list of sections or error
     */
    public function listSections(Request $request)
    {
        $sectionModel = new Section;
        $post = $request->criteria;

        if (!empty($post)) {
            $validator = \Validator::make($request->all(), [
                'criteria'          => 'nullable|array'
            ]);

            if (!$validator->fails()) {
                $validator = \Validator::make($request['criteria'], [
                    'id'       => 'nullable|integer|digits_between:1,10',
                    'active'   => 'nullable|boolean',
                    'locale'   => 'nullable|string|max:5',
                ]);
            }

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.list_sections_fail'), $validator->errors()->messages());
            }

            $criteria['locale'] = $request->filled('criteria.locale')
                    ? $request->input('criteria.locale')
                    : config('app.locale');

            if ($request->filled('criteria.active')) {
                $criteria['active'] = $request->input('criteria.active');
            }
        } else {
            $criteria['locale'] = config('app.locale');
        }

        $sections = $sectionModel->listSections($criteria);
        $response = $this->prepareSections($sections);

        return $this->successResponse($response);
    }

    /**
     * API function for listing multiple subsection records
     *
     * @param array criteria - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[section_id] - optional
     * @param integer criteria[active] - optional
     *
     * @return JsonResponse - with list of subsections or error
     */
    public function listSubsections(Request $request)
    {
        $sectionModel = new Section;
        $post = $request->criteria;

        if (!empty($post)) {
            $validator = \Validator::make($request->all(), [
                'criteria'   => 'nullable|array',
            ]);

            if (!$validator->fails()) {
                $validator = \Validator::make($post, [
                    'section_id'   => 'nullable|integer|digits_between:1,10',
                    'active'       => 'nullable|boolean',
                    'locale'       => 'nullable|string|max:5',
                ]);
            }

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.list_subsections_fail'), $validator->errors()->messages());
            }

            $criteria['locale'] = $request->filled('criteria.locale')
                    ? $request->input('criteria.locale')
                    : config('app.locale');

            if ($request->filled('criteria.active')) {
                $criteria['active'] = $request->input('criteria.active');
            }

            if ($request->filled('criteria.section_id')) {
                $criteria['parent_id'] = $request->input('criteria.section_id');
            }

            $sections = $sectionModel->listSubsections($criteria);
        } else {
            $criteria['locale'] = config('app.locale');
            $sections = $sectionModel->listSubsections($criteria);
        }

        $response = $this->prepareSections($sections);

        return $this->successResponse($response);
    }

    /**
     * Helper function for listing APIs - preparing section records for response
     *
     * @param Collection $sections - collection of Section records
     * @return array - Section records data prepared for response
     */
    private function prepareSections($sections)
    {
        $result = [];

        foreach ($sections as $section) {
            $result[] = [
                'id'            => $section->id,
                'name'          => $section->label,
                'locale'        => $section->locale,
                'parent_id'     => $section->parent_id,
                'active'        => $section->active,
                'ordering'      => $section->ordering,
                'read_only'     => $section->read_only,
                'forum_link'    => $section->forum_link,
                'theme'         => $section->theme,
                'created_at'    => $section->created_at,
                'updated_at'   => $section->updated_at,
                'created_by'   => $section->created_by,
                'updated_by'    => $section->updated_by,
            ];
        }

        return $result;
    }
}
