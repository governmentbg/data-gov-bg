@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class=" m-t-lg">
                <div class="articles">
                    <div class="article">
                        @if (isset($document))
                            <div class="m-b-lg">
                                <div class="col-sm-12 p-l-none article-underline">
                                    <h2 class="m-t-xs">{{$document->name}}</h2>
                                    <p>
                                        {!! nl2br(e($document->description)) !!}
                                    </p>
                                    <div class="col-xs-12 m-t-sm p-l-none text-right">
                                        <span class="badge badge-pill">
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
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <!-- IF there are commnets -->
                        <!-- <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments p-lg">
                                @for ($i=0; $i<2; $i++)
                                    <div class="comment-box p-lg m-b-lg">
                                        <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                        <p class="comment-author p-b-xs">{{ __('custom.profile_name') }}</p>
                                        <p>
                                            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                                            sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation
                                            ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                        </p>
                                    </div>
                                @endfor
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (isset($discussion))
        <div class="row discussion">
            @include('vendor.chatter.discussion')
        </div>
    @endif
</div>
@endsection
