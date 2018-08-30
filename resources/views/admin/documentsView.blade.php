@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'documents'])
    @if (!is_null($document))
        <div class="row">
            <div class="col-xs-12">
                <div class="m-t-lg">
                    <div class="articles">
                        <div class="article">
                            <div class="m-b-lg">
                                <div class="col-sm-12 p-l-none article-underline">
                                    <a href="{{ url('/news/view') }}">
                                        <h2 class="m-t-xs">{{ $document->name }}</h2>
                                    </a>
                                    <p>{{ $document->description }}</p>
                                    <p>{{ $document->filename }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 m-t-sm p-l-none text-right">
                            <form
                                method="POST"
                                class="inline-block"
                                action="{{ url('/admin/documents/view/'. $document->id) }}"
                            >
                                {{ csrf_field() }}
                                <button class="badge badge-pill" type="submit">{{ uctrans('custom.download') }}</button>
                                <input type="hidden" name="download" value="1">
                            </form>
                            <span class="badge badge-pill">
                                <a
                                    href="{{ url('/admin/documents/edit/'. $document->id) }}"
                                >{{ utrans('custom.edit') }}</a>
                            </span>
                            <span class="badge badge-pill red">
                                <a
                                    href="{{ url('/admin/documents/delete/'. $document->id) }}"
                                    data-confirm="{{ __('custom.remove_data') }}"
                                >{{ __('custom.delete') }}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-sm-12 m-t-lg text-center">
                {{ __('custom.no_info') }}
            </div>
        </div>
    @endif
</div>
@endsection
