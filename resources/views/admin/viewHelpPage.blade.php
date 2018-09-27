@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'help'])
        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame section-edit">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.help_page_preview') }}</h2>
                    </div>
                    <div class="body">
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.section') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->section_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.title') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->title }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.description') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->body }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($page->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.ordering')}}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ App\Category::getOrdering()[$page->ordering] }}</div>
                            </div>
                        </div>
                        <div class="text-center m-b-lg terms-hr">
                            <hr>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->created_by }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $page->created_at }}</div>
                            </div>
                        </div>
                        @if (!empty($page->updated_by))
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->updated_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $page->updated_at }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <a
                                    href="{{ url('admin/help/pages/list') }}"
                                    class="m-l-md btn btn-custom"
                                >{{ __('custom.close') }}</a>
                                <a
                                    href="{{ url('/admin/help/page/edit/'. $page->id) }}"
                                    class="m-l-md btn btn-custom"
                                >{{ __('custom.edit') }}</a>
                                <a
                                    href="{{ url('/admin/help/page/delete/'. $page->id) }}"
                                    class="m-l-md btn btn-custom del-btn"
                                >{{ __('custom.delete') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
