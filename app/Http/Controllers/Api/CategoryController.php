<?php
namespace App\Http\Controllers\Api;

use App\Category;
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
            'name'              => 'required|string|max:191',
            'locale'            => 'required|string|max:5',
            'icon'              => 'nullable|string|max:191',
            'icon_filename'     => 'nullable|string|max:191',
            'icon_mimetype'     => 'nullable|string|max:191',
            'icon_data'         => 'nullable|string|max:16777215',
            'active'            => 'nullable|boolean',
            'ordering'          => 'nullable|integer|digits_between:1,3',
        ]);

        // add main category
        if (!$validator->fails()) {
            $catData = [
                'name'              => $post['data']['name'],
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
                'name'             => 'required|string|max:191',
                'locale'           => 'required|string|max:5',
                'icon'             => 'nullable|string|max:191',
                'icon_filename'    => 'nullable|string|max:191',
                'icon_mimetype'    => 'nullable|string|max:191',
                'icon_data'        => 'nullable|string|max:16777215',
                'active'           => 'nullable|boolean',
                'ordering'         => 'nullable|integer|digits_between:1,3',
            ]);
        }

        if (!$validator->fails()) {
            $category = Category::find($post['category_id']);

            if ($category) {
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
                }

                if (!empty($post['data']['ordering'])) {
                    $category->ordering = $post['data']['ordering'];
                }

                try {
                    $category->save();

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
            try {
                if (Category::find($post['category_id'])->delete()) {
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

            if ($order) {
                $query->orderBy($order['field'], $order['type']);
            }

            $count = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $results = $query->get();

                $locale = \LaravelLocalization::getCurrentLocale();

                foreach ($results as $category) {
                    $category['name'] = $category->name;
                    $category['locale'] = $locale;
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

            if ($category) {
                return $this->successResponse(['category' => $category], true);
            }
        }

        return $this->errorResponse(__('custom.get_categories_fail'), $validator->errors()->messages());
    }

    /**
     * API function for adding a tag
     *
     * @param string api_key - required
     * @param string category_id - required
     * @param string name - required
     * @param string locale - required
     * @param integer active - optional
     *
     * @return json with tag_id or error
     */
    public function addTag(Request $request)
    {
        $post = $request->data;

        $validator = \Validator::make($post, [
            'name'          => 'required|string|max:191',
            'category_id'   => 'required|integer|digits_between:1,10',
            'locale'        => 'required|string|max:5',
            'active'        => 'nullable|boolean',
        ]);

        if (!$validator->fails()) {
            $data['name'] = $post['name'];
            $data['parent_id'] = $post['category_id'];
            $data['ordering'] = Category::ORDERING_ASC;

            if (empty($data['active'])) {
                $data['active'] = true;
            }

            try {
                $tag = Category::create($data);

                if ($tag) {
                    return $this->successResponse(['tag_id' => $tag->id], true);
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_tag_fail'), $validator->errors()->messages());
    }

    /**
     * API function for editing an existing tag
     *
     * @param string api_key - required
     * @param integer tag_id - required
     * @param array data - required
     * @param string data[locale] - required if data[name] is present
     * @param string data[name] - optional
     * @param string data[category_id] - optional
     * @param integer data[active] - optional
     *
     * @return json with success or error
     */
    public function editTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'tag_id'            => 'required|int|exists:categories,id|digits_between:1,10',
            'data'              => 'nullable|array',
        ]);

        if (!$validator->fails()) {
            $post['data'] = isset($post['data']) ? $post['data'] : [] ;
            $validator = \Validator::make($post['data'], [
                'name'         => 'nullable|string|max:191',
                'locale'       => 'nullable|string|max:5',
                'category_id'  => 'nullable|integer|digits_between:1,10',
                'active'       => 'nullable|boolean'
        ]);
        }

        if (!$validator->fails()) {
            $tag = Category::find($post['tag_id']);

            if ($tag) {
                if (!empty($post['data']['name'])) {
                    if (empty($post['data']['locale'])) {
                        return $this->errorResponse(__('custom.edit_tag_fail'));
                    }

                    $tag->name = $post['data']['name'];
                }

                if (!empty($post['data']['category_id'])) {
                    $tag->parent_id = $post['data']['category_id'];
                }

                if (isset($post['data']['active'])) {
                    $tag->active = $post['data']['active'];
                }

                try {
                    $tag->save();

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_tag_fail'), $validator->errors()->messages());
    }

     /**
     * API function for deleting an existing tag
     *
     * @param string api_key - required
     * @param integer tag_id - required
     *
     * @return json with success or error
     */
    public function deleteTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['tag_id' => 'required|integer|exists:categories,id|digits_between:1,10']);

        if (!$validator->fails()) {
            try {
                if (Category::find($post['tag_id'])->delete()) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_tag_fail'), $validator->errors()->messages());
    }

    /**
     * API function for listing tags by criteria
     *
     * @param array criteria - optional
     * @param array criteria[tag_ids] - optional
     * @param string criteria[locale] - optional
     * @param string criteria[active] - optional
     * @param integer criteria[category_id] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json with list of tags or error
     */
    public function listTags(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
            'criteria'              => 'nullable|array'
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'tag_ids'      => 'nullable|array',
                'locale'       => 'nullable|string|max:5',
                'category_id'  => 'nullable|integer|digits_between:1,10',
                'active'       => 'nullable|boolean',
                'order'        => 'nullable|array'
            ]);
        }

        if (!$validator->fails()) {
            $order = isset($request['criteria']['order']) ? $request['criteria']['order'] : [];
            $validator = \Validator::make($order, [
                'type'    => 'nullable|string|max:191',
                'field'   => 'nullable|string|max:191'
            ]);
        }

        if (!$validator->fails()) {
            $criteria = empty($post['criteria']) ? false : $post['criteria'];
            $order = [];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';

            $query = Category::where('parent_id', '!=', null);

            if (!empty($criteria['tag_ids'])) {
                $query->whereIn('id', $request->criteria['tag_ids']);
            }

            if (!empty($criteria['category_id'])) {
                $query->where('parent_id', $criteria['category_id']);
            }

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
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
                $data = $query->get();
                $tags = [];

                foreach ($data as $record) {
                    $tags[] = [
                        'id'            => $record->id,
                        'name'          => $record->name,
                        'category_id'   => $record->parent_id,
                        'locale'        => \LaravelLocalization::getCurrentLocale(),
                        'active'        => $record->active,
                        'created_at'    => date($record->created_at),
                        'created_by'    => $record->created_by,
                        'updated_at'    => date($record->updated_at),
                        'updated_by'    => $record->updated_by,
                    ];
                }

                return $this->successResponse([
                    'total_records' => $count,
                    'tags'          => $tags
                ], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_tags_fail'), $validator->errors()->messages());
    }

    /**
     * API function for viewing tag details
     *
     * @param integer tag_id - required
     * @param string locale - optional
     *
     * @return json with details or error
     */
    public function getTagDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'tag_id' => 'required|int|exists:categories,id|digits_between:1,10',
            'locale' => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            $tag = Category::find($post['tag_id']);

            $data = [
                'name'          => $tag->name,
                'category_id'   => $tag->parent_id,
                'locale'        => \LaravelLocalization::getCurrentLocale(),
                'active'        => $tag->active,
                'created_at'    => $tag->created_at->toDateTimeString(),
                'updated_at'    => isset($tag->updated_at) ? $tag->updated_at->toDateTimeString() : null,
                'created_by'    => $tag->created_by,
                'updated_by'    => $tag->updated_by,
            ];

            return $this->successResponse(['tag' => $data], true);
        }

        return $this->errorResponse(__('custom.get_tags_fail'), $validator->errors()->messages());
    }
}
