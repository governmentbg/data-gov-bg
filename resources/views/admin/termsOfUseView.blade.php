@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'termsConditions'])
        <div class="row">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.terms_preview') }}</h2>
                    </div>
                    <div class="body">
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.name') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $term->name }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.description') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{!! nl2br(e($term->descript)) !!}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="active" class="col-sm-6 col-xs-12 col-form-label">{{ utrans('custom.active') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($term->active) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label for="default" class="col-sm-6 col-xs-12 col-form-label">{{ uctrans('custom.by_default') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ !empty($term->is_default) ? utrans('custom.yes') : utrans('custom.no') }}</div>
                            </div>
                        </div>
                        <div class="form-group row m-b-lg m-t-md">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{__('custom.ordering')}}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $term->ordering }}</div>
                            </div>
                        </div>
                        @if (\App\Role::isAdmin())
                            <div class="text-right">
                                <div class="row">
                                    <form
                                        method="POST"
                                        class="inline-block"
                                        action="{{ url('admin/terms-of-use/edit/'. $term->id) }}"
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
                                        action="{{ url('admin/terms-of-use/delete/'. $term->id) }}"
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
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
