@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'forum'])
        <div class="row">
            <div class="col-xs-10 m-t-lg text-right section">
                <div class="filter-content section-nav-bar">
                    <ul class="nav filter-type right-border">
                        <li>
                            <a
                                href="{{ url('/admin/forum/discussions/list') }}"
                            >{{ __('custom.discussions') }}</a>
                        </li>
                        <li>
                            <a
                                class="active"
                                href="{{ url('/admin/forum/categories/list') }}"
                            >{{ __('custom.categories') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.subcategory_preview') }}</h2>
                    </div>
                    <div class="body">
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $category->name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.color')}}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $category->color }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.ordering')}}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $category->order }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.main_cat')}}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $mainCatName }}</div>
                            </div>
                        </div>
                        <div class="text-center m-b-lg terms-hr">
                            <hr>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $category->created_at }}</div>
                            </div>
                        </div>
                        @if (!empty($category->updated_at))
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $category->updated_at }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="text-right">
                            <div class="row">
                                <form
                                    method="POST"
                                    class="inline-block"
                                    action="{{ url('admin/forum/subcategories/edit/'. $category->id) }}"
                                >
                                    {{ csrf_field() }}
                                    <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                    <input type="hidden" name="view" value="1">
                                </form>
                                <form
                                    method="POST"
                                    class="inline-block"
                                >
                                    {{ csrf_field() }}
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
                                </form>
                                <form
                                    method="POST"
                                    class="inline-block"
                                    action="{{ url('admin/forum/subcategories/delete/'. $category->id) }}"
                                >
                                    {{ csrf_field() }}
                                    <button
                                        class="btn del-btn btn-primary del-btn"
                                        type="submit"
                                        name="delete"
                                        data-confirm="{{ __('custom.remove_data') }}"
                                    >{{ uctrans('custom.remove') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
