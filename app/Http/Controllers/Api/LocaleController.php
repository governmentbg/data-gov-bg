<?php

namespace App\Http\Controllers\Api;

use \Validator;
use \App\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class LocaleController extends ApiController
{
    /**
     * Adds a locale based on input data
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[locale] - required
     * @param bool data[active] - required
     *
     * @return json with success or error
     */
    public function addLocale(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post['data'], [
            'locale'   => 'required|string|max:5|unique:locale,locale',
            'active'   => 'required|bool',
        ]);

        if (!$validator->fails()) {
            $locale = new Locale;

            $locale->locale = $post['data']['locale'];
            $locale->active = $post['data']['active'];

            try {
                $locale->save();

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_locale_fail'), $validator->errors()->messages());
    }

    /**
     * Edit locale based on request data
     *
     * @param string api_key - required
     * @param string locale - required
     * @param array data - required
     * @param bool data[active] - required
     *
     * @return json with success or error
     */
    public function editLocale(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'locale'        => 'required|string|max:5|exists:locale,locale',
            'data'          => 'required|array',
            'data.active'   => 'required|bool',
        ]);

        if (!$validator->fails()) {
            $locale = Locale::find($post['locale']);

            if ($locale->locale == \LaravelLocalization::getDefaultLocale()) {
                return $this->errorResponse(__('custom.default_locale_error'));
            }

            $locale->active = $post['data']['active'];

            try {
                $locale->save();

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_locale_fail'), $validator->errors()->messages());
    }

    /**
     * Delete a locale based on request data
     *
     * @param string api_key - required
     * @param string locale - required
     *
     * @return json with success or error
     */
    public function deleteLocale(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'locale'    => 'required|string|max:5|exists:locale,locale',
        ]);

        if (!$validator->fails()) {
            $locale = Locale::find($post['locale']);

            if ($locale->locale == \LaravelLocalization::getDefaultLocale()) {
                return $this->errorResponse(__('custom.default_locale_error'));
            }

            try {
                $locale->delete();

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_locale_fail'), $validator->errors()->messages());
    }

    /**
     * Lists locales based on input criteria
     *
     * @param string api_key - optional
     * @param array criteria - optional
     * @param bool criteria[active] - optional
     *
     * @return json with success or error
     */
    public function listLocale(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'        => 'nullable|array',
            'criteria.active' => 'nullable|bool',
        ]);

        if (!$validator->fails()) {
            $locales = new Locale;
            $results = [];

            if (isset($post['criteria']['active'])) {
                $locales = $locales->where('active', $post['criteria']['active']);
            }

            $supportedLocales = \LaravelLocalization::getSupportedLocales();

            foreach ($locales->get() as $locale) {
                $name = isset($supportedLocales[$locale->locale]) ? $supportedLocales[$locale->locale]['native'] : null;

                $results[] = [
                    'locale'        => $locale->locale,
                    'name'          => $name,
                    'active'        => $locale->active,
                    'created_at'    => isset($locale->created_at) ? $locale->created_at->toDateTimeString() : null,
                    'updated_at'    => isset($locale->updated_at) ? $locale->updated_at->toDateTimeString() : null,
                    'created_by'    => $locale->created_by,
                    'updated_by'    => $locale->updated_by,
                ];
            }

            return $this->successResponse(['locale_list' => $results], true);
        }

        return $this->errorResponse(__('custom.list_locale_fail'), $validator->errors()->messages());
    }

    /**
     * Get locale details based on request data
     *
     * @param string locale - required
     *
     * @return json with success or error
     */
    public function getLocaleDetails(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'locale' => 'required|string|max:5|exists:locale,locale',
        ]);

        if (!$validator->fails()) {
            $supportedLocales = \LaravelLocalization::getSupportedLocales();
            $locale = Locale::where('locale', $post['locale'])->first();
            $name = isset($supportedLocales[$locale->locale]) ? $supportedLocales[$locale->locale]['native'] : null;

            return $this->successResponse(['locale' => [
                'locale'        => $locale->locale,
                'name'          => $name,
                'active'        => $locale->active,
                'created_at'    => isset($locale->created_at) ? $locale->created_at->toDateTimeString() : null,
                'updated_at'    => isset($locale->updated_at) ? $locale->updated_at->toDateTimeString() : null,
                'created_by'    => $locale->created_by,
                'updated_by'    => $locale->updated_by,
            ]], true);
        }

        return $this->errorResponse(__('custom.get_locale_fail'), $validator->errors()->messages());
    }
}
