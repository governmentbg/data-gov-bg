@extends('layouts.app')

@section('content')
<div class="container user-profile">
    <div class="col-xs-12 col-lg-10 m-t-md col-lg-offset-1">
        <div class="flash-message">
            @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">
                        {{ Session::get('alert-' . $msg) }}
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    </p>
                @endif
            @endforeach
        </div>

        <div class="col-sm-12 col-xs-12">
            <div class="filter-content">
                <div class="col-md-12 col-lg-offset-2">
                    <div class="row">
                        <div class="col-sm-12">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a href="{{ url('/users/list') }}">{{ trans_choice(__('custom.users'), 2) }}</a></li>
                                    <li><a class="active" href="#">{{ trans_choice(__('custom.users'), 1) }}</a></li>
                                    <li><a href="{{ route('data', ['user' => [$user->id]]) }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/user/profile/chronology/'. $user->id) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row basic-stats">
                <div class="col-md-4">
                    <a class="reg-users">
                        <p>{{ $followersCount }}</p>
                        <hr>
                        <p>{{ __('custom.followers') }}</p>
                        <img src="{{ asset('/img/followers.svg') }}">
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('data', ['user' => [$user->id]]) }}" class="data-sets">
                        <p>{{ $dataSetsCount }}</p>
                        <hr>
                        <p>{{ __('custom.data_sets') }}</p>
                        <img src="{{ asset('/img/data-sets.svg') }}">
                    </a>
                </div>
            </div>
            <div class="user-data">
                <div class="row">
                    <h2>{{ $user->firstname .' '. $user->lastname}}</h2>
                </div>
                <div class="row">
                    <span class="user-info">{!! nl2br(e($user->add_info)) !!}</span>
                </div>
                @if (!$ownProfile && \Auth::user() !== null)
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
                @endif
                <div class="row contacts">
                    <p>{{ uctrans('custom.to_contact') }}</p><br>
                    <p class="email">Email: {{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
