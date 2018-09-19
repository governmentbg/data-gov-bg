@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'pages'])
        <div class="row m-t-lg">
            @if (!is_null($page))
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-8 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-b-lg m-t-lg">
                            <h2>{{ __('custom.page_preview') }}</h2>
                        </div>
                        <div class="body">
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.title') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->title }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.section') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !is_null($section) ? $section : $page->section_id }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($page->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_head') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->head_title }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_keywords') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->meta_keywords }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.browser_desc') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->meta_description }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.valid') }}</label>
                                <div class="col-sm-3 col-xs-12">
                                    <div>{{ __('custom.from') .': '. $page->valid_from }}</div>
                                </div>
                                <div class="col-sm-3 col-xs-12">
                                    <div>{{ __('custom.to') .': '. $page->valid_to }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md hidden">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.short_txt') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->abstract }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.content') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div class="page-cont">{!! $page->body !!}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.forum_link') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->forum_link }}</div>
                                </div>
                            </div>

                            <div class="text-center m-b-lg terms-hr">
                                <hr>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->created_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->created_at }}</div>
                                </div>
                            </div>
                            @if ($page->created_at != $page->updated_at)
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $page->updated_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $page->updated_at }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-1"></div>
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        </div>
    </div>
@endsection
