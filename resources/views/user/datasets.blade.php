@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="row">
    @if ($buttons['add'])
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn"><a href="{{ url('/user/dataset/create') }}">{{ __('custom.add_new_dataset') }}</a></span>
        </div>
    @endif
    @if ($buttons['view'])
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
            <form method="GET" action="{{ url('/user/dataset/search') }}">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control"
                    placeholder="{{ __('custom.search') }}.."
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                >
            </form>
        </div>
    @endif
    </div>
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div class="articles m-t-lg">
                        @if (count($datasets))
                            @foreach ($datasets as $set)
                                @if ($buttons[$set->uri]['view'])
                                <div class="article m-b-lg col-xs-12 user-dataset">
                                    <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                                    <div class="col-sm-12 p-l-none">
                                        <a href="{{ route('datasetView', ['uri' => $set->uri]) }}">
                                            <h2 class="m-t-xs">{{ $set->name }}</h2>
                                        </a>
                                        @if ($set->status == 1)
                                            <span>({{ __('custom.draft') }})</span>
                                        @else
                                            <span>({{ __('custom.published') }})</span>
                                        @endif
                                        <div class="desc">
                                            {{ $set->descript }}
                                        </div>
                                        <div class="col-sm-12 p-l-none btns">
                                            <div class="pull-left row">
                                                <div class="col-xs-6">
                                                @if ($buttons[$set->uri]['edit'])
                                                    <span class="badge badge-pill m-r-md m-b-sm">
                                                        <a href="{{ url('/user/dataset/edit/'. $set->uri) }}">{{ uctrans('custom.edit') }}</a>
                                                    </span>
                                                @endif
                                                </div>
                                                <div class="col-xs-6">
                                                @if ($buttons[$set->uri]['delete'])
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
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="pull-right">
                                                <span><a href="{{ route('datasetView', ['uri' => $set->uri]) }}">{{ __('custom.see_more') }}</a></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-sm-12 m-t-xl text-center no-info">
                                {{ __('custom.no_info') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
