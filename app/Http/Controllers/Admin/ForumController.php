<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DevDojo\Chatter\Models\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;

class ForumController extends AdminController
{

    /**
     * Lists discussions
     *
     * @param Request $request
     *
     * @return view with list of discussions
     */
    public function listDiscussions(Request $request)
    {
        $perPage = 10;
        $discussions = Models::discussion()->with('category')->get();

        $paginationData = $this->getPaginationData(
            $discussions,
            0,
            [],
            $perPage
        );

        return view(
            'admin/forum/discussionsList',
            [
                'class'       => 'user',
                'discussions' => $paginationData['items'],
                'pagination'  => $paginationData['paginate'],
            ]
        );
    }

    /**
     * Lists categories
     *
     * @param Request $request
     *
     * @return view with list of categories
     */
    public function listCategories(Request $request)
    {
        $perPage = 10;
        $categories = Models::category()->where('parent_id', null)->get();

        $paginationData = $this->getPaginationData(
            $categories,
            0,
            [],
            $perPage
        );

        return view(
            'admin/forum/categoriesList',
            [
                'class'      => 'user',
                'categories' => $paginationData['items'],
                'pagination' => $paginationData['paginate'],
            ]
        );
    }

    /**
     * Lists categories
     *
     * @param Request $request
     *
     * @return view with list of categories
     */
    public function listSubcategories(Request $request, $id)
    {
        $perPage = 10;
        $subcategories = Models::category()->where('parent_id', $id)->get();
        $mainCategory = Models::category()->where('id', $id)->where('parent_id', null)->first();

        if (!is_null($mainCategory)) {
            $paginationData = $this->getPaginationData(
                $subcategories,
                0,
                [],
                $perPage
            );

            return view(
                'admin/forum/subcategoriesList',
                [
                    'class'      => 'user',
                    'categories' => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                    'mainCat'    => $mainCategory,
                ]
            );
        }

        return back();
    }

    public function addDiscussion(Request $request)
    {
        $categories = Models::category()->get();

        if ($request->has('create')) {

            $validator = \Validator::make($request->all(), [
                'title'    => 'required|max:191|unique:chatter_discussion,title',
                'color'    => 'nullable|max:20',
                'category' => 'required|int|digits_between:1,10',
                'message'  => 'required|max:8000',
            ]);

            $errors = empty($validator->errors()) ? null : $validator->errors();

            if (!$validator->fails()) {
                $userId = Auth::user()->id;
                $discussion = Models::discussion();

                if (isset($request->title)) {
                    $discussion->title = $request->title;
                    $discussion->slug = str_slug($request->title, '-');
                }

                if (isset($request->category)) {
                    $discussion->chatter_category_id = $request->category;
                }

                $discussion->user_id = $userId;

                $discussion->color = isset($request->color) ? $request->color : null;
                unset($discussion->created_by);

                DB::beginTransaction();

                try {
                    $discussion->save();
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    DB::table('chatter_user_discussion')->insert([
                        'user_id'       => $userId,
                        'discussion_id' => $discussion->id
                    ]);

                    DB::table('chatter_post')->insert([
                        'user_id'               => $userId,
                        'chatter_discussion_id' => $discussion->id,
                        'body'                  => $request->message,
                        'created_at'            => date('Y-m-d H:i:s'),
                    ]);

                    DB::commit();

                    return redirect('/admin/forum/discussions/view/'. $discussion->id);
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                    DB::rollback();
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));
            }

            return back()->withErrors($errors)->withInput(Input::all());
        }



        return view(
            'admin/forum/discussionAdd',
            [
                'class'      => 'user',
                'categories' => $categories
            ]
        );
    }

    public function addCategory(Request $request)
    {
        if ($request->has('create')) {

            $validator = \Validator::make($request->all(), [
                'name'   => 'required|max:191',
                'color'  => 'required|max:20',
                'order'  => 'nullable|int|max:11|digits_between:1,10',
            ]);

            $errors = empty($validator->errors()) ? null : $validator->errors();

            if (!$validator->fails()) {
                $category = Models::category();

                if (isset($request->name)) {
                    $category->name = $request->name;
                    $category->slug = str_slug($request->name, '-');
                }

                if (isset($request->order)) {
                    $category->order = $request->order;
                }

                $category->created_at = date('Y-m-d H:i:s');
                $category->color = $request->color;

                try {
                    $category->save();
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/forum/categories/view/'. $category->id);
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));
            }

            return back()->withErrors($errors)->withInput(Input::all());
        }

        return view(
            'admin/forum/categoriesAdd',
            [
                'class' => 'user',
            ]
        );
    }

    public function addSubcategory(Request $request, $id)
    {
        $mainCatId = Models::category()->where('id', $id)->where('parent_id', null)->value('id');
        $categories = Models::category()->where('parent_id', null)->get();

        if ($request->has('create')) {

            $validator = \Validator::make($request->all(), [
                'name'      => 'required|max:191',
                'color'     => 'required|max:20',
                'order'     => 'nullable|int|max:11|digits_between:1,10',
                'parent_id' => 'required|int|exists:chatter_categories,id|digits_between:1,10'
            ]);

            $errors = empty($validator->errors()) ? null : $validator->errors();

            if (!$validator->fails()) {
                $category = Models::category();

                if (isset($request->name)) {
                    $category->name = $request->name;
                    $category->slug = str_slug($request->name, '-');
                }

                if (isset($request->order)) {
                    $category->order = $request->order;
                }

                $category->parent_id = $request->parent_id;
                $category->color = $request->color;

                try {
                    $category->save();
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/forum/subcategories/view/'. $category->id);
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));
            }

            return back()->withErrors($errors)->withInput(Input::all());
        }

        return view(
            'admin/forum/subcategoriesAdd',
            [
                'class'      => 'user',
                'mainCatId'  => $mainCatId,
                'categories' => $categories
            ]
        );
    }

    /**
     * Displays information for a given discussion
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function viewDiscussion(Request $request, $id)
    {
        $discussion = Models::discussion()->with('category')->where('id', $id)->first();

        if (!is_null($discussion)) {
            $discussion->created_by = User::where('id', $discussion->user_id)->value('username');

            return view(
                'admin/forum/discussionView',
                [
                    'class'       => 'user',
                    'discussion'  => $discussion,
                ]
            );
        }

        return redirect('/admin/forum/discussions/list');
    }

    /**
     * Displays information for a given category
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function viewCategory(Request $request, $id)
    {
        $category = Models::category()->where('id', $id)->first();

        if (!is_null($category)) {

            return view(
                'admin/forum/categoryView',
                [
                    'class'     => 'user',
                    'category'  => $category,
                ]
            );
        }

        return redirect('/admin/forum/categories/list');
    }

    /**
     * Displays information for a given subcategory
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function viewSubcategory(Request $request, $id)
    {
        $category = Models::category()->where('id', $id)->where('parent_id', '!=', null)->first();

        if (!is_null($category)) {
            $mainCatName = Models::category()->where('id', $category->parent_id)->value('name');

            return view(
                'admin/forum/subcategoryView',
                [
                    'class'       => 'user',
                    'category'    => $category,
                    'mainCatName' => $mainCatName,
                ]
            );
        }

        return redirect('/admin/forum/subcategories/list');
    }

    /**
     * Edit a discussion based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function editDiscussion(Request $request, $id)
    {
        $class = 'user';
        $discussion = Models::discussion()->with('category')->where('id', $id)->first();
        $categories = Models::category()->get();

        if (!is_null($discussion)) {
            $discussion->created_by = User::where('id', $discussion->user_id)->value('username');

            if ($request->has('edit')) {
                $validator = \Validator::make($request->all(), [
                    'title'    => 'required|max:191',
                    'color'    => 'nullable|max:20',
                    'category' => 'required|int|digits_between:1,10',
                ]);

                $errors = empty($validator->errors()) ? null : $validator->errors();

                if (!$validator->fails()) {
                    if (isset($request->title)) {
                        $discussion->title = $request->title;
                        $discussion->slug = str_slug($request->title, '-');
                    }

                    if (isset($request->category)) {
                        $discussion->chatter_category_id = $request->category;
                    }

                    $discussion->color = isset($request->color) ? $request->color : null;
                    unset($discussion->created_by);

                    try {
                        $discussion->save();
                        $request->session()->flash('alert-success', __('custom.edit_success'));
                    } catch (QueryException $ex) {
                        Log::error($ex->getMessage());
                        $request->session()->flash('alert-danger', __('custom.edit_error'));
                    }
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors($errors)->withInput(Input::all());
            }

            return view('admin/forum/discussionEdit', compact('class', 'discussion', 'categories'));
        }

        return back();
    }

    /**
     * Edit a category based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function editCategory(Request $request, $id)
    {
        $class = 'user';
        $category = Models::category()->where('id', $id)->first();

        if (!is_null($category)) {
            if ($request->has('edit')) {
                $validator = \Validator::make($request->all(), [
                    'name'   => 'required|max:191',
                    'color'  => 'required|max:20',
                    'order'  => 'nullable|int|max:11|digits_between:1,10',
                ]);

                $errors = empty($validator->errors()) ? null : $validator->errors();

                if (!$validator->fails()) {
                    if (isset($request->name)) {
                        $category->name = $request->name;
                        $category->slug = str_slug($request->name, '-');
                    }

                    if (isset($request->order)) {
                        $category->order = $request->order;
                    }

                    if (isset($request->color)) {
                        $category->color = $request->color;
                    }

                    try {
                        $category->save();
                        $request->session()->flash('alert-success', __('custom.edit_success'));
                    } catch (QueryException $ex) {
                        Log::error($ex->getMessage());
                        $request->session()->flash('alert-danger', __('custom.edit_error'));
                    }
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors($errors)->withInput(Input::all());
            }

            return view('admin/forum/categoriesEdit', compact('class', 'category'));
        }

        return back();
    }

    /**
     * Edit a subcategory based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function editSubcategory(Request $request, $id)
    {
        $class = 'user';
        $category = Models::category()->where('id', $id)->where('parent_id', '!=', null)->first();
        $mainCategories = Models::category()->where('parent_id', null)->get();

        if (!is_null($category)) {
            if ($request->has('edit')) {
                $validator = \Validator::make($request->all(), [
                    'name'      => 'required|max:191',
                    'color'     => 'required|max:20',
                    'order'     => 'nullable|int|max:11|digits_between:1,10',
                    'parent_id' => 'required|int|exists:chatter_categories,id|digits_between:1,10'
                ]);

                $errors = empty($validator->errors()) ? null : $validator->errors();

                if (!$validator->fails()) {
                    if (isset($request->name)) {
                        $category->name = $request->name;
                        $category->slug = str_slug($request->name, '-');
                    }

                    if (isset($request->order)) {
                        $category->order = $request->order;
                    }

                    $category->color = $request->color;
                    $category->parent_id = $request->parent_id;

                    try {
                        $category->save();
                        $request->session()->flash('alert-success', __('custom.edit_success'));
                    } catch (QueryException $ex) {
                        Log::error($ex->getMessage());
                        $request->session()->flash('alert-danger', __('custom.edit_error'));
                    }
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors($errors)->withInput(Input::all());
            }

            return view('admin/forum/subcategoriesEdit', compact('class', 'category', 'mainCategories'));
        }

        return back();
    }

    /**
     * Delete a discussion based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function deleteDiscussion(Request $request, $id)
    {
        $discussion = Models::discussion()->with('category')->where('id', $id)->first();

        if (!is_null($discussion)) {
            try {
                $discussion->delete();
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));
        }

        return redirect('/admin/forum/discussions/list');
    }

    /**
     * Delete a category based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function deleteCategory(Request $request, $id)
    {
        $category = Models::category()->where('id', $id)->first();

        if (!is_null($category)) {
            try {
                $category->delete();
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));
        }

        return redirect('/admin/forum/categories/list');
    }

    /**
     * Delete a subcategory based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function deleteSubcategory(Request $request, $id)
    {
        $category = Models::category()->where('id', $id)->first();

        if (!is_null($category)) {
            try {
                $category->delete();
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));

            return redirect('/admin/forum/categories/list');
        }

        return redirect('/admin/forum/subcategories/list/'. $category->parent_id);
    }
}
