@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'languages'])

    <form method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <div class="frame-wrap">
            <div class="frame">
                <div class="row">
                    <h3>{{ __('custom.add_language') }}</h3>
                </div>
                <div class="form-group row">
                    <label for="lang" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.language') }}:</label>
                    <div class="col-lg-9">
                        <select
                            class="js-autocomplete"
                            name="lang"
                            id="lang"
                            data-placeholder="{{ __('custom.select') }}"
                        >
                            <option></option>
                            @foreach ($locales as $locale => $data)
                                <option
                                    value="{{ $locale }}"
                                >{{ $data['name'] }}</option>
                            @endforeach
                        </select>
                        <span class="error">{{ $errors->first('locale') }}</span>
                    </div>
                    <!-- <input type="text" class="input-border-r-12 col-sm-9" id="lang_name" name="name" value="{{ old('name') }}"> -->
                </div>
                <div class="form-group row">
                    <label for="lang_active" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="lang_active"
                                value="1"
                                {{ old('active') ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <button type="submit" name="save" class="btn btn-primary pull-right">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
