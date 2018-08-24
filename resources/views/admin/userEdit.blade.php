@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'users'])
    <div class="col-xs-12 m-t-lg">
        <form class="m-t-lg p-sm" method="post">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="fname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.name') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        name="firstname"
                        id="fname"
                        value="{{ $user->firstname }}"
                    >
                </div>
            </div>
            <div class="form-group row">
                <label for="lname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.family_name') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        name="lastname"
                        id="lname"
                        value="{{ $user->lastname }}"
                    >
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.email') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="email"
                        class="input-border-r-12 form-control"
                        name="email"
                        id="email"
                        value="{{ $user->email }}"
                    >
                </div>
            </div>
            <div class="form-group row">
                <label for="username" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.username') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        id="username"
                        name="username"
                        value="{{ $user->username }}"
                    >
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.password') }}:</label>
                <div class="col-sm-9 text-right">
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#change-password"
                    >{{ utrans('custom.change') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.additional_info') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        type="text"
                        class="input-border-r-12 form-control"
                        name="add_info"
                        id="description"
                    >{{ $user->add_info }}</textarea>
                </div>
            </div>
            @if (!empty($orgRoles[0]))
                <div class="form-group row">
                    <label for="def_role" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.roles') }}: </label>
                    <div class="col-sm-4">
                        <select
                            multiple="multiple"
                            class="js-select form-control"
                            name="role_id[0][]"
                            data-placeholder="{{ __('custom.select_role') }}"
                            id="def_role"
                        >
                            <option></option>
                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    {{ in_array($role->id, $orgRoles[0]) ? 'selected' : '' }}
                                >{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <span class="error">{{ $errors->first('role_id') }}</span>
                    </div>
                </div>
            @endif
            @if (!empty($orgRoles))
                @foreach ($orgRoles as $org => $orgRole)
                    @if ($org != 0)
                        <div class="form-group row">
                                <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.organisation') }}: </label>
                                <span class="col-sm-3 col-xs-12">{{ $organisations[$org] }} </span>
                                <label for="role[{{ $org }}]" class="col-sm-1 col-xs-12 col-form-label">{{ __('custom.roles') }}: </label>
                                <div class="col-sm-3">
                                    <select
                                        multiple="multiple"
                                        class="js-select form-control"
                                        name="{{ 'role_id['. $org .'][]' }}"
                                        data-placeholder="{{ __('custom.select_role') }}"
                                        id="role[{{ $org }}]"
                                    >
                                        <option></option>
                                        @foreach ($roles as $role)
                                            <option
                                                value="{{ $role->id }}"
                                                {{ in_array($role->id, $orgRole) ? 'selected' : '' }}
                                            >{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('role_id') }}</span>
                                </div>
                                <div class="col-sm-2 col-xs-12">
                                    <button
                                        type="submit"
                                        name="remove_role"
                                        class="btn btn-primary pull-right del-btn"
                                    >{{ utrans('custom.remove') }}</button>
                                    <input type="hidden" name="org_id" value="{{ $org }}">
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            <div class="form-group row">
                <label for="apiKey" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.api_key') }}:</label>
                <div class="col-sm-9">
                    <input type="text" class="input-border-r-12 form-control" id="apiKey" value="{{ $user->api_key }}" disabled>
                    <button
                        type="button"
                        class="col-xs-12 btn btn-primary m-t-sm"
                        data-toggle="modal"
                        data-target="#generateAPIkey"
                    >{{ __('custom.api_key_new') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="newsletter" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.newsletter_subscription') }}:</label>
                <div class="col-sm-4 col-xs-8">
                    <select class="input-border-r-12 form-control js-select" name="newsletter" id="newsletter">
                        @foreach ($digestFreq as $id => $freq)
                            <option
                                value="{{ $id }}"
                                {{
                                    $userSett->newsletter_digest == $id
                                        ? 'selected'
                                        : ''
                                }}
                            >{{ $freq }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="locales" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.language') }}:</label>
                <div class="col-sm-4 col-xs-8">
                    <select class="input-border-r-12 form-control js-select" name="locale" id="locales">
                        @foreach ($localeList as $locale)
                            <option
                                value="{{ $locale->locale }}"
                                {{
                                    $userSett->locale == $locale->locale
                                        ? 'selected'
                                        : ''
                                }}
                            >{{ $locale->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-5 col-xs-4 text-right">
                    <button type="submit" name="save" class="btn btn-primary">{{ __('custom.save') }}</button>
                </div>
            </div>
        </form>
        <div class="form-group row p-h-lg">
            <div class="col-xs-12">
                <button
                    type="button"
                    class="col-xs-12 btn btn-primary del-btn"
                    data-toggle="modal"
                    data-target="#delete-confirm"
                    >{{ utrans('custom.delete_profile') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="change-password" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h2>{{ __('custom.change_password') }}</h2>
                </div>
                <div class="modal-body">
                    <form class="m-t-lg" method="post">
                        {{ csrf_field() }}
                        <div class="form-group row required">
                            <label for="oldPass" class="col-sm-4 col-xs-12 col-form-label">{{ __('custom.password') }}:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="old_password"
                                    id="oldPass"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-4 col-xs-12 col-form-label">{{ __('custom.new_password') }}:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password"
                                    id="password"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="confPass" class="col-sm-4 col-xs-12 col-form-label">{{ __('custom.confirm_password') }}:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password_confirm"
                                    id="confPass"
                                >
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" name="change_pass" class="m-l-md btn btn-custom">{{ utrans('custom.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="delete-confirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="blue-highlight">
                <div class="modal-body">
                    <a type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></a>
                    <span>
                    {{ __('custom.delete_account_message') }}
                        <p class="small-text">
                        {{ __('custom.delete_account_continue') }}
                        </p>
                    </span>
                    <button
                        type="submit"
                        class="m-l-md btn btn-custom pull-right conf"
                        data-dismiss="modal"
                    >{{ utrans('custom.cancel') }}</button>
                    <button
                        id="confirm"
                        type="submit"
                        name="continue"
                        class="m-l-md btn btn-custom pull-right conf del-btn"
                        data-toggle="modal"
                        data-target="#delete"
                    >{{ utrans('custom.continue') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="blue-highlight">
                <div class="modal-body text-center">
                    <form method="post">
                        {{ csrf_field() }}
                        <a type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                        </a>
                        <div class="usr-delete row">
                            <span>
                                {{ __('custom.delete_profile') }}
                            </span>
                                <button
                                    type="submit"
                                    class="m-l-md btn btn-custom pull-right"
                                    data-dismiss="modal"
                                >{{ utrans('custom.cancel') }}</button>
                                <button
                                    type="submit"
                                    name="delete"
                                    class="m-l-md btn btn-custom pull-right del-btn"
                                >{{ utrans('custom.delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="generateAPIkey" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <form method="post">
                    {{ csrf_field() }}
                    <div class="p-w-md">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('custom.close') }}</span></button>
                        <h2>{{ __('custom.api_key_new') }}</h2>
                    </div>
                    <div class="modal-body">
                        <p class="p-sm text-center">
                        {{ __('custom.api_key_new') }}
                            <button type="submit" name="generate_key" class="m-l-md btn btn-custom">{{ utrans('custom.generate') }}</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
