@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'manageRoles'])

    <div class="frame-wrap">
        <div class="frame">
            <div class="row">
                <h3 class="col-lg-12">{{ __('custom.view_role') }}</h3>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                <div class="col-sm-9">
                    {{ $role[0]->name }}
                </div>
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
                            disabled
                        >
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="for_org" class="col-sm-11 col-form-label">{{ utrans('custom.for_org') }}:</label>
                <div class="col-sm-1 text-right">
                    <div class="js-check">
                        <input
                            type="checkbox"
                            name="for_org"
                            id="for_org"
                            value="1"
                            {{ $role[0]->for_org ? 'checked' : '' }}
                            disabled
                        >
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="for_group" class="col-sm-11 col-form-label">{{ utrans('custom.for_group') }}:</label>
                <div class="col-sm-1 text-right">
                    <div class="js-check">
                        <input
                            type="checkbox"
                            name="for_group"
                            id="for_group"
                            value="1"
                            {{ $role[0]->for_group ? 'checked' : '' }}
                            disabled
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
                            disabled
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
                            disabled
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
                            disabled
                        >
                    </div>
                </div>
            </div>
            <div class="form-group text-right">
                @if (\App\Role::isAdmin())
                    <div class="text-right">
                        <div class="row">
                            <form
                                method="POST"
                                class="inline-block"
                                action="{{ url('admin/roles/edit/'. $role[0]->id) }}"
                            >
                                {{ csrf_field() }}
                                <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                <input type="hidden" name="view" value="1">
                            </form>
                            <form
                                method="POST"
                                class="inline-block"
                            >
                                {{ csrf_field() }}
                            <button
                                name="back"
                                class="btn btn-primary"
                            >{{ uctrans('custom.close') }}</button>
                            </form>
                            <form
                                method="POST"
                                class="inline-block"
                                action="{{ url('admin/roles/delete/'. $role[0]->id) }}"
                            >
                                {{ csrf_field() }}
                                    <button
                                        class="btn del-btn btn-primary del-btn"
                                        type="submit"
                                        name="delete"
                                        data-confirm="{{ __('custom.remove_data') }}"
                                    >{{ uctrans('custom.remove') }}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
