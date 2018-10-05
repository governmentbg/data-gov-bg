@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if (!empty($newsList))
            <div class="col-sm-5 col-xs-12 pull-right">
                <form method="GET" action="{{ url('/news/search') }}">
                <input
                    type="text"
                    class="input-border-r-12 form-control js-ga-event"
                    placeholder="{{ __('custom.search') }}"
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                    data-ga-action="search"
                    data-ga-label="data search"
                    data-ga-category="data"
                >
                </form>
            </div>
        @endif
        <div class="col-xs-12">
            <div class=" m-t-lg">
                @if ($newsList)
                    @foreach ($newsList as $news)
                        <div class="m-b-lg">
                            <div> {{ __('custom.date_added') }} : {{ $news->created_at }}</div>
                            <div class="col-sm-12 p-l-none article-underline">
                                <a href="{{ url('/news/view/' . $news->id ) }}">
                                    <h2 class="m-t-xs">{!! $news->title !!}</h2>
                                </a>
                                <p>
                                    {!! $news->abstract !!}
                                </p>
                                <div class="col-sm-12 p-l-none text-right">
                                    <span><a href="{{ url('/news/view/' . $news->id ) }}">{{ __('custom.see_more') }}</a></span>
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
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
