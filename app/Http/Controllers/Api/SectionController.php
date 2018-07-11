<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use App\Section;
use App\Locale;

class SectionController extends ApiController
{
    /**
     * API function for creates new section
     * Route::post('section/addSection', 'Api\SectionController@addSection');
     *
     * @param Request $request - JSON containing api_key (string), data (object) containing new section data
     * @return JsonResponse - JSON containing: On success - Status 200, ID of new sections / On fail - Status 500 error message
     */
    public function addSection(Request $request)
    {
        $data = $request->json('data');
        $validator = \Validator::make($data, [
            'name'      => 'required',
            'locale'    => 'required|max:5',
            'active'    => 'required|boolean',
            'parent_id' => 'exists:sections,id',
        ]);

        if ($validator->fails()) {
            // TODO add graylog log
            return $this->errorResponse('Add section failure');
        }

        $locale = Locale::where('locale', $data['locale'])->value('locale');

        // if request locale not found set default
        if (is_null($locale)) {
            $data['locale'] = config('app.locale');
        }

        //prepare section data
        $newSection = new Section;
        $newSection->created_by = \Auth::user()->id;

        $newSection->name = [$data['locale'] => $data['name']];
        unset($data['locale']);
        $newSection->fill($data);

        try {
            $newSection->save();
        } catch (QueryException $ex) {
            // TODO add graylog log
            //return $this->errorResponse($ex->getMessage());
            return $this->errorResponse('Add section failure');
        }

        return $this->successResponse(['id' => $newSection->id], true);
    }

    /**
     * API function for editing section records
     * Route::post('section/editSection', 'Api\SectionController@editSection');
     *
     * @param Request $request - JSON containing api_key (string), id of edited section, data (object) containing updated section data
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function editSection(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'id'                => 'required|numeric|exists:sections,id',
            'data.name'         => 'required',
            'data.locale'       => 'required|max:5',
            'data.active'       => 'required|boolean',
            'data.parent_id'    => 'exists:sections,id',
        ]);

        if ($validator->fails()) {
            // TODO add graylog log
            return $this->errorResponse('Edit section failure');
        }

        $data = $request->json('data');
        $locale = Locale::where('locale', $data['locale'])->value('locale');

        // if request locale not found set default
        if (is_null($locale)) {
            $data['locale'] = config('app.locale');
        }

        //prepare section data
        $section = Section::find($post['id']);

        try {
            $section->updated_by = \Auth::user()->id;
            $section->name = [$data['locale'] => $data['name']];
            unset($data['locale']);
            $section->fill($data);

            $section->save();
        } catch (QueryException $ex) {
            // TODO add graylog log
            //return $this->errorResponse($ex->getMessage());
            return $this->errorResponse('Edit section failure');
        }
    }

    /**
     * API function for deleting section records
     * Route::post('section/deleteSection', 'Api\SectionController@deleteSection');
     *
     * @param Request $request - JSON containing api_key (string), id of section to be deleted
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function deleteSection(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'id'    => 'required|exists:sections,id'
        ]);

        if ($validator->fails()) {
            // TODO add graylog log
            return $this->errorResponse('Delete section failure');
        }

        if (Section::find($post['id'])->delete()) {
            return $this->successResponse();
        } else {
            // TODO add graylog log
            return $this->errorResponse('Delete section failure');
        }
    }

    /**
     * API function for listing multiple section records
     * Route::post('section/listSections', 'Api\SectionController@listSections');
     *
     * @param Request $request - JSON containing api_key (string), criteria (object) containing filtering criteria for section records
     * @return JsonResponse - JSON containing: On success - Status 200 list of sections / On fail - Status 500 error message
     */
    public function listSections(Request $request)
    {
        $sectionModel = new Section;
        $post = $request->criteria;

        if (!empty($post)) {
            $validator = \Validator::make($request->all(), [
                'active'    => 'boolean',
                'locale'    => 'max:5',
            ]);

            if ($validator->fails()) {
                // TODO add graylog log
                return $this->errorResponse('List sections failure');
            }

            $criteria['locale'] = $request->filled('criteria.locale')
                    ? $request->input('criteria.locale')
                    : config('app.locale');

            if ($request->filled('criteria.active')) {
                $criteria['active'] = $request->input('criteria.active');
            }

            $sections = $sectionModel->listSections($criteria);
        } else {
            $criteria['locale'] = config('app.locale');
            $sections = $sectionModel->listSections($criteria);
        }

        $response = $this->prepareSections($sections);

        return $this->successResponse($response);
    }

    /**
     * API function for listing multiple subsection records
     * Route::post('section/listSubsections', 'Api\SectionController@listSubsections');
     *
     * @param Request $request - JSON containing api_key (string), criteria (object) containing filtering criteria for section records
     * @return JsonResponse - JSON containing: On success - Status 200 list of subsections / On fail - Status 500 error message
     */
    public function listSubsections(Request $request)
    {
        $sectionModel = new Section;
        $post = $request->criteria;

        if (!empty($post)) {
            $validator = \Validator::make($request->all(), [
                'active'    => 'boolean',
                'locale'    => 'max:5',
            ]);

            if ($validator->fails()) {
                // TODO add graylog log
                return $this->errorResponse('List sections failure');
            }

            $criteria['locale'] = $request->filled('criteria.locale')
                    ? $request->input('criteria.locale')
                    : config('app.locale');

            if ($request->filled('criteria.active')) {
                $criteria['active'] = $request->input('criteria.active');
            }

            if ($request->filled('criteria.sectionid')) {
                $criteria['parent_id'] = $request->input('criteria.sectionid');
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
                'updated_at,'   => $section->updated_at,
                'created_by,'   => $section->created_by,
                'updated_by'    => $section->updated_by,
            ];
        }

        return $result;
    }
}