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
                        <div class="text-center m-b-md terms-hr">
                            <hr>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $term->created_at }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                            <div class="col-sm-6 col-xs-12">
                                <div>{{ $term->created_by }}</div>
                            </div>
                        </div>
                        @if ($term->created_at != $term->updated_at)
                            <div class="form-group row">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $term->updated_at }}</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ $term->updated_by }}</div>
                                </div>
                            </div>
                        @endif
                        @if (\App\Role::isAdmin())
                            <div class="text-right">
                                <div class="row">
                                    <form
                                        method="POST"
                                        class="inline-block"
                                        action="{{ url('admin/terms-of-use/edit/'. $term->id) }}"
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
                                        action="{{ url('admin/terms-of-use/delete/'. $term->id) }}"
                                    >
                                        {{ csrf_field() }}
                                            <button
                                                class="btn btn-primary del-btn m-b-sm"
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
