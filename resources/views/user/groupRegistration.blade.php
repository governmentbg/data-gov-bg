@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    <div class="col-xs-12 m-t-md">
        <div>
            <h2>{{ __('custom.group_registration') }}</h2>
            <p class='req-fields m-t-lg m-b-lg'>{{ __('custom.all_fields_required') }}</p>
        </div>
        <form method="POST" class="m-t-lg" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row {{ isset(session('result')->errors->logo) ? 'has-error' : '' }}">
                <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.image') }}:</label>
                <div class="col-sm-9">
                    <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                        <img class="preview js-preview hidden" src="#" alt="organisation logo" />
                    </div>
                    <div class="inline-block">
                        <span class="badge badge-pill"><label class="js-logo" for="logo">{{ uctrans('custom.select_image') }}</label></span>
                        <input class="hidden js-logo-input" type="file" name="logo">
                    </div>
                    <div class="error">{{ $errors->first('logo') }}</div>
                </div>
            </div>
            <div class="form-group row {{ isset(session('result')->errors->uri) ? 'has-error' : '' }}">
                <label for="uri" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.unique_identificator') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        name="uri"
                        value="{{ old('uri') }}"
                    >
                    @if (isset($errors) && $errors->has('uri'))
                        <span class="error">{{ $errors->first('uri') }}</span>
                    @endif
                </div>
            </div>
            @foreach($fields as $field)
                @if($field['view'] == 'translation')
                    @include(
                        'components.form_groups.translation_input',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif($field['view'] == 'translation_txt')
                    @include(
                        'components.form_groups.translation_textarea',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif($field['view'] == 'translation_custom')
                    @include(
                        'components.form_groups.translation_custom_fields',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach
            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <a
                        href="{{ url('user/groups') }}"
                        class="btn btn-primary"
                    >
                        {{ uctrans('custom.close') }}
                    </a>
                    <button type="submit" name="create" class="m-l-md btn btn-primary">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
