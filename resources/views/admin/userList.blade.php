@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'users'])
    <div class="row">
        <div class="col-xs-12 m-t-lg">
            <span class="my-profile m-l-sm">{{ uctrans('custom.users_list') }}</span>
        </div>
        <div class="col-md-3 col-sm-4 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn">
                <a
                    href="{{ url('/admin/users/create') }}"
                >{{ uctrans('custom.new_user') }}</a>
            </span>
            <span class="badge badge-pill m-t-lg new-data user-add-btn">
            <a
                data-toggle="modal"
                data-target="#invite"
            >{{ uctrans('custom.invite_by_mail') }}</a>
            </span>
        </div>
        <div class="col-lg-8 col-md-9 col-sm-8 col-xs-12 search-field admin">
            <form method="GET">
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
                @foreach (app('request')->except(['q', 'page']) as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $innerValue)
                            <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                        @endforeach
                    @else
                        <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                    @endif
                @endforeach
            </form>
        </div>
    </div>
    <div class="row">
        @include('partials.admin-sidebar', [
            'action' => 'Admin\UserController@list',
            'options' => ['organisation', 'role', 'approved', 'active', 'admin']
        ])
        <div class="col-sm-9 col-xs-12 list-orgs user-orgs user-list">
            @if (!empty($users))
                <div class="m-t-n-sm">
                    @include('partials.pagination')
                </div>
                @foreach ($users as $member)
                    <div class="col-xs-12 p-l-none">
                        <h3 class="m-b-md">{{
                            empty($member->firstname)
                            ? $member->username
                            : $member->firstname .' '. $member->lastname
                        }}</h3>
                        @if ($isAdmin)
                            <div class="js-member-admin-controls">
                                <a
                                    class="badge cust-btn badge-pill m-b-sm"
                                    href="{{ url('/admin/users/edit/'. $member->id) }}"
                                >{{ utrans('custom.edit') }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach
                @include('partials.pagination')
            @else
                <div class="row">
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                </div>
            @endif
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
                        <div class="form-group row m-b-md m-t-md">
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
                        <div class="form-group row m-b-md m-t-md">
                            <label for="approved" class="col-lg-3 col-sm-8 col-xs-12">{{ utrans('custom.approved') }}:</label>
                            <div class="col-lg-9 col-sm-4 col-xs-12 text-right">
                                <input
                                    type="checkbox"
                                    class="js-check form-control"
                                    id="approved"
                                    name="approved"
                                    value="1"
                                >
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
