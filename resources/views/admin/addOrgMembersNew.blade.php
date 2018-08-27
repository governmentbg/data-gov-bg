@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'members', 'organisation' => $organisation])
    <div class="row">
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <h2>{{ __('custom.user_registration') }}</h2>
                        <p class='req-fields m-t-lg m-b-lg'>{{ __('custom.all_fields_required') }}</p>
                    </div>
                    <form method="POST" class="m-t-lg p-sm">
                        {{ csrf_field() }}

                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.name') }}:</label>
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
                                    value="{{ old('password') }}"
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
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.roles') }}:</label>
                            <div class="col-sm-3 col-xs-6 p-r-none">
                                <select
                                    class="js-select form-control open-select"
                                    name="role_id"
                                    data-placeholder="{{ __('custom.select_role') }}"
                                    size="5"
                                >
                                    <option></option>
                                    @foreach ($roles as $role)
                                        <option
                                            {{ $role->id == old('role_id') ? 'selected' : '' }}
                                            value="{{ $role->id }}"
                                        >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-xs-6 text-right p-l-none reg-btns">
                                <button
                                    type="submit"
                                    name="save"
                                    class="m-l-md btn btn-primary m-b-sm"
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
