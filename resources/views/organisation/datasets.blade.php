@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar-org')
        <div class="col-sm-9 col-xs-12 p-sm page-content">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-lg-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                            <div>
                                <div class="m-r-md p-h-xs search-field">
                                    <form method="GET" action="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">
                                        <input
                                            type="text"
                                            class="m-t-md m-b-md input-border-r-12 form-control"
                                            placeholder="{{ __('custom.search') }}"
                                            value="{{ isset($getParams['q']) ? $getParams['q'] : '' }}"
                                            name="q"
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
                </div>
                <div>
                @if ($resultsCount > 0)
                    <div class="m-r-md p-h-xs">
                        <p>{{ __('custom.list_order_by') }}:</p>
                        <ul class="nav sort-by p-l-r-none">
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'OrganisationController@datasets',
                                            array_merge(
                                                array_except(app('request')->input(), ['sort', 'order', 'page']),
                                                ['uri' => $organisation->uri, 'sort' => 'relevance', 'order' => 'asc']
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['sort']) && app('request')->input()['sort'] == 'relevance' &&
                                        isset(app('request')->input()['order']) && app('request')->input()['order'] == 'asc'
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
            <div class="articles">
            @if ($resultsCount > 0)
                <div class="col-lg-12 p-h-xxs p-l-r-none">
                    <h4>{{ $resultsCount }} {{ ultrans('custom.results_found', $resultsCount) }}</h4>
                </div>
                <div class="col-lg-12 p-l-r-none">
                    @if (isset($getParams['category']) && count($getParams['category']) > 0)
                        <div class="col-lg-3 p-h-xs">
                            <span class="h4">{{ __('custom.selected_topics') }}:</span>
                        </div>
                        <div class="col-lg-9 p-h-xs">
                            @foreach ($getParams['category'] as $selCategory)
                                <span class="badge badge-pill">{{ array_pluck($categories, 'name', 'id')[$selCategory] }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if (isset($getParams['tag']) && count($getParams['tag']) > 0)
                        <div class="col-lg-3 p-h-xs">
                            <span class="h4">{{ __('custom.selected_tags') }}:</span>
                        </div>
                        <div class="col-lg-9 p-h-xs">
                            @foreach ($getParams['tag'] as $selTag)
                                <span class="badge badge-pill">{{ array_pluck($tags, 'name', 'id')[$selTag] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @foreach ($datasets as $dataset)
                    <div class="col-sm-12 article m-t-lg m-b-md p-l-r-none">
                        <div class="art-heading-bar row">
                            <div class="col-sm-7 col-xs-12 p-l-r-none">
                                <div class="col-sm-2 col-xs-4 logo">
                                    <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">
                                        <img
                                            alt="{{ $organisation->name }}"
                                            class="img-responsive"
                                            src="{{ $organisation->logo }}"
                                        >
                                    </a>
                                </div>
                                <div class="socialPadding p-w-sm">
                                    <div class="social fb"><a href="#"><i class="fa fa-facebook"></i></a></div>
                                    <div class="social tw"><a href="#"><i class="fa fa-twitter"></i></a></div>
                                    <div class="social gp"><a href="#"><i class="fa fa-google-plus"></i></a></div>
                                </div>
                                <div class="sendMail m-r-sm">
                                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                </div>
                                @if ($approved)
                                    <div class="status p-w-sm">
                                        <span>{{ __('custom.approved') }} </span>
                                    </div>
                                @else
                                    <div class="status notApproved p-w-sm">
                                        <span>{{ __('custom.unapproved') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="follow pull-right">
                                @auth
                                    <form method="post">
                                        {{ csrf_field() }}
                                        @if (!in_array($dataset->id, $followed))
                                            <div>
                                                <button class="badge badge-pill" type="submit" name="follow" value="{{ $dataset->id }}">{{ utrans('custom.follow') }}</button>
                                            </div>
                                        @else
                                            <div>
                                                <button class="badge badge-pill" type="submit" name="unfollow" value="{{ $dataset->id }}">{{ uctrans('custom.stop_follow') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                @endauth
                            </div>
                        </div>
                        <div class="col-sm-12 p-l-r-none">
                            <h2><a href="{{ url('/organisation/'. $organisation->uri .'/dataset/'. $dataset->uri) }}">{{ $dataset->name }}</a></h2>
                            <p>{{ $dataset->descript }}</p>
                            <div class="col-sm-12 p-l-none">
                                <div class="tags pull-left">
                                    @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                        @foreach ($dataset->tags as $tag)
                                            <span class="badge badge-pill">{{ $tag->name }}</span>
                                        @endforeach
                                    @else
                                        <div class="p-h-xs"></div>
                                    @endif
                                </div>
                                <div class="pull-right">
                                    <span class="badge badge-pill">
                                        <a href="{{ url('/organisation/'. $organisation->uri .'/dataset/'. $dataset->uri) }}">{{ uctrans('custom.see_more') }}</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
            </div>

            @if (isset($pagination))
                <div class="row">
                    <div class="col-xs-12 text-center">
                        {{ $pagination->render() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
