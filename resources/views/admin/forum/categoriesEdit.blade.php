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
                        <h2>{{ __('custom.edit_category') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="form-horisontal">
                            {{ csrf_field() }}

                            <div class="form-group row m-b-lg m-t-md required">
                            <label for="name" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.name') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ !empty($category->name) ? $category->name : '' }}"
                                        class="input-border-r-12 form-control"
                                    >
                                    @if (isset($errors) && $errors->has('name'))
                                        <span class="error">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                            <label for="color" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.color') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 color-picker colorpicker-component" data-color="">
                                    <input
                                        type="text"
                                        name="color"
                                        value="{{ !empty($category->color) ? $category->color : '' }}"
                                        class="input-border-r-12 form-control js-input-color"
                                        autocomplete="off"
                                    >
                                    @if (isset($errors) && $errors->has('color'))
                                        <span class="error">{{ $errors->first('color') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="order" class="col-lg-3 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                                <div class="col-lg-2">
                                    <input
                                        id="order"
                                        name="order"
                                        type="number"
                                        min="1"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($category->order) ? $category->order : ''  }}"
                                    >
                                    <span class="error">{{ $errors->first('order') }}</span>
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
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button
                                        type="submit"
                                        name="edit"
                                        value="1"
                                        class="m-l-md btn btn-custom"
                                    >{{ uctrans('custom.edit') }}</button>
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
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
