@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => 'group'])
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{uctrans('custom.groups_list')}}</span>
        </div>
        @include('partials.pagination')
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left">
                @if (!empty($buttons['add']))
                    <span class="badge badge-pill m-t-md new-data user-add-btn">
                        <a href="{{ url('/user/groups/register') }}">{{ __('custom.add_new_group') }}</a>
                    </span>
                @endif
            </div>
            @if ($buttons['view'])
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field p-l-lg">
                    <form method="GET" action="{{ url('/user/groups/search') }}">
                        <input
                            type="text"
                            class="m-t-md input-border-r-12 form-control js-ga-event"
                            placeholder="{{ __('custom.search') }}"
                            value="{{ isset($search) ? $search : '' }}"
                            name="q"
                            data-ga-action="search"
                            data-ga-label="data search"
                            data-ga-category="data"
                        >
                    </form>
                </div>
            @endif
        </div>
        <div class="col-xs-12 m-t-md list-orgs user-orgs">
            <div class="row">
                @if (count($groups))
                    @foreach ($groups as $group)
                        @if (!empty($buttons[$group->uri]['view']))
                            <div class="col-md-4 col-sm-12 org-col">
                                <div class="col-xs-12 m-t-lg">
                                    <a href="{{ url('/user/groups/view/'. $group->uri) }}">
                                        <img class="img-responsive logo" src="{{ $group->logo }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groups/view/'. $group->uri) }}"><h3 class="org-name">{{ $group->name }}</h3></a>
                                    <div class="org-desc">{!! nl2br(e($group->description)) !!}</div>
                                            <p class="text-right show-more">
                                                <a href="{{ url('/user/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                            </p>
                                </div>
                                <div class="col-xs-12 ch-del-btns">
                                    <div class="row">
                                        @if (!empty($buttons[$group->uri]['edit']))
                                            <form method="POST" action="{{ url('/user/groups/edit/'. $group->uri) }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6">
                                                    <button type="submit">{{ uctrans('custom.edit') }}</button>
                                                </div>
                                            </form>
                                        @endif
                                        @if (!empty($buttons[$group->uri]['delete']))
                                            <form method="POST" action="{{ url('/user/groups/delete/'. $group->id) }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        type="submit"
                                                        name="delete"
                                                        class="del-btn"
                                                        data-confirm="{{ __('custom.delete_group_confirm') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                                <input class="user-org-del" type="hidden" name="org_id" value="{{ $group->id }}">
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-md text-center no-info">
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
@endsection
