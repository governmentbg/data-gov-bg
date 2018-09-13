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
                        <h2>{{ __('custom.subsection_preview') }}</h2>
                    </div>
                    <div class="body">
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $section->name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.section') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>
                                    {{
                                        isset($sections[$section->parent_id])
                                            ? $sections[$section->parent_id]
                                            : $section->parent_id
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($section->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="read_only" class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.read_only') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($section->read_only) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="forum_link" class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.forum_link') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $section->forum_link }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.ordering')}}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $section->ordering }}</div>
                            </div>
                        </div>
                        <div class="text-center m-b-lg terms-hr">
                            <hr>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $section->created_by }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $section->created_at }}</div>
                            </div>
                        </div>
                        @if (!empty($section->updated_by))
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $section->updated_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $section->updated_at }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
