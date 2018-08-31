@extends('layouts.app')

@section('content')
<div class="container users-list">
    <div class="col-xs-12 col-lg-10 m-t-md">
        <div class="row">
            <div class="flash-message">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if (Session::has('alert-' . $msg))
                        <p class="alert alert-{{ $msg }}">
                            {{ Session::get('alert-' . $msg) }}
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        </p>
                    @endif
                @endforeach
            </div>
            <div class="col-sm-3 col-xs-12 sidenav">
                <h2 class="my-profile">{{ __('custom.users_list') }}</h2>
            </div>
            <div class="col-sm-9 col-xs-12">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="GET" action="{{ url('/users/list') }}">
                            <input
                                type="text"
                                class="input-border-r-12 form-control user-search"
                                placeholder=" Търсене.."
                                value="{{ isset($search) ? $search : '' }}"
                                name="search"
                            >
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        {{ $pagination->render() }}
                    </div>
                </div>
                <div class="row">
                    @foreach ($users as $user)
                        <div class="col-md-4 col-xs-12 user-col">
                            <a href="{{ url('/user/profile/'. $user->id) }}"><h3 class="user-name">{{ $user->username }}</h3></a>
                            <div class="user-desc">{{ $user->add_info }}</div>
                            <p class="text-right show-more"><a href="{{ url('/user/profile/'. $user->id) }}" class="view-profile">{{ __('custom.see_more') }}</a></p>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        {{ $pagination->render() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
