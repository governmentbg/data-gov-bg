@extends('layouts.app')

@section('content')
<div class="container home-stats">
    <div class="flash-message">
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if(Session::has('alert-' . $msg))
                <p class="alert alert-{{ $msg }}">
                    {{ Session::get('alert-'. $msg) }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
    </div>
    <div class="col-md-8 basic-stats">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12 pr-0 pl-0">

                    <a href="{{ url('/users/list') }}" class="reg-orgs">
                    <p>{{ $organisations }}</p>
                    <hr>
                    <p>{{ ucfirst(trans_choice('custom.organisations', 2)) }}</p>
                    <img src="{{ asset('/img/reg-users.svg') }}">
                </a>
                </div>
                <div class="col-md-12 pr-0 pl-0">

                    <a href="{{ url('data') }}" class="data-sets">
                        <p>{{ $datasets }}</p>
                        <hr>
                        <p>{{ __('custom.data_sets') }}</p>
                        <img src="{{ asset('/img/data-sets.svg') }}">
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="col-md-12 pr-0 pl-0 most-active">
                    <a href="{{ url('/news/view/' . $latestNews->id ) }}" class="lat-news">
                        <img src="{{ asset('/img/latest-news-'.Config::get('app.locale').'.png') }}" width="100">
                        <p class="ml-0 mr-0 p-left">{{$latestNews->title}} </p>
                        <hr class="news-count">
                        <p class="ml-0 mr-0 mt-5">{{ mb_strimwidth(strip_tags($latestNews->body), 0, 50,'...', 'utf-8') }}</p>
                        <p class="ml-0 mr-0 mt-0 see_more">{{ __('custom.see_more') }} <i class="fa fa-angle-double-right"></i></p>
                    </a>
                </div>
            </div>

        </div>
    </div>
    <div class="col-md-4 most-active">
        <a href="{{ isset($mostActiveOrg->uri) ? url('organisation/profile/'. $mostActiveOrg->uri) : '#' }}">
            <img src="{{ asset('/img/medal.svg') }}">
            <p>{{ __('custom.most_active_agency') }} {{ $lastMonth }}</p>
            <hr>
            <span>{{ isset($mostActiveOrg->name) ? $mostActiveOrg->name : 'N/A' }}</span>
            <img
                class="org-logo"
                src="{{ isset($mostActiveOrg->logo) ? $mostActiveOrg->logo : asset('img/open-data.png') }}"
            >
        </a>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="heading">{{ utrans('custom.topics') }}</h4>
            <div class="picks-box">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('data', ['category' => [$category->id]]) }}"
                    >
                        <span class="svg">
                            @if (!empty($category->icon_data))
                                {!! $category->icon_data !!}
                            @else
                                @if (file_exists(public_path('img/uncategorized.svg')))
                                    {!! file_get_contents(public_path('img/uncategorized.svg')) !!}
                                @endif
                            @endif
                        </span>
                        <p>{{ $category->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
