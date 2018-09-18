@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-offset-3 p-sm">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/data') }}">{{ __('custom.data') }}</a></li>
                                    <li><a class="active" href="{{ url('/data/linkedData') }}">{{ __('custom.linked_data') }}</a></li>
                                    <li><a href="{{ url('/data/reported') }}">{{ __('custom.signal_data') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (!request()->has('search_results_url'))
            <div class="col-xs-12 m-t-md">
                <h1>Elasticsearch</h1>
            </div>
        @endif
    </div>
</div>

<div class="container-fluid p-l-r-none">
    <div class="col-md-12 underline-thin"></div>
</div>

<div class="container related-data p-sm">
    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-xs-12">
                <!--
                <div class="m-t-md">
                    <p>
                    {{ __('custom.related_message') }}
                    </p>
                </div>
                <div class="m-t-lg">
                    <h3>{{ __('custom.namespaces') }} *</h3>
                </div>
                <div class="col-xs-12 p-l-none m-t-lg">
                    <p class="example">
                        <span>PREFIX dcat: &lt;http://www.w3.org/ns/dcat#&gt;</span>
                        <span>PREFIX odp: &lt;http://data.europa.eu/euodp/ontlogies/ec-odp#&gt;</span>
                        <span>PREFIX dc: &lt;http://purl.org/dc/terms/&gt;</span>
                        <span>PREFIX xsd: &lt;http://www.w3.org/2001/XMLSchema#&gt;</span>
                        <span>PREFIX foaf: &lt;http://xmlns.com/foaf/0.1/&gt;</span>
                    </p>
                </div>
                 -->
                 @if ($errors->has('common'))
                    <p class="alert alert-danger">
                        {{ $errors->first('common') }}
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    </p>
                @endif
                <div class="m-b-lg m-t-none">
                    <h3>{{ __('custom.search_query') }} *</h3>
                </div>
                @if (request()->has('search_results_url'))
                <div class="col-xs-12 p-l-none">
                    <textarea
                        id="query_str"
                        class="input-border-r-12 form-control"
                        rows="10"
                        name="query_str"
                        readonly
                    >{{ $searchResultsUrl }}</textarea>
                </div>
                @else
                <div class="col-xs-12 p-l-none">
                    <textarea
                        id="query"
                        class="input-border-r-12 form-control"
                        rows="10"
                        name="query"
                        placeholder='{!! "{\r\n\t\"query\": {\r\n\t\t\"match_all\": {}\r\n\t},\r\n\t\"sort\": [\r\n\t\t{\"field\": {\"order\" : \"asc\"}}\r\n\t]\r\n}" !!}'
                        required
                    >{{ old('query') }}</textarea>
                    <span class="error">{{ $errors->first('query') }}</span>
                </div>
                <div class="col-xs-12 p-h-lg rel-data-constr">
                    <div class="row m-t-md">
                        <div class="col-md-4 col-sm-5">
                            <p>
                                <span>{{ uctrans('custom.format') }} *</span>&nbsp;
                                <select class="input-border-r-12 m-l-xs" name="format">
                                    @foreach ($formats as $format => $formatName)
                                        <option value="{{ $format }}"{{ $format == $selectedFormat ? 'selected="selected"' : '' }}>{{ $formatName }}</option>
                                    @endforeach
                                </select>
                            </p>
                        </div>
                        <div class="col-md-offset-1 col-md-7 col-sm-7">
                            <p>
                                <span> {{ __('custom.limit_results') }} *</span>&nbsp;
                                <input class="input-border-r-12" name="limit_results" value="{{ old('limit_results') }}">
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 p-h-sm">
                    <span class="m-r-md m-b-sm">
                        <button type="submit" class="btn primary badge badge-pill m-b-sm">{{ __('custom.begin_search') }}</button>
                    </span>
                    <span class="m-r-md m-b-sm">
                        <button type="submit" name="search_results_url" class="btn primary badge badge-pill m-b-sm">{{ __('custom.search_results_url') }}</button>
                    </span>
                </div>
                <!--
                <div class="col-xs-12 p-h-sm">
                    <h3>{{ __('custom.metadata_vocab') }}</h3>
                    <p>{{ __('custom.vocab_message') }}</p>
                </div>
                -->
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
