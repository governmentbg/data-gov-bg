@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'group'])
    @include('partials.group-nav-bar', ['view' => 'view', 'group' => $group])
    @if (!empty($group))
        <div class="row m-t-xs">
            <div class="col-xs-12 info-box p-l-lg">
                <div class="row">
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a class="followers">
                            <p>{{ $group->followers_count }}</p>
                            <hr>
                            <p>{{ __('custom.followers') }} </p>
                            <img src="{{ asset('/img/followers.svg') }}">
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="{{ url('/admin/groups/datasets/'. $group->uri) }}" class="data-sets">
                            <p>{{ $group->datasets_count }}</p>
                            <hr>
                            <p>{{ __('custom.data_sets') }}</p>
                            <img src="{{ asset('/img/data-sets.svg') }}">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 m-t-sm">
                <div class="row">
                    <div class="col-xs-12 page-content p-sm">
                        <div class="col-xs-12 list-orgs p-l-r-none">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="col-xs-12 org-logo">
                                        <img class="img-responsive" src="{{ $group->logo }}"/>
                                    </div>
                                    <div class="col-xs-12 m-b-lg">
                                        <h3>{{ $group->name }}</h3>
                                        @if (!empty($group->description))
                                            <p><b>{{ utrans('custom.description') }}:</b></p>
                                            <p>{!! nl2br(e($group->description)) !!}</p>
                                        @endif
                                        @if (!empty($group->activity_info))
                                            <p><b>{{ utrans('custom.activity') }}:</b></p>
                                            <p>{!! nl2br(e($group->activity_info)) !!}</p>
                                        @endif
                                        @if (!empty($group->contacts))
                                            <p><b>{{ utrans('custom.contacts') }}:</b></p>
                                            <p>{!! nl2br(e($group->contacts)) !!}</p>
                                        @endif
                                        @if (
                                            isset($group->custom_fields[0])
                                            && !empty($group->custom_fields[0]->key)
                                        )
                                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                                            @foreach ($group->custom_fields as $field)
                                                <div class="row">
                                                    <div class="col-xs-6">{{ $field->key }}</div>
                                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    @if (\App\Role::isAdmin())
                                        <div class="col-xs-12 view-btns">
                                            <div class="row">
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/admin/groups/edit/'. $group->uri) }}"
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
                                                    action="{{ url('/admin/groups/delete/'. $id) }}"
                                                >
                                                    {{ csrf_field() }}
                                                        <button
                                                            class="btn del-btn btn-primary del-btn m-b-sm"
                                                            type="submit"
                                                            name="delete"
                                                            data-confirm="{{ __('custom.delete_group_confirm') }}"
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
