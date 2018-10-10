@extends('layouts.app')

@section('content')
    <div class="container help">
        <div class="row">
            <div class="row m-l-lg m-r-lg m-b-lg">
                <h2><img class="icon m-r-sm" src="{{ asset('/img/help_section.svg') }}">{{ utrans('custom.help_sections') }}</h2>
                <form method="GET" action="{{ url('help/search') }}">
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
            <div class="row">
                @if (!empty($records))
                    <div class="result-cont">
                        <h2>{{ sprintf(__('custom.search_found'), count($records)) }}</h2>
                        <hr>
                        <div class="result-wrap">
                            <div class="nano">
                                <div class="nano-content">
                                    @foreach ($records as $record)
                                        <div class="result m-b-md">
                                            <span class="info">"{{ $search }}" - </span>
                                            @if (!empty($record->parent[0]->title))
                                                {{ $record->parent[0]->title }} /
                                            @endif
                                            {{ $record->section_name }} /
                                            {{ $record->title }} -
                                            <a
                                                @if (!empty($record->parent[0]->id))
                                                    href="{{ url('help/view/'. $record->parent[0]->id) }}"
                                                @else
                                                    href="{{ url('help/view/'. $record->section_id) }}"
                                                @endif
                                            >{{ __('custom.link') }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
