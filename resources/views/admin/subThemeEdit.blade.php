@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'topicsSubtopics'])

        <div class="row">
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
                                        value="{{ !empty($model->name) ? $model->name : '' }}"
                                        class="input-border-r-12 form-control"
                                    >
                                    @if (isset($errors) && $errors->has('name'))
                                        <span class="error">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center m-b-lg terms-hr">
                                <hr>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($model->created_by) ? $model->created_by : '' }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($model->created_at) ? $model->created_at : '' }}</div>
                                </div>
                            </div>
                            @if (!empty($model->updated_by))
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->updated_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->updated_at }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a
                                        href="{{ url('admin/categories/list/') }}"
                                        class="btn btn-primary"
                                    >
                                        {{ uctrans('custom.close') }}
                                    </a>
                                    <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ uctrans('custom.edit') }}</button>
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
