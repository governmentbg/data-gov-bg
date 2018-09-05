@extends('layouts.app')

@section('content')
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
                <div class="article m-b-md m-t-lg">
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none m-t-md m-b-lg">
                            <div class="col-xs-6 logo-img">
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">
                                    <img class="img-responsive" src="{{ $organisation->logo }}"/>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none">
                            @if (!empty($dataset->source))
                                <p>
                                    <strong>{{ __('custom.source') }}:</strong>
                                    &nbsp;{{ $dataset->source }}
                                </p>
                            @endif
                            @if (!empty($dataset->author_name))
                                <p>
                                    <strong>{{ __('custom.author') }}:</strong>
                                    &nbsp;{{ $dataset->author_name }}
                                </p>
                            @endif
                            @if (!empty($dataset->author_email))
                                <p>
                                    <strong>{{ __('custom.contact_author') }}:</strong>
                                    &nbsp;{{ $dataset->author_email }}
                                </p>
                            @endif
                            @if (!empty($dataset->support_email))
                                <p>
                                    <strong>{{ __('custom.contact_support_name') }}:</strong>
                                    &nbsp;{{ $dataset->support_email }}
                                </p>
                            @endif
                            @if (!empty($dataset->support_email))
                                <p>
                                    <strong>{{ __('custom.contact_support') }}:</strong>
                                    &nbsp;{{ $dataset->support_email }}
                                </p>
                            @endif
                            @if (!empty($dataset->sla))
                                <p>
                                    <strong>{{ __('custom.sla_agreement') }}:&nbsp;</strong>
                                </p>
                                <div class="m-b-sm">{{ $dataset->sla }}</div>
                            @endif
                                <p>
                                    <strong>{{ __('custom.version') }}:</strong>
                                    &nbsp;{{ $dataset->version }}
                                </p>
                                <p>
                                    <strong>{{ __('custom.last_update') }}:</strong>
                                    &nbsp;{{ $dataset->updated_at }}
                                </p>
                                <p>
                                    <strong>{{ __('custom.created') }}:</strong>
                                    &nbsp;{{ $dataset->created_at }}
                                </p>
                        </div>
                    </div>
                    <div class="col-xs-12 p-l-none art-heading-bar">
                        <div class="socialPadding">
                            <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                            <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                            <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                        </div>
                        <div class="sendMail p-w-sm">
                            <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                        </div>
                        @if ($approved)
                            <div class="status p-w-sm">
                                <span>{{ __('custom.approved') }} </span>
                            </div>
                        @else
                            <div class="status notApproved p-w-sm">
                                <span>{{ __('custom.unapproved') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-xs-12 p-l-none">
                        <h2>{{ $dataset->name }}</h2>
                        @if (!empty($dataset->description))
                            <p>{{ $dataset->description }}</p>
                        @endif
                        @if (!empty($dataset->terms_of_use_id))
                            <p>
                                <strong>{{ utrans('custom.license', 1) }}:</strong>
                                &nbsp;{{ $dataset->terms_of_use_name }}
                            </p>
                        @endif
                        @if (!empty($dataset->category_id))
                            <p>
                                <strong>{{ __('custom.main_topic') }}:</strong>
                                &nbsp;{{ $dataset->category_name }}
                            </p>
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
                        <!-- chart goes here -->
                        <div class="col-xs-12 pull-left m-t-lg p-l-none">
                            <img class="img-responsive" src="{{ asset('img/test-img/bar-chart.jpg') }}">
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
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.signal') }}</a></span>
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
