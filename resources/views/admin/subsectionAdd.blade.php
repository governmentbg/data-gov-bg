@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'sections'])

        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.subsection_add') }}</h2>
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
                                @endif
                            @endforeach
                            <div class="form-group row required">
                                <label for="parent_id" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.section') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="parent_id"
                                        name="parent_id"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select_section') }}"
                                    >
                                        <option></option>
                                        @foreach ($sections as $id => $section)
                                            <option
                                                value="{{ $section->id }}"
                                                @if ($section->id == old('parent_id'))
                                                    {{ 'selected' }}
                                                @elseif ($section->id == $sectionId)
                                                    {{ 'selected' }}
                                                @endif
                                            >{{ isset($section->name) ? $section->name : '' }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('parent_id') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-lg-3 col-sm-3 col-xs-4 col-form-label">{{ uctrans('custom.activef') }}:</label>
                                <div class="col-lg-2 col-sm-9 col-xs-8">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="active"
                                            value="1"
                                            {{ !empty(old('active')) ? 'checked' : '' }}
                                        >
                                        <span class="error">{{ $errors->first('active') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md hidden">
                                <label for="read_only" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.read_only') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="read_only"
                                            value="1"
                                            {{ !empty(old('read_only')) ? 'checked' : '' }}
                                        >
                                        <span class="error">{{ $errors->first('read_only') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="forum_link" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.forum_link') }}:</label>
                                <div class="col-sm-9">
                                    <input
                                        name="forum_link"
                                        class="input-border-r-12 form-control"
                                        value="{{ old('forum_link') }}"
                                    >
                                    @if (isset($errors) && $errors->has('forum_link'))
                                        <span class="error">{{ $errors->first('forum_link') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="help_section" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.help_section') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="help_section"
                                        name="help_section"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select') }}"
                                    >
                                        <option></option>
                                        <option value="0"></option>
                                        @foreach ($helpSections as $section)
                                            <option
                                                value="{{ $section->name }}"
                                                {{ $section->name == old('help_section') ? 'selected' : '' }}
                                            >{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('help_section') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="order" class="col-lg-3 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                                <div class="col-lg-2">
                                    <input
                                        id="order"
                                        name="ordering"
                                        type="number"
                                        min="1"
                                        class="input-border-r-12 form-control"
                                        value="{{ old('ordering') }}"
                                    >
                                    <span class="error">{{ $errors->first('ordering') }}</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
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
