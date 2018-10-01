@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'manageRoles'])

    <form method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <div class="frame-wrap">
            <div class="frame">
                <div class="row">
                    <h3 class="col-lg-12">{{ __('custom.edit_role') }}</h3>
                </div>
                <div class="form-group row">
                    <label for="role_name" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control"
                            id="role_name"
                            name="name"
                            value="{{ $role[0]->name }}"
                        >
                    </div>
                    <span class="error">{{ $errors->first('name') }}</span>
                </div>
                <div class="form-group row">
                    <label for="role_active" class="col-sm-11 col-form-label">{{ utrans('custom.active') }}:</label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="role_active"
                                value="1"
                                {{ $role[0]->active ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="for_org" class="col-sm-11 col-form-label">{{ __('custom.for_org') }}:</label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="for_org"
                                id="for_org"
                                value="1"
                                {{ $role[0]->for_org ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="for_group" class="col-sm-11 col-form-label">{{ uctrans('custom.for_group') }}:</label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="for_group"
                                id="for_group"
                                value="1"
                                {{ $role[0]->for_group ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label
                        for="default_user"
                        class="col-sm-11 col-form-label"
                    >{{ __('custom.by_default') }} {{ __('custom.for') }} {{ ultrans('custom.users') }}:</label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="default_user"
                                id="default_user"
                                value="1"
                                {{ $role[0]->default_user ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label
                        for="default_org_admin"
                        class="col-sm-11 col-form-label"
                    >
                        {{ __('custom.by_default') }}
                        {{ __('custom.for') }}
                        {{ __('custom.admin_of') }}
                        {{ ultrans('custom.organisations') }}:
                    </label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="default_org_admin"
                                id="default_org_admin"
                                value="1"
                                {{ $role[0]->default_org_admin ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label
                        for="default_group_admin"
                        class="col-sm-11 col-form-label"
                    >
                        {{ __('custom.by_default') }}
                        {{ __('custom.for') }}
                        {{ __('custom.admin_of') }}
                        {{ ultrans('custom.groups') }}:
                    </label>
                    <div class="col-sm-1 text-right">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="default_group_admin"
                                id="default_group_admin"
                                value="1"
                                {{ $role[0]->default_group_admin ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-lg-12 text-right">
                        <a
                            href="{{ url('admin/roles') }}"
                            class="btn btn-primary"
                        >
                            {{ uctrans('custom.close') }}
                        </a>
                        <button type="submit" name="edit" class="btn btn-primary">{{ uctrans('custom.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
