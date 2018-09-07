<?php
namespace App\Http\Controllers\Api;

use Uuid;
use App\Tags;
use App\Module;
use App\DataSet;
use App\Category;
use App\Resource;
use App\DataSetTags;
use App\DataSetGroup;
use \App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\ActionsHistory;
use Illuminate\Http\Request;
use App\Translator\Translation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class TagController extends ApiController
{
    /**
     * API function for adding tag
     *
     * @param string api_key - required
     * @param string data[name] - required
     *
     * @return json response with id of tag or error
     */
    public function addTag(Request $request)
    {
        $errors = [];
        $post = $request->all();

        $validator = \Validator::make($post, [
            'data'      => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'name'  => [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('tags')->where(function ($query) use ($post) {
                        Tags::checkName($query, $post['data']['name']);
                    }),
                ],
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        if (empty($errors)) {
            try {
                $record = Tags::create(['name' => $post['data']['name']]);

                $logData = [
                    'module_name'      => Module::getModuleName(Module::TAGS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $record->id,
                    'action_msg'       => 'Added tag',
                ];

                Module::add($logData);

                return $this->successResponse(['id' => $record->id], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_tag_fail'), $errors);
    }

    /**
     * API function for editing an existing tag
     *
     * @param string api_key - required
     * @param integer tag_id - required
     * @param array data - required
     * @param string data[name] - required
     *
     * @return json with success or error
     */
    public function editTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'tag_id'    => 'required|int|exists:tags,id|digits_between:1,10',
            'data'      => 'required|array',
        ]);

        if (!$validator->fails()) {
            $tag = Tags::find($post['tag_id']);

            $validator = \Validator::make($post['data'], [
                'name'  => [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('tags')->where(function ($query) use ($post, $tag) {
                        Tags::checkName($query, $post['data']['name'], $tag->id);
                    }),
                ],
            ]);
        }

        if (!$validator->fails()) {
            try {

                $tag->name = $post['data']['name'];

                $tag->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::TAGS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $tag->id,
                    'action_msg'       => 'Edited tag',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
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

        $validator = \Validator::make($post, ['tag_id' => 'required|int|exists:tags,id|digits_between:1,10']);

        if (!$validator->fails()) {
            try {
                DB::table('data_set_tags')->where('tag_id', $post['tag_id'])->delete();
                Tags::find($post['tag_id'])->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::TAGS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['tag_id'],
                    'action_msg'       => 'Deleted tag',
                ];

                Module::add($logData);

                return $this->successResponse();
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
                'order'        => 'nullable|array'
            ]);
        }

        if (!$validator->fails()) {
            $order = isset($criteria['order']) ? $criteria['order'] : [];
            $validator = \Validator::make($order, [
                'type'    => 'nullable|string|max:191',
                'field'   => 'nullable|string|max:191'
            ]);
        }

        if (!$validator->fails()) {
            $query = Tags::select();
            $criteria = empty($post['criteria']) ? false : $post['criteria'];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'desc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'created_at';

            if (!empty($criteria['tag_ids'])) {
                $query->whereIn('id', $criteria['tag_ids']);
            }

            $query->orderBy($order['field'], $order['type']);

            $count = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $data = $query->get();
                $tags = [];

                foreach ($data as $record) {
                    $record = $this->getModelUsernames($record);
                    $tags[] = [
                        'id'            => $record->id,
                        'name'          => $record->name,
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
            'tag_id' => 'required|int|exists:tags,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $tag = $this->getModelUsernames(Tags::find($post['tag_id']));

            $data = [
                'name'          => $tag->name,
                'created_at'    => $tag->created_at->toDateTimeString(),
                'updated_at'    => isset($tag->updated_at) ? $tag->updated_at->toDateTimeString() : null,
                'created_by'    => $tag->created_by,
                'updated_by'    => $tag->updated_by,
            ];

            return $this->successResponse(['tag' => $data], true);
        }

        return $this->errorResponse(__('custom.get_tags_fail'), $validator->errors()->messages());
    }

    /**
     * API function for searching tag by name
     *
     * @param integer tag_id - required
     * @param string name - optional
     *
     * @return json with details or error
     */
    public function searchTag(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'name'  => 'required|string|max:191',
        ]);

        if (!$validator->fails()) {
            $data = null;
            $tag = Tags::select();
            $tag = Tags::checkName($tag, $post['name'])->first();

            if (!empty($tag)) {
                $tag = $this->getModelUsernames($tag);

                $data = [
                    'id'            => $tag->id,
                    'name'          => $tag->name,
                    'created_at'    => $tag->created_at->toDateTimeString(),
                    'updated_at'    => isset($tag->updated_at) ? $tag->updated_at->toDateTimeString() : null,
                    'created_by'    => $tag->created_by,
                    'updated_by'    => $tag->updated_by,
                ];
            }

            return $this->successResponse(['tag' => $data], true);
        }

        return $this->errorResponse(__('custom.search_tags_fail'), $validator->errors()->messages());
    }
}
