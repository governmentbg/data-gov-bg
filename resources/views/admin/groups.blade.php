@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'group'])
        <div class="col-xs-12 m-t-lg p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.groups_list') }}</span>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('/admin/groups/register') }}">{{ __('custom.add_new_group') }}</a>
                </span>
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field admin">
                <form method="GET" action="{{ url('/admin/groups/search') }}">
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
        </div>
        @include('partials.pagination')
        <div class="col-xs-12 m-t-md list-orgs user-orgs">
            <div class="row">
                @if (count($groups))
                    @foreach ($groups as $group)
                        <div class="col-md-4 col-sm-12 org-col">
                                <div class="cust-tooltip user">{{ $group->name }}</div>
                            <div class="col-xs-12 m-t-lg">
                                <a href="{{ url('/admin/groups/view/'. $group->uri) }}">
                                    <img class="img-responsive logo" src="{{ $group->logo }}"/>
                                </a>
                            </div>
                            <div class="col-xs-12">
                                <a href="{{ url('/admin/groups/view/'. $group->uri) }}"><h3 class="org-name">{{ $group->name }}</h3></a>
                                <div class="org-desc">{!! nl2br(e($group->description)) !!}</div>
                                <p class="text-right show-more">
                                    <a href="{{ url('/admin/groups/view/'. $group->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                </p>
                            </div>
                            <div class="col-xs-12 ch-del-btns">
                                <div class="row">
                                    @if (\App\Role::isAdmin())
                                        <form method="POST" action="{{ url('/admin/groups/edit/'. $group->uri) }}">
                                            {{ csrf_field() }}
                                            <div class="col-xs-6">
                                                <button type="submit">{{ uctrans('custom.edit') }}</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ url('/admin/groups/delete/'. $group->id) }}">
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
