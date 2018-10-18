@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'view', 'organisation' => $organisation])
    @if (!empty($organisation))
        <div class="row m-t-xs">
            <div class="col-xs-12 info-box p-l-lg">
                <div class="row">
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a class="followers">
                            <p>{{ $organisation->followers_count }}</p>
                            <hr>
                            <p>{{ __('custom.followers') }} </p>
                            <img src="{{ asset('/img/followers.svg') }}">
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="{{ url('/admin/organisations/datasets/'. $organisation->uri) }}" class="data-sets">
                            <p>{{ $organisation->datasets_count }}</p>
                            <hr>
                            <p>{{ __('custom.data_sets') }}</p>
                            <img src="{{ asset('/img/data-sets.svg') }}">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 m-t-md">
                <div class="row">
                    <div class="col-xs-12 page-content p-sm">
                        <div class="col-xs-12 list-orgs">
                            <div class="row">
                                <div class="col-xs-12 p-l-r-none">
                                    <div class="col-xs-12 org-logo">
                                        <img class="img-responsive" src="{{ $organisation->logo }}"/>
                                    </div>
                                    <div class="col-xs-12 m-b-lg">
                                        <h3>{{ $organisation->name }}</h3>
                                        @if (!empty($organisation->description))
                                            <p><b>{{ utrans('custom.description') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->description)) !!}</p>
                                        @endif
                                        @if (!empty($organisation->activity_info))
                                            <p><b>{{ utrans('custom.activity') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->activity_info)) !!}</p>
                                        @endif
                                        @if (!empty($organisation->contacts))
                                            <p><b>{{ utrans('custom.contacts') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->contacts)) !!}</p>
                                        @endif
                                        @if (
                                            isset($organisation->custom_fields[0])
                                            && !empty($organisation->custom_fields[0]->key)
                                        )
                                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                                            @foreach ($organisation->custom_fields as $field)
                                                <div class="row">
                                                    <div class="col-xs-6">{{ $field->key }}</div>
                                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    @if (\App\Role::isAdmin())
                                        <div class="col-xs-12 view-btns p-w-md">
                                            <div class="row">
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/admin/organisations/edit/'. $organisation->uri) }}"
                                                >
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-primary m-b-sm" type="submit" name="edit">{{ uctrans('custom.edit') }}</button>
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
                                                    action="{{ url('/admin/organisations/delete/'. $organisation->id) }}"
                                                >
                                                    {{ csrf_field() }}
                                                        <button
                                                            class="btn del-btn btn-primary m-b-sm"
                                                            type="submit"
                                                            name="delete"
                                                            class="del-btn"
                                                            data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                        >{{ uctrans('custom.remove') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
