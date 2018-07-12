<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use \App\Locale;
use \Validator;

class LocaleController extends ApiController
{
    /**
     * Adds a locale based on input data
     *
     * @param Request $request
     * @return json response
     */
    public function addLocale(Request $request)
    {
        $localeData = $request->all();
        $validator = Validator::make($localeData, [
            'data'        => 'required|array',
            'data.locale' => 'required|string',
            'data.active' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Add locale failure');
        }

        $newLocale = new Locale;
        $newLocale->locale = $localeData['data']['locale'];
        $newLocale->active = $localeData['data']['active'];

        try {
            $newLocale->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Locale add failure');
        }
        return $this->successResponse();
    }

    /**
     * Edits a locale based on request data
     *
     * @param Request $request
     * @return json response
     */
    public function editLocale(Request $request)
    {
        $localeEditData = $request->all();

        $locale = \LaravelLocalization::getCurrentLocale();
        $validator = Validator::make($localeEditData, [
            'data'        => 'required|array',
            'locale'      => 'string|required',
            'data.active' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Edit locale failure');
        }

        $localeToEdit = Locale::find($locale);
        $localeToEdit->locale = $locale;
        $localeToEdit->active = $localeEditData['data']['active'];
        try {
            $localeToEdit->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Locale add failure');
        }
        return $this->successResponse();
    }

    /**
     * Deletes a locale based on request data.
     * Locale id is column locale values
     * like 'en', 'es' etc.
     *
     * @param Request $request
     * @return json response
     */
    public function deleteLocale(Request $request)
    {
        $localeDeleteData = $request->all();
        $validator = Validator::make($localeDeleteData, [
            'locale' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete locale failure');
        }

        $locale = \LaravelLocalization::getCurrentLocale();
        $localeToDelete = Locale::find($locale);

        try {
            $localeToDelete->delete();
        } catch (QueryException $e) {
            return $this->errorResponse('Locale add failure');
        }
        return $this->successResponse();
    }

    /**
     * Lists locales based on input criteria
     *
     * @param Request $request
     * @return json response
     */
    public function listLocale(Request $request)
    {
        $localeListData = $request->all();
        $validator = Validator::make($localeListData, [
            'criteria'        => 'array',
            'criteria.active' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List locale failure');
        }

        $result = [];
        $criteria = $request->json('criteria');

        $listLocale = Locale::select(
            'locale',
            'active',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by'
        );

        if (is_null($criteria)) {
            $listLocale = $listLocale;
        }

        if (isset($criteria['active'])) {
            $listLocale = $listLocale->where('active', $criteria['active']);
        }

        $listLocale = $listLocale->get();

        if (!empty($listLocale)) {
            foreach ($listLocale as $singleLocale) {
                $result[] = [
                    'locale'     => $singleLocale->locale,
                    'active'     => $singleLocale->active,
                    'created_at' => date($singleLocale->created_at),
                    'updated_at' => date($singleLocale->updated_at),
                    'created_by' => $singleLocale->created_by,
                    'updated_by' => $singleLocale->updated_by,
                ];
            }
        }
        return $this->successResponse(['locale' => $result], true);
    }

    /**
     * Gets the details for a given locale
     *
     * @param Request $request
     * @return json response
     */
    public function getLocaleDetails(Request $request)
    {
        $localeDetailsData = $request->all();
        $validator = Validator::make($localeDetailsData, [
            'locale' => 'string',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();
        $localeDetails = Locale::where('locale', $locale)->get();

        if (!empty($localeDetails)) {
            foreach ($localeDetails as $singleLocale) {
                $result[] = [
                    'locale'     => $singleLocale->locale,
                    'active'     => $singleLocale->active,
                    'created_at' => date($singleLocale->created_at),
                    'updated_at' => date($singleLocale->updated_at),
                    'created_by' => $singleLocale->created_by,
                    'updated_by' => $singleLocale->updated_by,
                ];
            }
            return $this->successResponse(['locale' => $result], true);
        }
    }
}
