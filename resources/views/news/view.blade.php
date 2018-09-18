@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class=" m-t-lg">
                <div class="articles">
                    <div class="article">
                        <div class="m-b-lg">
                            <div class="col-sm-12 p-l-none article-underline">
                                @if (isset($newsList))
                                    <h2 class="m-t-xs">{{$newsList->title}}</h2>
                                    <p>
                                        {{$newsList->body}}
                                    </p>
                                @endif

                                <div class="col-xs-12 m-t-sm p-l-none text-right">
                                @if (\App\Role::isAdmin())
                                    <span class="badge badge-pill"><a href="#">{{ uctrans('custom.edit') }}</a></span>
                                @endif
                                <span class="badge badge-pill"><a href="#">{{ uctrans('custom.comment') }}</a></span>
                                </div>
                            </div>
                        </div>
                        <!-- IF there are commnets -->
                        <!-- <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments p-lg">
                                @for ($i=0; $i<3; $i++)
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
</div>
@endsection
