<?php
namespace App\Http\Controllers\Api;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class CategoryController extends ApiController
{
    /**
     * API function for adding main Category
     *
     * @param Request $request - POST request
     * @return json $response - response with status and id of Data Set if successfull
     */
    public function addMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post['data'], [
            'name'              => 'required|string',
            'locale'            => 'required|string|max:5',
            'icon'              => 'string',
            'icon_filename'     => 'string',
            'icon_mimetype'     => 'string',
            'icon_data'         => 'string',
            'active'            => 'integer',
            'ordering'          => 'integer',
        ]);

        // add library for image manipulation
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
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Add main category failure');
    }

    public function editMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'           => 'required|integer',
            'data.name'             => 'required|string',
            'data.locale'           => 'required|string|max:5',
            'data.icon'             => 'string',
            'data.icon_filename'    => 'string',
            'data.icon_mimetype'    => 'string',
            'data.icon_data'        => 'string',
            'data.active'           => 'integer',
            'data.ordering'         => 'integer',
        ]);

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
                    return $this->errorResponse($ex->getMessage());
                }

            } else {
                return $this->errorResponse('Edit main category failure');
            }
        }

        return $this->errorResponse('Edit main category failure');
    }

    public function deleteMainCategory(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'   => 'required|integer',
        ]);

        if (!$validator->fails()) {
            try {
                if (Category::find($post['category_id'])->delete()) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete main category failure');
    }

    public function listMainCategories(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria.locale'        => 'string|max:5',
            'criteria.active'        => 'integer',
            'criteria.order.type'    => 'string',
            'criteria.order.field'   => 'string',
            'records_per_page'      => 'integer',
            'page_number'           => 'integer',
        ]);

        if (!$validator->fails()) {
            $criteria = empty($post['criteria']) ? false : $post['criteria'];
            $order = [];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
            $pagination = $this->getRecordsPerPage($post['records_per_page']);
            $page = !empty($post['page_number']) ? $post['page_number'] : 1;

            $query = Category::select('*');

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
            }

            if ($order) {
                $query->orderBy($order['field'], $order['type']);
            }

            if ($pagination && $page) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            try {
                $categoryData = $query->get();

                foreach ($categoryData as $category) {
                    $category['name'] = $category->name;
                    $category['locale'] = \LaravelLocalization::getCurrentLocale();
                }

                return $this->successResponse($categoryData);
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('List main categories failure');
    }

    public function getMainCategoryDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'category_id'   => 'required|integer',
            'locale'        => 'string|max:5',
        ]);

        if (!$validator->fails()) {
            $category = Category::find($post['category_id']);

            $category['name'] = $category->name;
            $category['locale'] = \LaravelLocalization::getCurrentLocale();

            if ($category) {
                return $this->successResponse(['category' => $category], true);
            }
        }

        return $this->errorResponse('Get main category details failure');
    }

    public function addTag(Request $request)
    {
        $post = $request->data;

        $validator = \Validator::make($post, [
            'name'          => 'required|string',
            'category_id'   => 'required|integer',
            'locale'        => 'required|string|max:5',
            'active'        => 'integer',
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
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Add tag failure');
    }

    public function editTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'tag_id'            => 'required|integer',
            'data.name'         => 'string',
            'data.locale'       => 'string',
            'data.category_id'  => 'integer',
            'data.active'       => 'integer'
        ]);

        if (!$validator->fails()) {
            $tag = Category::find($post['tag_id']);

            if ($tag) {
                if (!empty($post['data']['name'])) {
                    if (empty($post['data']['locale'])) {
                        return $this->errorResponse('Edit tag failure');
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
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Edit tag failure');
    }

    public function deleteTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['tag_id' => 'required|integer']);

        if (!$validator->fails()) {
            try {
                if (Category::find($post['tag_id'])->delete()) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete tag failure');
    }

    public function listTags(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'records_per_page'      => 'integer',
            'page_number'           => 'integer',
            'criteria.locale'       => 'string|max:5',
            'criteria.category_id'  => 'integer',
            'criteria.active'       => 'integer',
            'criteria.order.type'   => 'string',
            'criteria.order.field'  => 'string',
        ]);

        if (!$validator->fails()) {
            $criteria = empty($post['criteria']) ? false : $post['criteria'];
            $order = [];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
            $pagination = $this->getRecordsPerPage($post['records_per_page']);
            $page = !empty($post['page_number']) ? $post['page_number'] : 1;

            $query = Category::where('parent_id', '!=', null);

            if (!empty($criteria['category_id'])) {
                $query->where('parent_id', $criteria['category_id']);
            }

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
            }

            $count = $query->count();

            if ($order) {
                $query->orderBy($order['field'], $order['type']);
            }

            if ($pagination && $page) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            try {
                $data = $query->get();
                $tags = [];

                foreach ($data as $record) {
                    $tags[] = [
                        'name'          => $record->name,
                        'category_id'   => $record->parent_id,
                        'locale'        => \LaravelLocalization::getCurrentLocale(),
                        'active'        => $record->active,
                        'created_at'    => $record->created_at,
                        'created_by'    => $record->created_by,
                        'updated_at'    => $record->updated_at,
                        'updated_by'    => $record->updated_by,
                    ];
                }

                return $this->successResponse([
                    'tags' => $tags,
                    'total_records' => $count,
                ], true);
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('List tags failure');
    }

    public function getTagDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'tag_id' => 'required|integer',
            "locale" => 'string|max:5',
        ]);

        if (!$validator->fails()) {
            $data = [];
            $tag = Category::find($post['tag_id']);

            if ($tag) {
                $data['name'] = $tag->name;
                $data['category_id'] = $tag->parent_id;
                $data['locale'] = \LaravelLocalization::getCurrentLocale();
                $data['active'] = $tag->active;
                $data['created_at'] = $tag['created_at'];
                $data['created_by'] = $tag['created_by'];
                $data['updated_at'] = $tag['updated_at'];
                $data['updated_by'] = $tag['updated_by'];

                return $this->successResponse(['tag' => $data], true);
            }
        }

        return $this->errorResponse('Get tag details failure');
    }
}
