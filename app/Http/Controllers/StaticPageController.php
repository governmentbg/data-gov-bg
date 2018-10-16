<?php

namespace App\Http\Controllers;

use App\Page;
use App\Section;
use Illuminate\Http\Request;
use DevDojo\Chatter\Models\Models;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Api\PageController as ApiPage;
use App\Http\Controllers\Api\ThemeController as ApiTheme;
use App\Http\Controllers\Api\SectionController as ApiSection;
use DevDojo\Chatter\Controllers\ChatterDiscussionController;

class StaticPageController extends Controller {

    public $page = 1;
    public $perPage = 5;
    public $class = null;
    public $section = null;
    public $activeSections = [];
    public $sectionSlugName = null;

    public function show(Request $request, $sectionSlugName)
    {
        $this->sectionSlugName = $sectionSlugName;
        $this->activeSections = $this->getActiveSections();
        $this->page = isset($request->spage) ? $request->spage : 1;
        $this->section = $this->getMainSection($request->section);

        if ($this->section) {
            $this->class = isset(ApiTheme::getThemeClasses()[$this->section->theme])
                ? ApiTheme::getThemeClasses()[$this->section->theme]
                : null;

            if (isset($request->item)) {
                return $this->page($request->item);
            }

            if (isset($request->subsection)) {
                return $this->subsection($request->subsection);
            }

            if (isset($request->section)) {
                return $this->section();
            }
        }

        return back();
    }

    public function section()
    {
        if ($this->section) {
            $subParams = ['criteria' => ['section_id' => $this->section->id]];
            $subsections = $this->getSubsections($subParams, false);

            if ($subsections) {
                $this->section->subsections = $subsections;

                foreach ($this->section->subsections as $index => $subsec) {
                    $this->section->subsections[$index]->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section'    => $this->section->id,
                            'subsection' => $subsec->id
                        ]
                    );
                }
            }

            $paginatedPages = $this->getSectionPages(
                $this->section->id,
                ['section' => $this->section->id],
                $this->page
            );
            $this->section->pages = $paginatedPages['items'];

            if (!empty($this->section->pages)) {
                foreach ($this->section->pages as $index => $page) {
                    $this->section->pages[$index]->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section' => $this->section->id,
                            'item'    => $page->id
                        ]
                    );
                }

                if (count($this->section->pages) == 1) {
                    return redirect($this->section->pages[0]->base_url);
                }
            }
        }

        $discussion = $this->getForumDiscussion($this->section->forum_link);
        $viewParams = [
            'class'          => $this->class,
            'activeSections' => $this->activeSections,
            'section'        => $this->section,
            'pagination'     => isset($paginatedPages) ? $paginatedPages['pagination'] : null,
        ];

        return view (
            'static.section',
            !empty($discussion)
                ? array_merge($viewParams, $discussion)
                : $viewParams
        );
    }

    public function subsection($id)
    {
        $subParams = ['criteria' => ['section_id' => $this->section->id]];
        $subsections = $this->getSubsections($subParams, false);

        $subsection = $subsections
            ? array_filter(
                $subsections,
                function ($item) use ($id) {
                    $item->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section'    => $item->parent_id,
                            'subsection' => $item->id
                        ]
                    );

                    return $item->id == $id;
                }
            )
            : null;

        $subsection = empty($subsection) ? null : array_values($subsection)[0];

        if ($subsection) {
            $subsection->base_url = $this->getBaseURL(
                $this->sectionSlugName,
                [
                    'section'    => $subsection->parent_id,
                    'subsection' => $subsection->id
                ]
            );

            $paginatedPages = $this->getSectionPages(
                $subsection->id,
                ['section' => $subsection->parent_id, 'subsection' => $subsection->id],
                $this->page
            );
            $subsection->pages = $paginatedPages['items'];

            if (!empty($subsection->pages)) {
                foreach ($subsection->pages as $index => $page) {
                    $subsection->pages[$index]->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section'    => $subsection->parent_id,
                            'subsection' => $subsection->id,
                            'item'       => $page->id,
                        ]
                    );
                }

                if (count($subsection->pages) == 1) {
                    return redirect($subsection->pages[0]->base_url);
                }
            }
        }

        $discussion = $this->getForumDiscussion($subsection ? $subsection->forum_link : null);
        $viewParams = [
            'class'          => $this->class,
            'activeSections' => $this->activeSections,
            'subsection'     => $subsection,
            'subsections'    => $subsections,
            'pagination'     => isset($paginatedPages) ? $paginatedPages['pagination'] : null,
        ];

        return view (
            'static.subsection',
            !empty($discussion)
                ? array_merge($viewParams, $discussion)
                : $viewParams
        );
    }

    public function page($id)
    {
        $req = Request::create('/api/listPages', 'POST', ['criteria' => ['page_id' => $id]]);
        $api = new ApiPage($req);
        $result = $api->listPages($req)->getData();
        $page = isset($result->pages[0]) ? $result->pages[0] : null;
        $sectionId = $page ? $page->section_id : null;

        $subParams = ['criteria' => ['section_id' => $this->section->id]];
        $subsections = $this->getSubsections($subParams, false);

        $subsection = $subsections
            ? array_filter(
                $subsections,
                function ($item) use ($sectionId) {
                    $item->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section'    => $item->parent_id,
                            'subsection' => $item->id
                        ]
                    );

                    return $item->id == $sectionId;
                }
            )
            : null;

        $subsection = empty($subsection) ? null : array_values($subsection)[0];
        $pages = $subsection ? $subsection->pages : $this->section->pages;

        if (!empty($pages)) {
            array_walk_recursive(
                $pages, function ($item) use ($subsection) {
                    $item->base_url = $this->getBaseURL(
                        $this->sectionSlugName,
                        [
                            'section'    => $this->section->id,
                            'subsection' => isset($subsection->id) ? $subsection->id : null,
                            'item'       => $item->id,
                        ]
                    );
                }
            );
        }

        $title = $page ? $page->head_title : null;
        $keywords = $page ? $page->meta_keywords : null;
        $description = $page ? $page->meta_description : null;

        $discussion = $this->getForumDiscussion($page ? $page->forum_link : null);
        $viewParams = [
            'class'          => $this->class,
            'activeSections' => $this->activeSections,
            'page'           => $page,
            'pages'          => $pages,
            'description'    => $description,
            'subsections'    => $subsections,
            'keywords'       => $keywords,
            'title'          => $title
        ];

        return view (
            'static.page',
            !empty($discussion)
                ? array_merge($viewParams, $discussion)
                : $viewParams
        );
    }

    public function getBaseURL($name, $params = [])
    {
        return str_slug($name) .'?'. http_build_query($params);
    }

    public function getSectionPages($secId, $getParams = [], $page = null)
    {
        $result = ['items' => [], 'pagination' => null];

        $req = Request::create(
            '/api/listPages',
            'POST',
            [
                'criteria' => [
                    'section_id' => $secId,
                    'active'     => 1
                ],
                'records_per_page' => $this->perPage,
                'page_number'      => !empty($page) ? $page : 1,
            ]
        );

        $api = new ApiPage($req);
        $apiResult = $api->listPages($req)->getData();
        $apiResult = !empty($apiResult->pages) ? $apiResult : null;

        if ($apiResult) {
            $paginationData = $this->getPaginationData(
                $apiResult->pages,
                $apiResult->total_records,
                $getParams,
                $this->perPage,
                'spage'
            );

            $result['items'] = $paginationData['items'];
            $result['pagination'] = $paginationData['paginate'] instanceof LengthAwarePaginator && $paginationData['paginate']->hasPages()
                ? $paginationData['paginate']
                : null;
        }

        return $result;
    }

    public function getMainSection($id)
    {
        $criteria = ['criteria' => ['id' => $id]];
        $rq = Request::create('/api/listSections', 'POST', $criteria);
        $api = new ApiSection($rq);
        $result = $api->listSections($rq)->getData();

        return !empty($result->sections) ? $result->sections[0] : null;
    }

    public function getSubsections($criteria, $first = false)
    {
        $request = Request::create('/api/listSubsections', 'POST', $criteria);
        $api = new ApiSection($request);
        $apiResult = $api->listSubsections($request)->getData();
        $result = !empty($apiResult->subsections) ? $apiResult->subsections : null;

        if ($result) {
            return $first ? $result[0] : $result;
        }

        return $result;
    }

    public function getForumDiscussion($link)
    {
        if ($link) {
            $segments = explode('/', parse_url($link)['path']);

            if (count($segments) > 2) {
                $discSlug = $segments[count($segments) - 1];
                $categorySlug = $segments[count($segments) - 2];

                $disc = Models::discussion()->where('slug', $discSlug)->count();
                $discCategory = Models::category()->where('slug', $categorySlug)->count();

                if ($disc && $discCategory) {
                    $rq = Request::create($link, 'GET', []);
                    $chatterDisc = new ChatterDiscussionController($rq);
                    $discussion = $chatterDisc->show($categorySlug, $discSlug)->getData();

                    return $discussion;
                }
            }
        }

        return [];
    }
}
