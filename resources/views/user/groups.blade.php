@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    <div class="col-xs-12 m-t-md">
        <div class="row">
            <div class="col-xs-12 p-l-none">
                <form method="GET" action="{{ url('/user/searchGroups') }}">
                    <input
                        type="text"
                        class="rounded-input pull-right"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="search"
                    >
                </form>
                <span class="badge badge-pill m-t-lg new-data"><a href="{{ url('/user/registerGroup') }}">{{ __('custom.create_group') }}</a></span><br>
                <span class="badge badge-pill m-t-lg new-data"><a href="{{ url('/user/groupDatasets') }}">{{ __('custom.data_sets') }}</a></span>
            </div>
            <div class="col-xs-12 page-content p-sm">
                <div class="col-xs-12 user-group">
                    <div class="row">
                        @foreach($groups as $group)
                            <input type="hidden" value="{{ $group->id }}" name="group_id">
                            <div class="col-sm-4 p-md group-wrap">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView/'. $group->id) }}">
                                        <img class="img-responsive" src="{{ $group->logo }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView/'. $group->id) }}">
                                        <h3 class="group-name">{{ $group->name }}</h3>
                                    </a>
                                    <p class="text-justify group-desc">{{ $group->description }}</p>
                                    <p class="text-right show-more">
                                        <a
                                            href="{{ url('/user/groupView/'. $group->id) }}"
                                            class="view-profile"
                                        >{{ __('custom.see_more') }}</a>
                                    </p>
                                    <div class="control-btns text-center ch-del-btns">
                                        <div class="col-xs-6">
                                            <a href="{{ url('/user/editGroup/'. $group->id) }}">
                                                <button
                                                    type="submit"
                                                    name="edit"
                                                    class="btn btn-custom m-r-md"
                                                >{{ __('custom.edit') }}</button>
                                            </a>
                                        </div>
                                        <form method="POST" action="{{ url('/user/deleteGroup/'. $group->id) }}">
                                            {{ csrf_field() }}
                                            <div class="col-xs-6">
                                                <button
                                                    type="submit"
                                                    name="delete"
                                                    class="btn btn-custom m-r-md"
                                                >{{ __('custom.remove') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
