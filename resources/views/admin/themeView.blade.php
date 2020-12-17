@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'topicsSubtopics'])
        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.theme_preview') }}</h2>
                    </div>
                    <div class="body">
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $theme->name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($theme->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.ordering')}}</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $theme->ordering }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="filename" class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.filename') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $theme->icon_file_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.file_preview') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div class="picks-box left">
                                    <a>
                                        <span class="svg">
                                            @if (!empty($theme->icon_data))
                                                {!! $theme->icon_data !!}
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="text-center m-b-lg terms-hr">
                            <hr>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $theme->created_by }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $theme->created_at }}</div>
                            </div>
                        </div>
                        @if (!empty($theme->updated_by))
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $theme->updated_by }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $theme->updated_at }}</div>
                                </div>
                            </div>
                        @endif
                        @if (\App\Role::isAdmin())
                            <div class="text-right">
                                <div class="row">
                                    <form
                                        method="POST"
                                        class="inline-block"
                                        action="{{ url('admin/themes/edit/'. $theme->id) }}"
                                    >
                                        {{ csrf_field() }}
                                        <button class="btn btn-primary m-b-sm" type="submit">{{ uctrans('custom.edit') }}</button>
                                        <input type="hidden" name="view" value="1">
                                    </form>
                                    <form
                                        method="POST"
                                        class="inline-block"
                                    >
                                        {{ csrf_field() }}
                                    <button
                                        name="back"
                                        class="btn btn-primary m-b-sm"
                                    >{{ uctrans('custom.close') }}</button>
                                    </form>
                                    <form
                                        method="POST"
                                        class="inline-block"
                                        action="{{ url('admin/themes/delete/'. $theme->id) }}"
                                    >
                                        {{ csrf_field() }}
                                            <button
                                                class="btn del-btn btn-primary del-btn m-b-sm"
                                                type="submit"
                                                name="delete"
                                                data-confirm="{{ __('custom.delete_theme_confirm') }}"
                                            >{{ uctrans('custom.remove') }}</button>
                                    </form>
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
