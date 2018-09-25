@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'images'])

        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.image_add') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" enctype="multipart/form-data" class="form-horisontal">
                            {{ csrf_field() }}

                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="name" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.name') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        class="input-border-r-12 form-control"
                                    >
                                    @if (isset($errors) && $errors->has('name'))
                                        <span class="error">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="comment" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.comment') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <input
                                        type="text"
                                        name="comment"
                                        value="{{ old('comment') }}"
                                        class="input-border-r-12 form-control"
                                    >
                                    @if (isset($errors) && $errors->has('comment'))
                                        <span class="error">{{ $errors->first('comment') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.active') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
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
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="image" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.image') }}:</label>
                                <div class="col-sm-6">
                                    <input
                                        type="file"
                                        name="image"
                                        class="input-border-r-12 form-control doc-upload-input js-doc-input"
                                        value="{{ old('image') }}"
                                    >
                                    @if (isset($errors) && $errors->has('image'))
                                        <span class="error">{{ $errors->first('image') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">
                                    <button type="submit" class="btn btn-custom js-doc-btn">{{ __('custom.select_file') }}</button>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
                                    <button type="submit" name="create" value="1" class="m-l-md btn btn-custom">{{ utrans('custom.save') }}</button>
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
