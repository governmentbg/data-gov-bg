@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    @include('partials.group-nav-bar', ['view' => 'view', 'group' => $group])
    @if (!empty($group))
        <div class="row m-t-xs p-l-lg">
            <div class="col-xs-12 info-box">
                <div class="row">
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="" class="followers">
                            <p>{{ $group->followers_count }}</p>
                            <hr>
                            <p>{{ __('custom.followers') }} </p>
                            <img src="{{ asset('/img/followers.svg') }}">
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="" class="data-sets">
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
            <div class="col-xs-12 m-t-md">
                <div class="row">
                    <div class="col-xs-12 page-content p-sm">
                        <div class="col-xs-12 list-orgs">
                            <div class="row">
                                <div class="col-xs-12 p-md">
                                    <div class="col-xs-12 org-logo">
                                        <img class="img-responsive" src="{{ $group->logo }}"/>
                                    </div>
                                    <div class="col-xs-12 m-b-lg">
                                        <h3>{{ $group->name }}</h3>
                                        @if (!empty($group->description))
                                            <p><b>{{ utrans('custom.description') }}:</b></p>
                                            <p>{{ $group->description }}</p>
                                        @endif
                                        @if (!empty($group->activity_info))
                                            <p><b>{{ utrans('custom.activity') }}:</b></p>
                                            <p>{{ $group->activity_info }}</p>
                                        @endif
                                        @if (!empty($group->contacts))
                                            <p><b>{{ utrans('custom.contacts') }}:</b></p>
                                            <p>{{ $group->contacts }}</p>
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
                                    @if ($buttons[$group->uri]['edit'])
                                        <div class="col-xs-12 view-btns">
                                            <div class="row">
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/user/groups/edit/'. $group->uri) }}"
                                                >
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                                    <input type="hidden" name="view" value="1">
                                                </form>
                                    @endif
                                    @if ($buttons[$group->uri]['delete'])
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/user/groups/delete/'. $id) }}"
                                                >
                                                    {{ csrf_field() }}
                                                        <button
                                                            class="btn del-btn btn-primary"
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
