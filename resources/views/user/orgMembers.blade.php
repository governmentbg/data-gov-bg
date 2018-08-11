@extends('layouts.app')

@section('content')
<div class="container">
    <div class="sidenav js-sidenav p-l-r-none hidden-lg hidden-md hidden-sm" id="sidebar-wrapper">
        <ul class="nav">
            <li>
                <span>{{ __('custom.filters') }}</span>
                <button
                    type="button"
                    class="navbar-toggle btn-sidebar pull-right"
                    data-toggle="collapse"
                    data-target="#sidebar-wrapper"
                ><span><i class="fa fa-angle-left"></i></span></button>
                <ul class="sidebar-submenu">
                    @foreach ($roles as $role)
                        <li>
                            <a
                                class="{{ $filter == $role->id ? 'active' : null }}"
                                href="{{ route('userOrgMembersView', [
                                    'uri'       => $organisation->uri,
                                    'filter'    => $filter == $role->id ? null : $role->id,
                                    'keywords'  => $keywords,
                                ]) }}"
                            >{{ $role->name }}</a>
                        </li>
                    @endforeach
                    <li>
                        <a
                            class="{{ $filter == 'for_approval' ? 'active' : null }}"
                            href="{{ route('userOrgMembersView', [
                                'uri'       => $organisation->uri,
                                'filter'    => $filter == 'for_approval' ? null : 'for_approval',
                                'keywords'  => $keywords,
                            ]) }}"
                        >{{ __('custom.for_approval') }}</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'members', 'organisation' => $organisation])
    <div class="row">
        <div class="col-xs-12 m-t-md m-b-md">
            <div class="row">
                <div class="col-sm-3 sidenav">
                    <ul class="nav">
                        <li>
                            <ul class="sidebar-submenu open">
                                @foreach ($roles as $role)
                                    <li>
                                        <a
                                            class="{{ $filter == $role->id ? 'active' : null }}"
                                            href="{{ route('userOrgMembersView', [
                                                'uri'       => $organisation->uri,
                                                'filter'    => $filter == $role->id ? null : $role->id,
                                                'keywords'  => $keywords,
                                            ]) }}"
                                        >{{ $role->name }}</a>
                                    </li>
                                @endforeach
                                <li>
                                    <a
                                        class="{{ $filter == 'for_approval' ? 'active' : null }}"
                                        href="{{ route('userOrgMembersView', [
                                            'uri'       => $organisation->uri,
                                            'filter'    => $filter == 'for_approval' ? null : 'for_approval',
                                            'keywords'  => $keywords,
                                        ]) }}"
                                    >{{ __('custom.for_approval') }}</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <div class="org m-t-lg">
                        <img src="{{ $organisation->logo }}">
                        <h2>{{ $organisation->name }}</h2>
                        <h4>{{ truncate($organisation->descript, 150) }}</h4>
                    </div>
                </div>
                <div class="navbar-header hidden-lg hidden-md hidden-sm p-l-r-none sidebar-open">
                    <button
                        type="button"
                        class="navbar-toggle btn-sidebar"
                        data-toggle="collapse"
                        data-target="#sidebar-wrapper"
                    ><span><i class="fa fa-angle-right"></i></span></button>
                </div>
                <div class="col-sm-9 cl-xs-12">
                    <div class="filter-content tex">
                        <div class="p-l-r-none m-b-sm col-md-6">
                            <form class="js-keywords-form">
                                @foreach (app('request')->except(['keywords']) as $key => $value)
                                    <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                                @endforeach
                                <input name="keywords" class="rounded-input" type="text" value="{{ $keywords }}">
                                <input type="submit" class="hidden">
                            </form>
                        </div>
                        @if ($isAdmin)
                            <div class="m-r-md p-h-xs col-md-6 invite-choice">
                                <div>{{ __('custom.add_members') }}</div>
                                <ul class="input-border-r-12">
                                    <li>
                                        <a
                                            class="black"
                                            href="{{ url('/user/organisations/members/addNew/'. $organisation->uri) }}"
                                        >{{ uctrans('custom.new_user') }}</a>
                                    </li>
                                    <li>
                                        <a class="black" data-toggle="modal" data-target="#invite-existing">
                                            {{ uctrans('custom.existing_user') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="black" data-toggle="modal" data-target="#invite">
                                            {{ uctrans('custom.invite_by_mail') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="col-xs-12 page-content text-left p-l-none">
                        @if (!empty($members))
                            @foreach ($members as $member)
                                <div class="col-xs-12 p-l-none">
                                    <h3>{{
                                        empty($member->firstname)
                                        ? $member->username
                                        : $member->firstname .' '. $member->lastname
                                    }}</h3>
                                    @if ($isAdmin)
                                        <div class="js-member-admin-controls">
                                            <button
                                                class="badge badge-pill m-r-md m-b-sm js-member-edit"
                                            >{{ __('custom.edit') }}</button>
                                            <form method="POST" class="inline-block">
                                                {{ csrf_field() }}
                                                <button
                                                    class="badge badge-pill m-b-sm"
                                                    type="submit"
                                                    name="delete"
                                                    onclick="return confirm('Изтриване на данните?');"
                                                >{{ __('custom.remove') }}</button>
                                                <input name="user_id" type="hidden" value="{{ $member->id }}">
                                            </form>
                                        </div>
                                    @endif
                                    <div class="js-member-edit-controls m-b-sm hidden">
                                        <form method="POST" class="member-edit-form">
                                            {{ csrf_field() }}
                                            <input name="org_id" type="hidden" value="{{ $organisation->id }}">
                                            <input name="user_id" type="hidden" value="{{ $member->id }}">
                                            <select
                                                class="form-control js-select"
                                                name="role_id"
                                            >
                                                @foreach ($roles as $role)
                                                    <option
                                                        value="{{ $role->id }}"
                                                        {{ $member->role_id == $role->id ? 'selected' : null }}
                                                    >{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button
                                                type="submit"
                                                class="badge badge-pill m-t-sm m-r-md"
                                                name="edit_member"
                                            >{{ __('custom.save') }}</button>
                                            <button
                                                type="button"
                                                class="badge badge-pill m-t-sm js-member-cancel"
                                            >{{ __('custom.cancel') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            @if (isset($pagination))
                                <div class="row">
                                    <div class="col-xs-12 text-center pagination">
                                        {{ $pagination->links(null, app('request')->except(['page'])) }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="m-t-xl text-center">
                                {{ __('custom.no_info') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="invite-existing" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('custom.close') }}</span></button>
                    <h2>{{ __('custom.add_user') }}</h2>
                </div>
                <div class="modal-body">
                    <form method="POST" class="form-horisontal">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label for="role" class="col-lg-2 col-form-label">{{ __('custom.name') }}: </label>
                            <div class="col-lg-10">
                                <select
                                    class="js-ajax-autocomplete form-control"
                                    data-url="{{ url('/api/searchUsers') }}"
                                    data-post="{{ json_encode(['api_key' => \Auth::user()->api_key]) }}"
                                    data-parent="#invite-existing"
                                    name="user"
                                    data-placeholder="{{ __('custom.select_user') }}"
                                    id="user"
                                >
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="role" class="col-lg-2 col-form-label">{{ __('custom.roles') }}: </label>
                            <div class="col-lg-10">
                                <select
                                    class="js-select form-control"
                                    name="role"
                                    data-placeholder="{{ __('custom.select_role') }}"
                                    id="role"
                                >
                                    <option></option>
                                    @foreach ($roles as $role)
                                        <option
                                            value="{{ $role->id }}"
                                        >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="m-l-md btn btn-danger" data-dismiss="modal">{{ __('custom.close') }}</button>
                                <button type="submit" name="invite_existing" class="m-l-md btn btn-custom">{{ __('custom.send') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="invite" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('custom.close') }}</span></button>
                    <h2>{{ __('custom.add_user') }}</h2>
                </div>
                <div class="modal-body">
                    <form method="POST" class="form-horisontal">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label for="email" class="col-lg-2 col-form-label">{{ __('custom.email') }}: </label>
                            <div class="col-lg-10">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="input-border-r-12 form-control"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="role" class="col-lg-2 col-form-label">{{ __('custom.roles') }}: </label>
                            <div class="col-lg-10">
                                <select class="js-select form-control" name="role" id="role">
                                    @foreach($roles as $role)
                                        <option
                                            value="{{ $role->id }}"
                                        >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="m-l-md btn btn-danger" data-dismiss="modal">{{ __('custom.close') }}</button>
                                <button type="submit" name="invite" class="m-l-md btn btn-custom">{{ __('custom.send') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
