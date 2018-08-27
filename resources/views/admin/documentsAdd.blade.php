@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'documents'])

        <div class="row">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>Добавяне на документ</h2>
                    </div>
                    <div class="body">
                        <form method="POST" enctype="multipart/form-data" class="form-horisontal">
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
                                @endif
                            @endforeach
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="file" class="col-sm-3 col-xs-12 col-form-label">Файл</label>
                                <div class="col-sm-6">
                                    <input
                                        type="file"
                                        name="file"
                                        class="input-border-r-12 form-control doc-upload-input js-doc-input"
                                        value="{{ old('file') }}"
                                    >
                                    @if (isset($errors) && $errors->has('file'))
                                        <span class="error">{{ $errors->first('file') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">
                                    <button type="submit" class="btn btn-custom js-doc-btn">Избери файл</button>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button type="submit" name="create" value="1" class="m-l-md btn btn-custom">{{ __('custom.add') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
