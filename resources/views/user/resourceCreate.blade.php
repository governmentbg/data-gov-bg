@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <p class='req-fields'>{{ __('custom.all_fields_required') }}</p>
        <form id="addResource" method="POST" action="{{ route('resourceCreate', ['uri' => $uri]) }}" class="m-t-lg" enctype="multipart/form-data">
            {{ csrf_field() }}
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
                @elseif($field['view'] == 'translation_tags')
                    @include(
                        'components.form_groups.translation_tags',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach
            <div class="form-group row required">
                <label for="type" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.type', 1) }}:</label>
                <div class="col-sm-9">
                    <select
                        id="type"
                        class="input-border-r-12 form-control"
                        name="type"
                    >
                        <option value=""> {{ utrans('custom.type') }}</option>
                        @foreach ($types as $id =>$type)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('type') ? 'selected' : '' }}
                            >{{ $type }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('type'))
                        <span class="error">{{ $errors->first('type') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row required">
                <label for="file" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.file', 1) }}:</label>
                <div class="col-sm-9">
                    <input
                        id="file"
                        class="input-border-r-12 form-control"
                        name="file"
                        type="file"
                    >
                </div>
            </div>
            <div class="form-group row">
                <label for="schema_desc" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.schema_description') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        id="schema_desc"
                        class="input-border-r-12 form-control"
                        name="schema_description"
                    >{{ old('schema_desc') }}</textarea>
                    @if ($errors->has('schema_description'))
                        <span class="error">{{ $errors->first('schema_description') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="schema_url" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.schema_url') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="schema_url"
                        class="input-border-r-12 form-control"
                        name="schema_url"
                        type="text"
                        value="{{ old('schema_url') }}"
                    >
                    @if ($errors->has('schema_url'))
                        <span class="error">{{ $errors->first('schema_url') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <button type="submit" class="m-l-md btn btn-custom">{{ __('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
