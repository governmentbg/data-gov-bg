@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => $activeMenu])
    <div class="row">
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn"><a href="{{ url('/user/organisations/dataset/create') }}">{{ __('custom.add_new_dataset') }}</a></span>
        </div>
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
            <form method="GET" action="{{ url('/user/organisations/datasets/search') }}">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control"
                    placeholder="{{ __('custom.search') }}"
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                >
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div class="articles m-t-lg">
                        @if (count($datasets))
                            @foreach ($datasets as $set)
                                <div class="article m-b-lg col-xs-12 user-dataset">
                                    <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                                    <div class="col-sm-12 p-l-none">
                                        <a href="{{ url('/user/organisations/dataset/view/'. $set->uri) }}">
                                            <h2 class="m-t-xs">{{ $set->name }}</h2>
                                        </a>
                                        <div class="desc">
                                            {{ $set->descript }}
                                        </div>
                                        <div class="col-sm-12 p-l-none btns">
                                            <div class="pull-left row">
                                                <div class="col-xs-6">
                                                    <span class="badge badge-pill m-r-md m-b-sm">
                                                        <a href="{{ url('/user/organisations/datasets/edit/'. $set->uri) }}">{{ __('custom.edit') }}</a>
                                                    </span>
                                                </div>
                                                <div class="col-xs-6">
                                                    <form method="POST">
                                                        {{ csrf_field() }}
                                                        <div class="col-xs-6 text-right">
                                                            <button
                                                                class="badge badge-pill m-b-sm"
                                                                type="submit"
                                                                name="delete"
                                                                onclick="return confirm('Изтриване на данните?');"
                                                            >{{ __('custom.remove') }}</button>
                                                        </div>
                                                        <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="pull-right">
                                                <span><a href="{{ url('/user/organisations/dataset/view/'. $set->uri) }}">{{ __('custom.see_more') }}</a></span>
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
