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
                                    <li><a class="active" href="{{ url('/data/relatedData') }}">{{ __('custom.linked_data') }}</a></li>
                                    <li><a href="{{ url('/data/reportedList') }}">{{ __('custom.signal_data') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 m-t-md">
            <h1>SPARQL</h1>
        </div>
    </div>
</div>

<div class="container-fluid p-l-r-none">
    <div class="col-md-12 underline-thin"></div>
</div>

<div class="container related-data p-sm">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div>
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
            <div class="p-h-sm">
                <h3>{{ __('custom.sparql_search') }} *</h3>
            </div>
            <div class="col-xs-12 p-l-none">
                <input class="input-border-r-12 input-long" name="query" placeholder="select distinct ?g ?o where {graph ?g {?s dc:title ?o. filter regex(?o, 'Statistics','i')}}} LIMIT 10">
            </div>
            <div class="col-xs-12 p-h-lg rel-data-constr">
                <div class="row m-t-md">
                    <div class="col-md-4 col-sm-5">
                        <p>
                            <span>{{ uctrans('custom.format') }} *</span>&nbsp;
                            <input class="input-border-r-12" name="format">
                        </p>
                    </div>
                    <div class="col-md-offset-1 col-md-7 col-sm-7">
                        <p>
                            <span> {{ __('custom.limit_results') }} *</span>&nbsp;
                            <input class="input-border-r-12" name="format">
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 p-h-sm">
                <span class="badge badge-pill m-r-md m-b-sm"><a href="#"> {{ __('custom.begin_search') }}</a></span>
                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.search_results_url') }}</a></span>
            </div>
            <div class="col-xs-12 p-h-sm">
                <h3>{{ __('custom.metadata_vocab') }}</h3>
                <p>{{ __('custom.vocab_message') }}</p>
            </div>
        </div><!-- .col-md-12 -->
    </div>
</div>
@endsection
