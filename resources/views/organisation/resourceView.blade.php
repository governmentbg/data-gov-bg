@extends('layouts.app')

@section('content')

@include('partials.add-signal', ['postUrl' => 'organisation/resource/sendSignal'])

<div class="container">
    @include('partials.alerts-bar')
    <div class="row">
        <div class="col-sm-9 col-xs-11 page-content p-sm col-sm-offset-3">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="articles">
                <div class="article m-t-lg m-b-md">
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none m-t-lg m-b-md">
                            <div class="col-xs-6 logo-img">
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}" title="{{ $organisation->name }}">
                                    <img class="img-responsive" src="{{ $organisation->logo }}" alt="{{ $organisation->name }}">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none">
                            <h3>
                                {{ uctrans('custom.dataset') }}:&nbsp;
                                <a href="{{ url('/organisation/dataset/'. $dataset->uri) }}">{{ $dataset->name }}</a>
                            </h3>
                            <div class="info-bar-sm col-sm-7 col-xs-12 p-l-none">
                                <ul class="p-l-none p-h-sm">
                                    <li>{{ utrans('custom.version') }}:&nbsp;{{ $resource->version }}</li>
                                    <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                                    <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                                    @if (!empty($resource->updated_by))
                                        <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                                        <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 p-l-none art-heading-bar">
                        <div class="socialPadding">
                            <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                            <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                            <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                        </div>
                        @if ($approved)
                            <div class="status p-w-sm m-l-sm">
                                <span>{{ __('custom.approved') }} </span>
                            </div>
                        @else
                            <div class="status notApproved p-w-sm m-l-sm">
                                <span>{{ __('custom.unapproved') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-xs-12 p-l-none">
                        <h2>{{ $resource->name }}</h2>
                        @if (!empty($resource->description))
                            <p>{{ $resource->description }}</p>
                        @endif
                        <div class="col-xs-12 p-l-none">
                            <div class="tags pull-left">
                                @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                    @foreach ($dataset->tags as $tag)
                                        <span class="badge badge-pill">{{ $tag->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-12 m-t-lg p-l-r-none">
                            @if (empty($data))
                                <div class="col-sm-12 m-t-lg text-center">{{ __('custom.no_info') }}</div>
                            @else
                                @include('partials.resource-visualisation')
                            @endif
                        </div>

                       <div class="col-xs-12 pull-left m-t-md p-l-r-none">
                            <div class="col-md-6 col-xs-12 text-left p-l-r-none m-b-md">
                                <div class="badge-info m-r-md pull-left">
                                    <span class="badge badge-pill js-toggle-info-box m-b-sm">{{ __('custom.information') }}</span>
                                    <div class="info-box">
                                        <p>
                                        {{ __('custom.row') }}<br>
                                        {{ __('custom.from') }} ... &nbsp; {{ __('custom.to') }} ...
                                        </p>
                                        <p>
                                        {{ __('custom.column') }}<br>
                                        {{ __('custom.from') }} ... &nbsp; {{ __('custom.to') }} ...
                                        </p>
                                    </div>
                                </div>
                                <div class="badge-info m-r-md">
                                    <span class="badge badge-pill js-toggle-info-box m-b-sm">{{ __('custom.show_as') }}</span>
                                    <div class="info-box">
                                        <p>lorem ipsum</p>
                                        <p>lorem ipsum</p>
                                        <p>lorem ipsum</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12 text-right p-l-r-none m-b-md group-three">
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.download') }}</a></span>
                                <button type="button" class="badge badge-pill m-b-sm" data-toggle="modal" data-target="#addSignal">{{ __('custom.signal') }}</button>
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.comment') }}</a></span>
                            </div>
                        </div>
                        <!-- IF there are old versions of this article -->
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="pull-left history">
                                <div>
                                    <a href="#">
                                        <span class="version-heading">{{ __('custom.title') }}</span>
                                        <span class="version">&nbsp;&#8211;&nbsp;версия 3</span>
                                    </a>
                                </div>
                                <div>
                                    <a href="#">
                                        <span class="version-heading">{{ __('custom.title') }}</span>
                                        <span class="version">&nbsp;&#8211;&nbsp;версия 2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- IF there are commnets -->
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments p-lg">
                                @for ($i=0; $i<3; $i++)
                                    <div class="comment-box p-lg m-b-lg">
                                        <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                        <p class="comment-author p-b-xs">{{ __('custom.profile_name') }}</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
