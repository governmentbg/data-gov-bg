@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <span class="my-profile m-l-sm">{{ uctrans('custom.datasets_list') }}</span>
    </div>
    @include('partials.pagination')
    <div class="row">
    @if ($buttons['add'])
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn">
                <a href="{{ url('/user/dataset/create') }}">{{ __('custom.add_new_dataset') }}</a>
            </span>
        </div>
    @endif
    @if ($buttons['view'])
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field p-l-lg">
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
    @endif
    </div>
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        @if ($buttons[$set->uri]['view'])
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ route('userDatasetView', ['uri' => $set->uri]) }}">
                                    <h2 class="m-t-xs {{$set->reported ? 'error' : '' }}">{{ $set->name }}</h2>
                                </a>
                                @if ($set->status == 1)
                                    <span>({{ __('custom.draft') }})</span>
                                @else
                                    <span>({{ __('custom.published') }})</span>
                                @endif
                                <div class="desc">
                                    {!! nl2br(e($set->descript)) !!}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                        @if ($buttons[$set->uri]['edit'])
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a href="{{ url('/user/dataset/edit/'. $set->uri) }}">
                                                    {{ uctrans('custom.edit') }}
                                                </a>
                                            </span>
                                        @endif
                                        </div>
                                        <div class="col-xs-6">
                                        @if ($buttons[$set->uri]['delete'])
                                            <form method="POST">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        class="badge badge-pill m-b-sm del-btn"
                                                        type="submit"
                                                        name="delete"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="pull-right">
                                        <span>
                                            <a href="{{ route('userDatasetView', ['uri' => $set->uri]) }}">
                                                {{ __('custom.see_more') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
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
