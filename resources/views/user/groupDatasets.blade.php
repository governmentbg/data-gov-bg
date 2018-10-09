@extends('layouts.app')
@php $root = empty(Auth::user()->is_admin) ? 'user' : 'admin'; @endphp
@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.'. $root .'-nav-bar', ['view' => 'group'])
    @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $group])
    @include('partials.pagination')
    <div class="row">
        <div class="col-sm-3 col-xs-12 text-left sidenav">
        @if ($buttons['add'])
            <span class="badge badge-pill m-t-lg new-data user-add-btn">
                <a href="{{ url('/'. $root .'/groups/dataset/create/'. $uri) }}">{{ __('custom.add_new_dataset') }}</a>
            </span>
        @endif
            @include('partials.group-info', ['group' => $group])
        </div>
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control js-ga-event"
                    placeholder="{{ __('custom.search') }}.."
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                    data-ga-action="search"
                    data-ga-label="data search"
                    data-ga-category="data"
                >
            </form>
        </div>
        <div class="col-lg-9 col-xs-12 m-t-md">
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ route($root .'GroupDatasetView', ['uri' => $set->uri, 'grpUri' => $group->uri]) }}">
                                    <h2 class="m-t-xs">{{ $set->name }}</h2>
                                </a>
                                <div class="desc">
                                    {!! nl2br(truncate(e($set->descript), 150)) !!}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                        @if ($buttons[$set->uri]['edit'])
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a href="{{ url('/'. $root .'/group/'. $group->uri .'/dataset/edit/'. $set->uri) }}">
                                                    {{ uctrans('custom.edit') }}
                                                </a>
                                            </span>
                                        @endif
                                        </div>
                                        @if ($buttons[$set->uri]['delete'])
                                            <div class="col-xs-6">
                                                <form method="POST">
                                                    {{ csrf_field() }}
                                                    <div class="col-xs-6 text-right">
                                                        <button
                                                            class="badge badge-pill m-b-sm del-btn"
                                                            type="submit"
                                                            name="delete"
                                                            data-confirm="{{ __('custom.remove_data') }}"
                                                        >{{ uctrans('custom.remove') }}</button>
                                                    </div>
                                                    <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="pull-right">
                                        <span>
                                            <a href="{{ route($root .'GroupDatasetView', ['uri' => $set->uri, 'grpUri' => $group->uri]) }}">
                                                {{ __('custom.see_more') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('partials.pagination')
</div>
@endsection
