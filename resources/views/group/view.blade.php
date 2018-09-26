@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 m-l-sm">
        <div class="row">
            <div class="col-sm-9 col-xs-12 p-sm col-sm-offset-3">
                <div class="filter-content">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/groups') }}">{{ untrans('custom.groups', 2) }}</a></li>
                                    <li><a class="active" href="{{ url('/groups/view/'. $group->uri) }}">{{ untrans('custom.groups', 1) }}</a></li>
                                    <li><a href="{{ route('data', ['group' => [$group->id]]) }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/groups/chronology/'. $group->uri) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row m-t-lg">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-12 col-sm-6 col-xs-12 info-box">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="followers">
                                    <p>{{ $group->followers_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.followers') }} </p>
                                    <img src="{{ asset('/img/followers.svg') }}">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="data-sets">
                                    <p>{{ $group->datasets_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.data_sets') }}</p>
                                    <img src="{{ asset('/img/data-sets.svg') }}">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="m-t-lg">
                    <div class="m-b-md">
                        <div>
                            <div class="col-xs-12 org-logo m-t-lg">
                                <img class="img-responsive" src="{{ $group->logo }}"/>
                            </div>
                            <div class="col-xs-12 p-l-none">
                                <div>
                                    <h3>{{ $group->name }} </h3><br/>
                                    <p>{!! nl2br(e($group->description)) !!}</p><br/>
                                </div>
                            </div>
                            @if (!empty($group->activity_info))
                                <div class="col-xs-12 p-l-none">
                                    <span><b>{{ __('custom.activity') }}:</b></span><br/><br/>
                                    <p>{!! nl2br(e($group->activity_info)) !!}</p><br/>
                                </div>
                            @endif
                            <div class="col-xs-12 p-l-none articles">
                                @if (!empty($group->contacts))
                                    <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                        <span><b>{{ __('custom.contact_person') }}:</b></span><br/><br/>
                                        <p>{!! nl2br(e($group->contacts)) !!}</p><br/>
                                    </div>
                                @endif
                                @if (isset($group->custom_fields[0]) && !empty($group->custom_fields[0]->key))
                                    <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                        <p><b>{{ __('custom.additional_fields') }}:</b></p>
                                        @foreach ($group->custom_fields as $field)
                                            <div class="row">
                                                <div class="col-xs-6">{{ $field->key }}</div>
                                                <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                            </div>
                                        @endforeach
                                        <br/>
                                    </div>
                                @endif
                                <div class="col-sm-4 col-xs-12 pull-right text-right">
                                    <form method="post">
                                        {{ csrf_field() }}
                                        @if (isset($buttons['follow']) && $buttons['follow'])
                                            <div class="row">
                                                <button
                                                    class="btn btn-primary pull-right m-r-xs"
                                                    type="submit"
                                                    name="follow"
                                                    value="{{ $group->id }}"
                                                >{{ utrans('custom.follow') }}</button>
                                            </div>
                                        @elseif (isset($buttons['unfollow']) && $buttons['unfollow'])
                                            <div class="row">
                                                <button
                                                    class="btn btn-primary pull-right m-r-xs"
                                                    type="submit"
                                                    name="unfollow"
                                                    value="{{ $group->id }}"
                                                >{{ uctrans('custom.stop_follow') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                                <div class="col-xs-12 view-btns p-h-md">
                                    <div class="row">
                                        @if (isset($buttons['edit']) && $buttons['edit'])
                                            <form
                                                method="POST"
                                                class="inline-block"
                                                action="{{ url('/'. $buttons['rootUrl'] .'/groups/edit/'. $group->uri) }}"
                                            >
                                                {{ csrf_field() }}
                                                <button class="btn btn-primary" type="submit">{{ uctrans('custom.edit') }}</button>
                                                <input type="hidden" name="view" value="1">
                                            </form>
                                        @endif
                                        @if (isset($buttons['delete']) && $buttons['delete'])
                                            <form
                                                method="POST"
                                                class="inline-block"
                                                action="{{ route('groupDelete', array_except(app('request')->input(), ['page'])) }}"
                                            >
                                                {{ csrf_field() }}
                                                <button
                                                    class="btn del-btn btn-primary del-btn"
                                                    type="submit"
                                                    name="delete"
                                                    data-confirm="{{ __('custom.delete_group_confirm') }}"
                                                >{{ uctrans('custom.remove') }}</button>
                                                <input class="user-org-del" type="hidden" name="group_uri" value="{{ $group->uri }}">
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
