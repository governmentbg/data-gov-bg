<?php
namespace App\Http\Controllers\Api;

use App\Module;
use App\Category;
use App\DataSet;
use App\RoleRight;
use App\ActionsHistory;
use App\Resource;
use App\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class CategoryController extends ApiController
{
    /**
     * API function for adding main Category
     *
     * @param string api_key - required
     * @param string name - required
     * @param string locale - required
     * @param string icon - optional
     * @param string icon_filename - optional
     * @param string icon_mimetype - optional
     * @param string icon_data - optional
     * @param integer active - optional
     * @param integer ordering - optional
     *
     * @return json response with id of category or error
     */
    public function addMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($request->get('data', []), [
            'name'              => 'required_with:locale|max:191',
            'name.bg'           => 'required_without:locale|string|max:191',
            'name.*'            => 'max:191',
            'locale'            => 'nullable|string|max:5',
            'icon'              => 'nullable|string|max:191',
            'icon_filename'     => 'nullable|string|max:191',
            'icon_mimetype'     => 'nullable|string|max:191|in:'. env('THEME_FILE_MIMES'),
            'icon_data'         => 'nullable|string|max:16777215',
            'active'            => 'nullable|boolean',
            'ordering'          => 'nullable|integer|digits_between:1,3',
        ]);

        $validator->after(function ($validator) {
            if ($validator->errors()->has('icon_mimetype')) {
                $validator->errors()->add(
                    'file',
                    $validator->errors()->first('icon_mimetype') .' '. __('custom.valid_file_types') .': '. env('THEME_FILE_MIMES')
                );
            }

            if ($validator->errors()->has('icon_filename')) {
                $validator->errors()->add('file', $validator->errors()->first('icon_filename'));
            }

            if ($validator->errors()->has('icon_data')) {
                $validator->errors()->add('file', $validator->errors()->first('icon_data'));
            }
        });

        // add main category
        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::MAIN_CATEGORIES,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $catData = [
                'name'              => $this->trans($post['locale'], $post['data']['name']),
                'icon_file_name'    => empty($post['data']['icon_filename'])
                    ? null
                    : $post['data']['icon_filename'],
                'icon_mime_type'    => empty($post['data']['icon_mimetype'])
                    ? null
                    : $post['data']['icon_mimetype'],
                'icon_data'         => empty($post['data']['icon_data'])
                    ? null
                    : $post['data']['icon_data'],
                'active'            => empty($post['data']['active'])
                    ? true
                    : $post['data']['active'],
                'ordering'          => empty($post['data']['ordering'])
                    ? Category::ORDERING_ASC
                    : $post['data']['ordering'],
            ];

            try {
                $category = Category::create($catData);

                if ($category) {
                    $logData = [
                        'module_name'      => Module::getModuleName(Module::MAIN_CATEGORIES),
                        'action'           => ActionsHistory::TYPE_ADD,
                        'action_object'    => $category->id,
                        'action_msg'       => 'Added main category',
                    ];

                    Module::add($logData);

                    return $this->successResponse(['category_id' => $category->id], true);
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_main_category'), $validator->errors()->messages());
    }

    /**
     * API function for editing main Category
     *
     * @param string api_key - required
     * @param integer category_id - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[locale] - required
     * @param string data[icon] - optional
     * @param string data[icon_filename] - optional
     * @param string data[icon_mimetype] - optional
     * @param string data[icon_data] - optional
     * @param integer data[active] - optional
     * @param integer data[ordering] - optional
     *
     * @return json with success true or errors on failure
     */
    public function editMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'           => 'required|integer|digits_between:1,10',
            'data'                  => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'name'             => 'required_with:locale|max:191',
                'name.bg'          => 'required_without:locale|string|max:191',
                'name.*'           => 'max:191',
                'locale'           => 'nullable|string|max:5',
                'icon'             => 'nullable|string|max:191',
                'icon_filename'    => 'nullable|string|max:191',
                'icon_mimetype'    => 'nullable|string|max:191',
                'icon_data'        => 'nullable|string|max:16777215',
                'active'           => 'nullable|boolean',
                'ordering'         => 'nullable|integer|digits_between:1,3',
            ]);
        }

        $validator->after(function ($validator) {
            if ($validator->errors()->has('icon_filename')) {
                $validator->errors()->add('file', $validator->errors()->first('icon_filename'));
            }

            if ($validator->errors()->has('icon_data')) {
                $validator->errors()->add('file', $validator->errors()->first('icon_data'));
            }
        });

        if (!$validator->fails()) {
            $category = Category::find($post['category_id']);

            if ($category) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::MAIN_CATEGORIES,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $category->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $category->name = $post['data']['name'];

                // add library for image manipulation
                if (!empty($post['data']['icon_filename'])) {
                    $category->icon_file_name = $post['data']['icon_filename'];
                }

                if (!empty($post['data']['icon_mimetype'])) {
                    $category->icon_mime_type = $post['data']['icon_mimetype'];
                }

                if (!empty($post['data']['icon_data'])) {
                    $category->icon_data = $post['data']['icon_data'];
                }

                if (!empty($post['data']['active'])) {
                    $category->active = $post['data']['active'];
                } else {
                    $category->active = Category::ACTIVE_FALSE;
                }

                if (!empty($post['data']['ordering'])) {
                    $category->ordering = $post['data']['ordering'];
                }

                $category->updated_by = \Auth::id();

                try {
                    $category->save();
                    $logData = [
                        'module_name'      => Module::getModuleName(Module::MAIN_CATEGORIES),
                        'action'           => ActionsHistory::TYPE_MOD,
                        'action_object'    => $category->id,
                        'action_msg'       => 'Edited main category',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_category_fail'), $validator->errors()->messages());
    }

    /**
     * API function for deleting a main Category
     *
     * @param string api_key - required
     * @param integer category_id - required
     *
     * @return json with success true or errors on failure
     */
    public function deleteMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'   => 'required|integer|exists:categories,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $category = Category::find($post['category_id']);

            $rightCheck = RoleRight::checkUserRight(
                Module::MAIN_CATEGORIES,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $category->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                DataSet::withTrashed()
                    ->where('category_id', $post['category_id'])
                    ->update(['category_id' => null]);

                DB::table('user_follows')->where('category_id', $post['category_id'])
                    ->update(['category_id' => null]);

                DB::table('user_follows')->where('tag_id', $post['category_id'])
                    ->update(['tag_id' => null]);

                DB::table('categories')->where('parent_id', $post['category_id'])
                    ->update(['parent_id' => null]);

                if ($category->delete()) {
                    $logData = [
                        'module_name'      => Module::getModuleName(Module::MAIN_CATEGORIES),
                        'action'           => ActionsHistory::TYPE_DEL,
                        'action_object'    => $post['category_id'],
                        'action_msg'       => 'Deleted main category',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_category_fail'), $validator->errors()->messages());
    }

    /**
     * API function for listing main categories by criteria
     *
     * @param array criteria - optional
     * @param array criteria[category_ids] - optional
     * @param string criteria[locale] - optional
     * @param string criteria[active] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json with list of categories or error
     */
    public function listMainCategories(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'               => 'nullable|array',
            'records_per_page'       => 'nullable|integer|digits_between:1,10',
            'page_number'            => 'nullable|integer|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'category_ids'  => 'nullable|array',
                'locale'        => 'nullable|string|max:5',
                'active'        => 'nullable|boolean',
                'order'         => 'nullable|array',
                'keywords'      => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $order = isset($post['order']) ? $post['order'] : [];
            $validator = \Validator::make($order, [
                'type'    => 'nullable|string|max:191',
                'field'   => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $criteria = empty($post['criteria']) ? false : $post['criteria'];
            $order = [];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';

            $query = Category::where('parent_id', null);

            if (isset($criteria['category_ids'])) {
                $query->whereIn('id', $criteria['category_ids']);
            }

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
            }

            if (!empty($criteria['keywords'])) {
                $ids = Category::search($criteria['keywords'])->get()->pluck('id');
                $query->whereIn('id', $ids);
            }

            if ($order) {
                $query->orderBy($order['field'], $order['type']);
            }

            $count = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $results = [];
                $categories = $query->get();

                foreach ($categories as $category) {
                    $results[] = [
                        'id'            => $category->id,
                        'name'          => $category->name,
                        'locale'        => \LaravelLocalization::getCurrentLocale(),
                        'active'        => $category->active,
                        'ordering'      => $category->ordering,
                        'icon'          => $this->getImageData($category->icon_data, $category->icon_mime_type),
                        'created_at'    => date($category->created_at),
                        'created_by'    => $category->created_by,
                        'updated_at'    => date($category->updated_at),
                        'updated_by'    => $category->updated_by,
                    ];
                }

                if ($order && $order['field'] == 'name') {
                    usort($results, function($a, $b) use ($order) {
                        return strtolower($order['type']) == 'asc'
                            ? strcmp($a[$order['field']], $b[$order['field']])
                            : strcmp($b[$order['field']], $a[$order['field']]);
                    });
                }

                return $this->successResponse([
                    'total_records' => $count,
                    'categories'    => $results,
                ], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_categories_fail'), $validator->errors()->messages());
    }

    /**
     * API function for viewing main category details
     *
     * @param integer category_id - required
     * @param string locale - optional
     *
     * @return json with details or error
     */
    public function getMainCategoryDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'   => 'required|integer|exists:categories,id|digits_between:1,10',
            'locale'        => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            $category = Category::find($post['category_id']);

            $category['name'] = $category->name;
            $category['locale'] = \LaravelLocalization::getCurrentLocale();
            $category['icon_data'] = utf8_encode($category->icon_data);

            if ($category) {
                return $this->successResponse(['category' => $category], true);
            }
        }

        return $this->errorResponse(__('custom.get_categories_fail'), $validator->errors()->messages());
    }

    /**
     * Lists the count of the datasets per main category
     *
     * @param array criteria - optional
     * @param array criteria[dataset_criteria] - optional
     * @param array criteria[dataset_criteria][user_ids] - optional
     * @param array criteria[dataset_criteria][org_ids] - optional
     * @param array criteria[dataset_criteria][group_ids] - optional
     * @param array criteria[dataset_criteria][category_ids] - optional
     * @param array criteria[dataset_criteria][tag_ids] - optional
     * @param array criteria[dataset_criteria][formats] - optional
     * @param array criteria[dataset_criteria][terms_of_use_ids] - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[locale] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataCategories(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'criteria' => 'nullable|array'
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'dataset_criteria'  => 'nullable|array',
                'dataset_ids'       => 'nullable|array',
                'dataset_ids.*'     => 'int|exists:data_sets,id|digits_between:1,10',
                'locale'            => 'nullable|string|max:5|exists:locale,locale,active,1',
                'records_limit'     => 'nullable|int|digits_between:1,10|min:1',
            ]);
        }

        if (!$validator->fails()) {
            $dsCriteria = isset($criteria['dataset_criteria']) ? $criteria['dataset_criteria'] : [];
            $validator = \Validator::make($dsCriteria, [
                'user_ids'            => 'nullable|array',
                'user_ids.*'          => 'int|digits_between:1,10|exists:users,id',
                'org_ids'             => 'nullable|array',
                'org_ids.*'           => 'int|digits_between:1,10|exists:organisations,id',
                'group_ids'           => 'nullable|array',
                'group_ids.*'         => 'int|digits_between:1,10|exists:organisations,id,type,'. Organisation::TYPE_GROUP,
                'category_ids'        => 'nullable|array',
                'category_ids.*'      => 'int|digits_between:1,10|exists:categories,id,parent_id,NULL',
                'tag_ids'             => 'nullable|array',
                'tag_ids.*'           => 'int|digits_between:1,10|exists:tags,id',
                'terms_of_use_ids'    => 'nullable|array',
                'terms_of_use_ids.*'  => 'int|digits_between:1,10|exists:terms_of_use,id',
                'formats'             => 'nullable|array|min:1',
                'formats.*'           => 'string|in:'. implode(',', Resource::getFormats()),
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($criteria['locale']) ? $criteria['locale'] : \LaravelLocalization::getCurrentLocale();
                $data = Category::join('data_sets', 'categories.id', '=', 'category_id');
                $data->select('categories.id', 'categories.name', DB::raw('count(distinct data_sets.id, data_sets.category_id) as total'));
                $data->where('categories.active', 1);
                $data->whereNull('categories.parent_id');
                $data->where('data_sets.status', DataSet::STATUS_PUBLISHED);
                $data->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC);

                if (!empty($dsCriteria['user_ids'])) {
                    $data->whereIn('data_sets.created_by', $dsCriteria['user_ids']);
                }

                if (!empty($dsCriteria['org_ids'])) {
                    $data->whereIn('org_id', $dsCriteria['org_ids']);
                }

                if (!empty($dsCriteria['group_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_groups')->select('data_set_id')->distinct()->whereIn('group_id', $dsCriteria['group_ids'])
                    );
                }

                if (!empty($dsCriteria['category_ids'])) {
                    $data->whereIn('category_id', $dsCriteria['category_ids']);
                }

                if (!empty($dsCriteria['tag_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_tags')->select('data_set_id')->distinct()->whereIn('tag_id', $dsCriteria['tag_ids'])
                    );
                }

                if (!empty($dsCriteria['terms_of_use_ids'])) {
                    $data->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                }

                if (!empty($dsCriteria['formats'])) {
                    $fileFormats = [];

                    foreach ($dsCriteria['formats'] as $format) {
                        $fileFormats[] = Resource::getFormatsCode($format);
                    }

                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)
                    );
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_sets.id', $criteria['dataset_ids']);
                }

                $data->groupBy(['categories.id', 'categories.name'])->orderBy('total', 'desc')->orderBy('ordering', 'asc');

                if (!empty($criteria['records_limit'])) {
                    $data->take($criteria['records_limit']);
                }

                $data = $data->get();
                $results = [];

                if (!empty($data)) {
                    foreach ($data as $item) {
                        $results[] = [
                            'id'             => $item->id,
                            'name'           => $item->name,
                            'locale'         => $locale,
                            'datasets_count' => $item->total,
                        ];
                    }
                }

                return $this->successResponse(['categories' => $results], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_categories_fail'), $validator->errors()->messages());
    }
}
