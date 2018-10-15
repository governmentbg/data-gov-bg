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
                            <h2>{{ uctrans('custom.document_preview') }}</h2>
                                <div class="col-sm-12 p-l-none article-underline">
                                    <h2 class="m-t-xs">{{ $document->name }}</h2>
                                    <p>{!! nl2br(e($document->description)) !!}</p>
                                    <p>{{ $document->filename }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 m-t-sm p-l-none text-right">
                        <a href="{{ url('admin/documents/list') }}" class="badge badge-pill">{{ __('custom.back') }}</a>
                            <form
                                method="POST"
                                class="inline-block"
                                action="{{ $document->data }}"
                            >
                                {{ csrf_field() }}
                                <button
                                    class="badge badge-pill js-ga-event"
                                    type="submit"
                                    data-ga-action="download"
                                    data-ga-label="document download"
                                    data-ga-category="data"
                                >{{ uctrans('custom.download') }}</button>
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
        <div class="text-center m-b-lg terms-hr">
            <hr>
        </div>
        <div class="form-group row m-b-lg m-t-md">
            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
            <div class="col-sm-6 col-xs-12">
                <div>{{ $document->created_by }}</div>
            </div>
        </div>
        <div class="form-group row m-b-lg m-t-md">
            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
            <div class="col-sm-6 col-xs-12">
                <div>{{ $document->created_at }}</div>
            </div>
        </div>
        @if ($document->created_at != $document->updated_at)
            <div class="form-group row m-b-lg m-t-md">
                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                <div class="col-sm-6 col-xs-12">
                    <div>{{ $document->updated_by }}</div>
                </div>
            </div>
            <div class="form-group row m-b-lg m-t-md">
                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                <div class="col-sm-6 col-xs-12">
                    <div>{{ $document->updated_at }}</div>
                </div>
            </div>
        @endif
    @else
        <div class="row">
            <div class="col-sm-12 m-t-lg text-center">
                {{ __('custom.no_info') }}
            </div>
        </div>
    @endif
</div>
@endsection
