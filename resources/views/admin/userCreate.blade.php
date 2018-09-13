@extends('layouts.app')

@section('content')
<div class="container user-create">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'users'])
    <div class="col-lg-12">
        <h3>{{ utrans('custom.new_user') }}</h3>
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <p class='req-fields m-t-lg m-b-lg'>{{ __('custom.all_fields_required') }}</p>
                    </div>
                    <form method="POST" class="m-t-lg p-sm">
                        {{ csrf_field() }}

                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="firstname"
                                    value="{{ old('firstname') }}"
                                    placeholder="Иван"
                                >
                                <span class="error">{{ $errors->first('firstname') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.family_name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="lastname"
                                    value="{{ old('lastname') }}"
                                    placeholder="Иванов"
                                >
                                <span class="error">{{ $errors->first('lastname') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.email') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="email"
                                    class="input-border-r-12 form-control"
                                    name="email"
                                    placeholder="ivanov@abv.bg"
                                    value="{{ !empty($invMail) ? $invMail : old('email') }}"
                                >
                                <span class="error">{{ $errors->first('email') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="username" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.username') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="username"
                                    value="{{ old('username') }}"
                                    placeholder="Иванов"
                                >
                                <span class="error">{{ $errors->first('username') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.password') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password"
                                >
                                <span class="error">{{ $errors->first('password') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password-confirm" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.confirm_password') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password_confirm"
                                >
                                <span class="error">{{ $errors->first('password_confirm') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.additional_info') }}:</label>
                            <div class="col-sm-9">
                                <textarea
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="add_info"
                                >{{ old('add_info') }}</textarea>
                                <span class="error">{{ $errors->first('add_info') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.organisations', 1) }}:</label>
                            <div class="col-sm-9">
                                <select
                                    id="organisation"
                                    class="js-select form-control"
                                    name="org_id"
                                    data-placeholder="{{ utrans('custom.select_org') }}"
                                >
                                    <option value=""></option>
                                    <option value="0"></option>
                                    @foreach ($organisations as $id =>$org)
                                        <option
                                            value="{{ $id }}"
                                            {{ $id == old('org_id') ? 'selected' : '' }}
                                        >{{ $org }}</option>
                                    @endforeach
                                </select>
                                <span class="error">{{ $errors->first('org_id') }}</span>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="role" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.roles') }}: </label>
                            <div class="col-sm-9">
                                <select
                                    multiple="multiple"
                                    class="js-select form-control"
                                    name="role_id[]"
                                    data-placeholder="{{ __('custom.select_role') }}"
                                    id="role"
                                >
                                    <option></option>
                                    @foreach ($roles as $role)
                                        <option
                                            value="{{ $role->id }}"
                                            {{ old('role_id') && in_array($role->id, old('role_id')) ? 'selected' : '' }}
                                        >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <span class="error">{{ $errors->first('role_id') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.newsletter_subscription') }}:</label>
                            <div class="col-sm-3 col-xs-6 p-r-none">
                                <select
                                    class="js-select form-control"
                                    name="user_settings[newsletter_digest]"
                                >
                                    @foreach ($digestFreq as $id => $freq)
                                        <option
                                            value="{{ $id }}"
                                            {{ old('user_settings')['newsletter_digest'] == $id ? 'selected' : null }}
                                        >{{ $freq }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="is_admin" class="col-lg-2 col-sm-3 col-xs-12 col-form-label">{{ __('custom.admin') }}:</label>
                            <div class="col-lg-2 col-md-9 col-sm-9 col-xs-12">
                                <div class="js-check">
                                    <input
                                        type="checkbox"
                                        name="is_admin"
                                        value="{{ old('is_admin') != null ? old('active') : '1' }}"
                                        {{ !empty(old('is_admin')) ? 'checked' : '' }}
                                    >
                                </div>
                            </div>
                            <label for="invite" class="col-lg-2 col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.approved') }}:</label>
                            <div class="col-lg-2 col-md-9 col-sm-9 col-xs-12">
                                <div class="js-check">
                                    <input
                                        type="checkbox"
                                        name="invite"
                                        value="{{ old('invite') != null ? old('invite') : '1' }}"
                                        {{ !empty(old('invite')) ? 'checked' : '' }}
                                    >
                                </div>
                            </div>
                            <label for="active" class="col-lg-2 col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                            <div class="col-lg-2 col-md-9 col-sm-9 col-xs-12">
                                <div class="js-check">
                                    <input
                                        type="checkbox"
                                        name="active"
                                        value="{{ old('active') != null ? old('active') : '1' }}"
                                        {{ !empty(old('active')) ? 'checked' : '' }}
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="reg-btns">
                                <button
                                    type="submit"
                                    class="m-l-md btn pull-right btn-primary m-b-sm"
                                >{{ uctrans('custom.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
