@extends('layouts.app')

@section('content')
<div class="container role">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'manageRoles'])

    <form method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <div class="frame-wrap">
            <div class="frame">
                <div class="row">
                    <h3>{{ __('custom.add_role') }}</h3>
                </div>
                <div class="form-group row">
                    <label for="role_name" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                    <input type="text" class="input-border-r-12 col-sm-9" id="role_name" name="name" value="{{ old('name') }}">
                    <span class="error">{{ $errors->first('name') }}</span>
                </div>
                <div class="form-group row">
                    <label for="role_active" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="role_active"
                                value="1"
                                {{ old('active') ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <button type="submit" name="save" class="btn btn-primary pull-right">{{ __('custom.save') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
