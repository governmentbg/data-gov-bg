@extends('layouts.app', [
'title'            => isset($newsList->head_title) ? $newsList->head_title : null,
'keywords'         => isset($newsList->meta_keywords) ? $newsList->meta_keywords : null,
'description'      => isset($newsList->meta_description) ? $newsList->meta_description : null
])

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-b-md">
            <div class=" m-t-lg">
                <div class="articles">
                    <div class="article">
                        <div class="m-b-lg">
                            <div class="col-sm-12 p-l-none article-underline">
                                @if (isset($newsList))
                                    <h2 class="m-t-xs">{!! $newsList->title !!}</h2>
                                    <p>
                                        {!! $newsList->body !!}
                                    </p>
                                @endif

                                <div class="col-xs-12 m-t-sm p-l-none text-right">
                                @if (\App\Role::isAdmin())
                                    <span class="badge badge-pill"><a href="{{ url('/admin/news/edit/' . $newsList->id) }}">{{ uctrans('custom.edit') }}</a></span>
                                @endif
                                <span class="badge badge-pill"><a href="#">{{ uctrans('custom.comment') }}</a></span>
                                </div>
                            </div>
                        </div>
                        @if (isset($discussion))
                            <div class="row discussion">
                                @include('vendor.chatter.discussion')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
