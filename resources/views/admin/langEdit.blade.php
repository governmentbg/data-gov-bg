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
                    <h3 class="col-lg-12">{{ __('custom.edit_language') }}</h3>
                </div>
                <div class="form-group row">
                    <label for="lang" class="col-lg-2 col-sm-3 col-xs-4 col-form-label">{{ utrans('custom.language') }}:</label>
                    <div class="col-lg-4 col-sm-6 col-xs-8">
                        <span>{{ $locale->name }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="lang_active" class="col-lg-2 col-sm-3 col-xs-4 col-form-label">{{ utrans('custom.active') }}:</label>
                    <div class="col-lg-2 col-sm-9 col-xs-8">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="lang_active"
                                value="1"
                                {{ $locale->active ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-12 text-right">
                        <a
                            href="{{ url('admin/languages') }}"
                            class="btn btn-primary"
                        >
                            {{ uctrans('custom.close') }}
                        </a>
                        <button type="submit" name="edit" class="btn btn-primary ">{{ uctrans('custom.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
