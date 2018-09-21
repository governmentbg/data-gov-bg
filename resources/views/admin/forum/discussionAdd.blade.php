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
                                class="active"
                                href="{{ url('/admin/forum/discussions/list') }}"
                            >{{ __('custom.discussions') }}</a>
                        </li>
                        <li>
                            <a
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
                        <h2>{{ __('custom.add_discussion') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="form-horisontal">
                            {{ csrf_field() }}

                            <div class="form-group row m-b-lg m-t-md required">
                            <label for="title" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.title') }}</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <input
                                        type="text"
                                        name="title"
                                        value="{{ old('title') }}"
                                        class="input-border-r-12 form-control"
                                    >
                                    @if (isset($errors) && $errors->has('title'))
                                        <span class="error">{{ $errors->first('title') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                            <label for="color" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.color') }}</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 color-picker colorpicker-component" data-color="">
                                    <input
                                        type="text"
                                        name="color"
                                        value="{{ old('color') }}"
                                        class="input-border-r-12 form-control js-input-color"
                                        autocomplete="off"
                                    >
                                    @if (isset($errors) && $errors->has('color'))
                                        <span class="error">{{ $errors->first('color') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row required">
                            <label for="category" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.category') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="category"
                                        name="category"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select_category') }}"
                                    >
                                        <option></option>
                                        @foreach ($categories as $category)
                                            <option
                                                value="{{ $category->id }}"
                                                {{ $category->id == old('category') ? 'selected' : '' }}
                                            >{{ isset($category->name) ? $category->name : '' }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('category') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                            <label for="message" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.message') }}</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <textarea
                                        name="message"
                                        class="input-border-r-12 form-control"
                                    >{{ old('message') }}</textarea>
                                    @if (isset($errors) && $errors->has('message'))
                                        <span class="error">{{ $errors->first('message') }}</span>
                                    @endif
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
