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
                        <div class="m-r-md p-h-xs col-md-6 invite-choice">
                            <div>{{ __('custom.add_members') }}</div>
                            <ul class="input-border-r-12">
                                <li>
                                    <a
                                        class="black"
                                        href="{{ route('addOrgMembersNew', app('request')->only('uri')) }}"
                                    >{{ uctrans('custom.new_user') }}</a>
                                </li>
                                <li>
                                    <a class="black" href="{{ url('/user/registration') }}">
                                        {{ uctrans('custom.existing_user') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="black" href="#" data-toggle="modal" data-target="#invite-member">
                                        {{ uctrans('custom.invite_by_mail') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
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
                                    <div class="js-member-admin-controls">
                                        <button
                                            class="badge badge-pill m-r-md m-b-sm js-member-edit"
                                        >{{ __('custom.edit') }}</button>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="{{ route('delOrgMember', [
                                                    'id'        => $member->id,
                                                    'uri'       => $organisation->uri,
                                                ]) }}"
                                                data-confirm="{{ __('custom.delete_member') }}?"
                                            >{{ __('custom.remove') }}</a>
                                        </span>
                                    </div>
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
@endsection
