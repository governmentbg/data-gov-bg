@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'images'])
        <div class="row m-t-lg">
            @if (!is_null($image))
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-8 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-b-lg m-t-lg">
                            <h2>{{ __('custom.image_preview') }}</h2>
                        </div>
                        <div class="body">
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->name }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.type') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->mime_type }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($image->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.width') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->width }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.height') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->height }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.file_size') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->size }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.comment') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->comment }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.file_preview') }}</label>
                                <div class="col-sm-6 col-xs-12 fileinput-new thumbnai form-control input-border-r-12 m-l-sm">
                                    <img
                                        class="preview js-preview {{ empty($image->img_data) ? 'hidden' : '' }}"
                                        src="{{ !empty($image->img_data) ? $image->img_data : '' }}"
                                        alt="theme preview"
                                    />
                                </div>
                            </div>
                            <div class="text-center m-b-lg terms-hr">
                                <hr>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">URI Image</label>
                                <div class="col-sm-6 col-xs-12">
                                <div>{{ $image->item }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">URI Thumbnail</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->thumb }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->created_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $image->created_at }}</div>
                                </div>
                            </div>
                            @if ($image->created_at != $image->updated_at)
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $image->updated_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $image->updated_at }}</div>
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
