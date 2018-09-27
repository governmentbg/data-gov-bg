<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Category;
use App\HelpPage;
use App\HelpSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\HelpController as ApiHelp;

class HelpController extends AdminController
{
    public static function getHelpSectionTransFields()
    {
        return [
            [
                'label'    => 'custom.name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
        ];
    }

    /**
     *  show list of help sections
     */
    public function listSections(Request $request)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key' => Auth::user()->api_key,
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $helpSections = $result->success ? $result->sections : [];

        return view('admin/helpList', compact('helpSections'));
    }

    /**
     * show form for creation of help sections
     */
    public function addHelpSecton(Request $request)
    {
        if ($request->has('back')) {
            return redirect('admin/help/list');
        }

        if ($request->has('create')) {
            $rq = Request::create('/api/addHelpSection', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'name'      => $request->offsetGet('name'),
                    'parent_id' => $request->offsetGet('parent_id'),
                    'active'    => $request->offsetGet('active') ?: false,
                    'ordering'  => $request->offsetGet('ordering'),
                ],
            ]);
            $api = new ApiHelp($rq);
            $result = $api->addHelpSection($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('admin/help/list');
            }

            $request->session()->flash('alert-danger', __('custom.add_error'));

            return redirect()->back()->withErrors($result->errors);
        }

        return view('admin/addHelpSection', [
            'fields'    => self::getHelpSectionTransFields(),
            'parents'   => self::getMainSections(),
            'ordering'  => Category::getOrdering(),
        ]);
    }

    public function deleteHelpSection(Request $request, $id)
    {
        if ($this->delete($id)) {
            $request->session()->flash('alert-success', __('custom.delete_success'));
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));
        }

        return redirect('admin/help/list');
    }

    /**
     * show form for help section edit page
     */
    public function editHelpSection(Request $request, $id)
    {
        $section = HelpSection::find($id);

        return view('admin/editHelpSection', [
            'section'   => $section,
            'fields'    => self::getHelpSectionTransFields(),
            'parents'   => self::getMainSections(),
            'ordering'  => Category::getOrdering(),
        ]);
    }

    /**
     * show view of help section
     */
    public function viewHelpSection(Request $request, $id)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'id'        => $id
            ]
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $section = $result->success ? $result->sections[0] : [];

        return view('admin/viewHelpSection', compact('section'));
    }

    public static function getMainSections()
    {
        $sections = HelpSection::where('parent_id', null)->get() ?: [];

        return $sections;
    }

    public function delete($id)
    {
        $rq = Request::create('/api/deleteHelpSection', 'POST', ['api_key' => Auth::user()->api_key, 'id' => $id]);
        $api = new ApiHelp($rq);
        $result = $api->deleteHelpSection($rq)->getData();

        if ($result->success) {
            return true;
        }

        return false;
    }
}
