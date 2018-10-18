@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar-org')
        <div class="col-sm-9 col-xs-12 page-content">
            <div class="row">
                <div class="col-md-9 col-xs-12 p-sm col-sm-offset-0 col-xs-offset-2">
                    @include('partials.org-type-bar', ['orgTypes' => $orgTypes, 'getParams' => ['type' => $organisation->type]])
                </div>

                <div class="col-xs-12 col-sm-offset-0 col-xs-offset-1">
                    <div class="row">
                        <div class="col-xs-12 filter-content">
                            <div class="org-nav-bar">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                        <li><a class="active" href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                        <li><a href="{{ url('/organisation/chronology/'. $organisation->uri ) }}">{{ __('custom.chronology') }}</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <div class="m-r-md p-h-xs search-field">
                                        <form method="GET" action="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">
                                            <input
                                                type="text"
                                                class="m-t-md m-b-md input-border-r-12 form-control js-ga-event"
                                                placeholder="{{ __('custom.search') }}"
                                                value="{{ isset($getParams['q']) ? $getParams['q'] : '' }}"
                                                name="q"
                                                data-ga-action="search"
                                                data-ga-label="data search"
                                                data-ga-category="data"
                                            >
                                            @foreach (array_except($getParams, ['q', 'page']) as $qp => $qpv)
                                                @if (is_array($qpv))
                                                    @foreach ($qpv as $pk => $pv)
                                                        <input type="hidden" name="{{ $qp .'['. $pk .']' }}" value="{{ $pv }}"/>
                                                    @endforeach
                                                @else
                                                    <input type="hidden" name="{{ $qp }}" value="{{ $qpv }}"/>
                                                @endif
                                            @endforeach
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                        @if ($resultsCount > 0)
                            <div class="m-r-md p-h-xs">
                                <p>{{ __('custom.order_by') }}:</p>
                                <ul class="nav sort-by p-l-r-none">
                                    <li>
                                        <a
                                            href="{{
                                                action(
                                                    'OrganisationController@datasets',
                                                    array_merge(
                                                        array_except(app('request')->input(), ['sort', 'order', 'page']),
                                                        ['uri' => $organisation->uri, 'sort' => 'relevance']
                                                    )
                                                )
                                            }}"
                                            class="{{
                                                isset(app('request')->input()['sort']) && app('request')->input()['sort'] == 'relevance'
                                                    ? 'active'
                                                    : ''
                                            }}"
                                        >{{ __('custom.relevance') }}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{
                                                action(
                                                    'OrganisationController@datasets',
                                                    array_merge(
                                                        array_except(app('request')->input(), ['sort', 'order', 'page']),
                                                        ['uri' => $organisation->uri, 'sort' => 'name', 'order' => 'asc']
                                                    )
                                                )
                                            }}"
                                            class="{{
                                                isset(app('request')->input()['sort']) && app('request')->input()['sort'] == 'name' &&
                                                isset(app('request')->input()['order']) && app('request')->input()['order'] == 'asc'
                                                    ? 'active'
                                                    : ''
                                            }}"
                                        >{{ __('custom.names_asc') }}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{
                                                action(
                                                    'OrganisationController@datasets',
                                                    array_merge(
                                                        array_except(app('request')->input(), ['sort', 'order', 'page']),
                                                        ['uri' => $organisation->uri, 'sort' => 'name', 'order' => 'desc']
                                                    )
                                                )
                                            }}"
                                            class="{{
                                                isset(app('request')->input()['sort']) && app('request')->input()['sort'] == 'name' &&
                                                isset(app('request')->input()['order']) && app('request')->input()['order'] == 'desc'
                                                    ? 'active'
                                                    : ''
                                            }}"
                                        >{{ __('custom.names_desc') }}</a>
                                    </li>
                                    <li>
                                        <a
                                            href="{{
                                                action(
                                                    'OrganisationController@datasets',
                                                    array_merge(
                                                        array_except(app('request')->input(), ['sort', 'order', 'page']),
                                                        ['uri' => $organisation->uri, 'sort' => 'updated_at', 'order' => 'desc']
                                                    )
                                                )
                                            }}"
                                            class="{{
                                                isset(app('request')->input()['sort']) && app('request')->input()['sort'] == 'updated_at' &&
                                                isset(app('request')->input()['order']) && app('request')->input()['order'] == 'desc'
                                                    ? 'active'
                                                    : ''
                                            }}"
                                        >{{ __('custom.last_change') }}</a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
                @if (isset($buttons['add']) && $buttons['add'])
                    <div class="col-xs-12 text-right">
                        <span class="badge badge-pill m-t-md">
                            <a href="{{ url($buttons['addUrl']) }}">{{ __('custom.add_new_dataset') }}</a>
                        </span>
                    </div>
                @endif
                <div class="articles">
                @if ($resultsCount > 0)
                    <div class="col-xs-12 p-h-xxs ">
                        <h4>{{ $resultsCount }} {{ ultrans('custom.results_found', $resultsCount) }}</h4>
                    </div>
                    <div class="col-xs-12">
                        @if (isset($getParams['category']) && count($getParams['category']) > 0)
                            <div class="col-xs-3 p-h-xs">
                                <span class="h4">{{ __('custom.selected_topics') }}:</span>
                            </div>
                            <div class="col-xs-9 p-h-xs">
                                <form method="post">
                                    {{ csrf_field() }}
                                    @foreach ($getParams['category'] as $selCategory)
                                        <span class="badge badge-pill">
                                            {{ array_pluck($categories, 'name', 'id')[$selCategory] }}&nbsp;
                                            @if (isset($buttons[$selCategory]['followCategory']) && $buttons[$selCategory]['followCategory'])
                                                <button class="badge badge-pill badge-follow" type="submit" name="followCategory" value="{{ $selCategory }}"
                                                    title="{{ uctrans('custom.follow') }}">
                                                    <i class="fa fa-plus-circle"></i>
                                                </button>
                                            @elseif (isset($buttons[$selCategory]['unfollowCategory']) && $buttons[$selCategory]['unfollowCategory'])
                                                <button class="badge badge-pill badge-follow" type="submit" name="unfollowCategory" value="{{ $selCategory }}"
                                                title="{{ uctrans('custom.stop_follow') }}">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            @endif
                                            <a href="{{ action('OrganisationController@datasets', array_merge(
                                                        array_except(app('request')->input(), ['category', 'page']),
                                                        (array_diff($getParams['category'], [$selCategory])
                                                            ? ['uri' => $organisation->uri, 'category' => array_diff($getParams['category'], [$selCategory])]
                                                            : ['uri' => $organisation->uri])
                                                    )) }}"
                                            ><i class="fa fa-remove"></i></a>
                                        </span>
                                    @endforeach
                                </form>
                            </div>
                        @endif
                        @if (isset($getParams['tag']) && count($getParams['tag']) > 0)
                            <div class="col-xs-3 p-h-xs">
                                <span class="h4">{{ __('custom.selected_tags') }}:</span>
                            </div>
                            <div class="col-xs-9 p-h-xs">
                                <form method="post">
                                    {{ csrf_field() }}
                                    @foreach ($getParams['tag'] as $selTag)
                                        <span class="badge badge-pill">
                                            {{ array_pluck($tags, 'name', 'id')[$selTag] }}&nbsp;
                                            @if (isset($buttons[$selTag]['followTag']) && $buttons[$selTag]['followTag'])
                                                <button class="badge badge-follow" type="submit" name="followTag" value="{{ $selTag }}"
                                                title="{{ uctrans('custom.follow') }}">
                                                    <i class="fa fa-plus-circle"></i>
                                                </button>
                                            @elseif (isset($buttons[$selTag]['unfollowTag']) && $buttons[$selTag]['unfollowTag'])
                                                <button class="badge badge-follow" type="submit" name="unfollowTag" value="{{ $selTag }}"
                                                title="{{ uctrans('custom.stop_follow') }}">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            @endif
                                            <a href="{{ action('OrganisationController@datasets', array_merge(
                                                        array_except(app('request')->input(), ['tag', 'page']),
                                                        (array_diff($getParams['tag'], [$selTag])
                                                            ? ['uri' => $organisation->uri, 'tag' => array_diff($getParams['tag'], [$selTag])]
                                                            : ['uri' => $organisation->uri])
                                                    )) }}"
                                            ><i class="fa fa-remove"></i></a>
                                        </span>
                                    @endforeach
                                </form>
                            </div>
                        @endif
                    </div>
                    @if (isset($pagination))
                        <div class="col-xs-12 m-t-md text-center">
                            {{ $pagination->render() }}
                        </div>
                    @endif
                    @foreach ($datasets as $dataset)
                        <div class="col-xs-12 article m-t-md m-b-md">
                            <div class="art-heading-bar row">
                                <div class="col-sm-7 col-xs-12 p-l-r-none">
                                    <div class="col-sm-2 col-xs-4 logo">
                                        <a href="{{ url('/organisation/profile/'. $organisation->uri) }}" title="{{ $organisation->name }}">
                                            <img class="img-responsive" src="{{ $organisation->logo }}" alt="{{ $organisation->name }}">
                                        </a>
                                    </div>
                                    <div class="p-w-sm">
                                        @include(
                                            'partials.social-icons',
                                            ['shareUrl' => route('orgViewDataset', ['uri' => $dataset->uri])]
                                        )
                                    </div>
                                    @if ($approved)
                                        <div class="status p-w-sm">
                                            <span>{{ __('custom.approved') }} </span>
                                        </div>
                                    @else
                                        <div class="status notApproved p-w-sm p-l-r-none">
                                            <span>{{ __('custom.unapproved') }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="follow pull-right">
                                    <form method="post">
                                        {{ csrf_field() }}
                                        @if (isset($buttons[$dataset->id]['follow']) && $buttons[$dataset->id]['follow'])
                                            <div>
                                                <button class="badge badge-pill" type="submit" name="follow" value="{{ $dataset->id }}">{{ utrans('custom.follow') }}</button>
                                            </div>
                                        @elseif (isset($buttons[$dataset->id]['unfollow']) && $buttons[$dataset->id]['unfollow'])
                                            <div>
                                                <button class="badge badge-pill" type="submit" name="unfollow" value="{{ $dataset->id }}">{{ uctrans('custom.stop_follow') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <a href="{{ route('orgViewDataset', array_merge(app('request')->input(), ['uri' => $dataset->uri])) }}">
                                    <h2 class="{{ $dataset->reported ? 'error' : '' }}">{{ $dataset->name }}</h2>
                                </a>
                                <p>{!! nl2br(e($dataset->descript)) !!}</p>
                                <div class="col-xs-12 p-l-none">
                                    <div class="tags pull-left">
                                        @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                            @foreach ($dataset->tags as $tag)
                                                <span class="badge badge-pill m-b-sm">{{ $tag->name }}</span>
                                            @endforeach
                                        @else
                                            <div class="p-h-xs"></div>
                                        @endif
                                    </div>
                                    <div class="pull-right">
                                        <span class="badge badge-pill">
                                            <a href="{{ route('orgViewDataset', array_merge(app('request')->input(), ['uri' => $dataset->uri])) }}">
                                                {{ uctrans('custom.see_more') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-xs-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
                </div>
                @if ($resultsCount > 0)
                    @include('partials.pagination')
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
