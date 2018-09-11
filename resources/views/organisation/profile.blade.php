@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="row">
             <div class="col-sm-offset-3 filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="active p-l-none" href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row m-t-lg">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        @if (isset($parentOrg))
                            <a href="{{ url('/organisation/profile/'. $parentOrg->uri) }}">
                                <img class="img-responsive" src="{{ $parentOrg->logo }}"/>
                            </a>
                        @else
                            <img class="img-responsive" src="{{ $organisation->logo }}"/>
                        @endif
                    </div>
                    <div class="col-md-9 col-sm-6 col-xs-12 info-box">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="followers">
                                    <p>{{ $organisation->followers_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.followers') }} </p>
                                    <img src="{{ asset('/img/followers.svg') }}">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="data-sets">
                                    <p>{{ $organisation->datasets_count }}</p>
                                    <hr>
                                    <p>{{ __('custom.data_sets') }}</p>
                                    <img src="{{ asset('/img/data-sets.svg') }}">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($parentOrg))
                    <div class="row">
                        <div class="col-xs-12">
                            <br/>
                            <h3>
                                {{ __('custom.main_org') }}:
                                <a href="{{ url('/organisation/profile/'. $parentOrg->uri) }}">{{ $parentOrg->name }}</a>
                            </h3>

                        </div>
                    </div>
                @endif

                <div class="m-t-lg">
                    <div class="m-b-md">
                        @if (isset($parentOrg))
                            <div class="col-md-2 col-sm-6 col-xs-12">
                                <img class="img-responsive" src="{{ $organisation->logo }}"/>
                            </div>
                        @endif
                        <div>
                            <div class="col-xs-12 p-l-none">
                                <div>
                                    <h3>{{ $organisation->name }} </h3><br/>
                                    <p>{{ $organisation->description }}</p><br/>
                                </div>
                            </div>
                            <div class="col-xs-12 p-l-r-none articles">
                                <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                    <span>{{ __('custom.contact_person') }}</span><br/><br/>
                                    <p>{{ $organisation->contacts }}</p><br/>
                                </div>
                                <div class="col-sm-4 col-xs-12 pull-right text-right">
                                    @auth
                                        <form method="post">
                                            {{ csrf_field() }}
                                            @if (!$followed)
                                                <div class="row">
                                                    <button
                                                        class="btn btn-primary pull-right"
                                                        type="submit"
                                                        name="follow"
                                                    >{{ utrans('custom.follow') }}</button>
                                                </div>
                                            @else
                                                <div class="row">
                                                    <button
                                                        class="btn btn-primary pull-right"
                                                        type="submit"
                                                        name="unfollow"
                                                    >{{ uctrans('custom.stop_follow') }}</button>
                                                </div>
                                            @endif
                                        </form>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (count($childOrgs) > 0)
            <div class="row">
                <div class="col-sm-12">
                    <hr>
                </div>
            </div>
            <h3>{{ __('custom.child_orgs') }}</h3><br/>
            @foreach ($childOrgs as $childOrg)
                <div class="row">
                    <div class="col-sm-12">
                        <a href="{{ url('/organisation/profile/'. $childOrg->uri) }}">{{ $childOrg->name }}</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
