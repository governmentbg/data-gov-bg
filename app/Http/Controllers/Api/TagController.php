<?php
namespace App\Http\Controllers\Api;

use Uuid;
use App\Tags;
use App\Module;
use App\DataSet;
use App\Category;
use App\Resource;
use App\RoleRight;
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
use Illuminate\Support\Facades\Auth;
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
            $rightCheck = RoleRight::checkUserRight(
                Module::TAGS,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                //prepare model data
                $newTag = [];
                $newTag['name'] = $post['data']['name'];

                if (
                    isset($post['data']['migrated_data'])
                    && Auth::user()->username == 'migrate_data'
                ) {
                    if (!empty($post['data']['created_by'])) {
                        $newTag['created_by'] = $post['data']['created_by'];
                    }
                }

                $record = Tags::create($newTag);

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
            $rightCheck = RoleRight::checkUserRight(
                Module::TAGS,
                RoleRight::RIGHT_EDIT,
                [],
                [
                    'created_by' => $tag->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {

                $tag->name = $post['data']['name'];
                $tag->updated_by = \Auth::id();

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
                $rightCheck = RoleRight::checkUserRight(
                    Module::TAGS,
                    RoleRight::RIGHT_ALL,
                    [],
                    [
                        'created_by' => \Auth::user()->id
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

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

            $orderColumns = [
                'id',
                'name',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            if (isset($criteria['order']['field'])) {
                if (!in_array($criteria['order']['field'], $orderColumns)) {
                    return $this->errorResponse(__('custom.invalid_sort_field'));
                }
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
     * @param boolean criteria[dataset_criteria][reported] - optional
     * @param array criteria[dataset_ids] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataTags(Request $request)
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
                'keywords'          => 'nullable|string|max:191',
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
                'reported'            => 'nullable|boolean',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($criteria['locale']) ? $criteria['locale'] : \LaravelLocalization::getCurrentLocale();
                $data = DataSetTags::join('tags', 'tag_id', '=', 'tags.id');

                $data->join('data_sets', 'data_sets.id', '=', 'data_set_tags.data_set_id');

                $data->select('tags.id', 'tags.name', DB::raw('count(distinct data_set_id, tag_id) as total'));

                $data->whereHas('DataSet', function($q) use ($dsCriteria) {
                    if (!empty($dsCriteria['user_ids'])) {
                        $q->whereNull('org_id');
                        $q->whereIn('created_by', $dsCriteria['user_ids']);
                    }
                    if (!empty($dsCriteria['org_ids'])) {
                        $q->whereIn('org_id', $dsCriteria['org_ids']);
                    }
                    if (!empty($dsCriteria['group_ids'])) {
                        $q->whereHas('DataSetGroup', function($qr) use ($dsCriteria) {
                            $qr->whereIn('group_id', $dsCriteria['group_ids']);
                        });
                    }
                    if (!empty($dsCriteria['category_ids'])) {
                        $q->whereIn('category_id', $dsCriteria['category_ids']);
                    }
                    if (!empty($dsCriteria['tag_ids'])) {
                        $q->whereHas('DataSetTags', function($qr) use ($dsCriteria) {
                            $qr->whereIn('tag_id', $dsCriteria['tag_ids']);
                        });
                    }
                    if (!empty($dsCriteria['terms_of_use_ids'])) {
                        $q->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                    }
                    if (!empty($dsCriteria['formats'])) {
                        $fileFormats = [];
                        foreach ($dsCriteria['formats'] as $format) {
                            $fileFormats[] = Resource::getFormatsCode($format);
                        }
                        $q->whereIn(
                            'data_sets.id',
                            DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)->whereNull('resources.deleted_by')
                        );
                    }
                    if (isset($dsCriteria['reported']) && $dsCriteria['reported']) {
                        $q->whereIn(
                            'data_sets.id',
                            DB::table('resources')->select('data_set_id')->distinct()->where('is_reported', Resource::REPORTED_TRUE)->whereNull('resources.deleted_by')
                        );
                    }
                    $q->where('status', DataSet::STATUS_PUBLISHED);
                    $q->where('visibility', DataSet::VISIBILITY_PUBLIC);
                });

                $data->where(function($q) {
                    $q->whereIn(
                        'data_sets.org_id',
                        Organisation::select('id')
                            ->where('organisations.active', 1)
                            ->where('organisations.approved', 1)
                            ->get()
                            ->pluck('id')
                    )
                        ->orWhereNull('data_sets.org_id');
                });

                if (!empty($criteria['keywords'])) {
                    $tntIds = DataSet::search($criteria['keywords'])->get()->pluck('id');

                    $fullMatchIds = DataSet::select('data_sets.id')
                        ->leftJoin('translations', 'translations.group_id', '=', 'data_sets.name')
                        ->where('translations.locale', $locale)
                        ->where('translations.text', 'like', '%'. $criteria['keywords'] .'%')
                        ->pluck('id');

                    $ids = $fullMatchIds->merge($tntIds)->unique();

                    $data->whereIn('data_sets.id', $ids);

                    if (count($ids)) {
                        $strIds = $ids->implode(',');
                        $data->raw(DB::raw('FIELD(data_sets.id, '. $strIds .')'));
                    }
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_set_id', $criteria['dataset_ids']);
                }

                $data->groupBy('tags.id', 'tags.name')->orderBy('total', 'desc');

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
                            'datasets_count' => $item->total,
                        ];
                    }
                }

                return $this->successResponse(['tags' => $results], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_tags_fail'), $validator->errors()->messages());
    }
}
