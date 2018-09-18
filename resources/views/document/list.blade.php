@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class=" m-t-md">
                <div class="col-xs-12 m-b-md">
                @if (!empty($documents))
                    <div class="col-sm-5 col-xs-12 pull-right">
                        <form method="GET" action="{{ url('/document/search') }}">
                        <input
                            type="text"
                            class="input-border-r-12 form-control"
                            placeholder="{{ __('custom.search') }}"
                            value="{{ isset($search) ? $search : '' }}"
                            name="q"
                        >
                        </form>
                    </div>
                @endif
                </div>
                @if (isset($documents))
                    @foreach ($documents as $document)
                    <div class="m-b-lg">
                        <div>{{ __('custom.date_added') }}: {{ date($document->created_at) }}</div>
                        <div class="col-sm-12 p-l-none article-underline">
                            <a href="{{ url('/document/view/' . $document->id ) }}">
                                <h2 class="m-t-xs">{{$document->name}}</h2>
                            </a>
                            <p>
                                {{$document->description}}
                            </p>
                            <div class="col-sm-12 p-l-none text-right">
                                <span><a href="{{ url('/document/view/' . $document->id) }}">{{ __('custom.see_more') }}</a></span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
                @if (empty($documents))
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
