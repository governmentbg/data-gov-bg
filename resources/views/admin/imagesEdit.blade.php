@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'images'])

        <div class="row m-t-lg">
            @if (isset($model->id))
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-8 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-b-lg m-t-lg">
                            <h2>{{ __('custom.image_edit') }}</h2>
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
                                            value="{{ $model->name }}"
                                            class="input-border-r-12 form-control"
                                        >
                                        @if (isset($errors) && $errors->has('name'))
                                            <span class="error">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-3 col-xs-12 col-form-label">MIME type:</label>
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        <div>{{ $model->mime_type }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="comment" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.comment') }}:</label>
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        <input
                                            type="text"
                                            name="comment"
                                            value="{{ $model->comment }}"
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
                                                {{ !empty($model->active) ? 'checked' : '' }}
                                            >
                                            <span class="error">{{ $errors->first('active') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 m-t-sm p-l-none text-right m-b-lg">
                                        <a
                                            href="{{ url('admin/images/list') }}"
                                            class="badge badge-pill"
                                        >
                                            {{ uctrans('custom.close') }}
                                        </a>
                                        <button name="edit" class="badge badge-pill" type="submit">{{ uctrans('custom.edit') }}</button>
                                        <span class="badge badge-pill red">
                                            <a
                                                href="{{ url('/admin/images/delete/'. $model->id) }}"
                                                data-confirm="{{ __('custom.remove_data') }}"
                                            >{{ __('custom.delete') }}</a>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center m-b-lg terms-hr">
                                    <hr>
                                </div>
                                <div class="form-group row m-b-lg">
                                    <label class="col-sm-6 col-xs-12 col-form-label m-t-lg">{{ utrans('custom.size') }}:</label>
                                    <div class="col-sm-6 col-xs-12 m-t-lg">
                                        <div>{{ $model->width .' x '. $model->height }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.file_size') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->size }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">URI Image:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->item }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">URI Thumbnail:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->thumb }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->created_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->created_at }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->created_at != $model->updated_at ? $model->updated_by : '' }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{$model->created_at != $model->updated_at ? $model->updated_at : '' }}</div>
                                    </div>
                                </div>
                            </form>
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
