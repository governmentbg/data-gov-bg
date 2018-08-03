@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-md">
        <div class="col-xs-12 m-t-lg">
            <div class="articles">
                <div class="article m-b-md">
                    <div class="col-sm-12 user-dataset">
                        <div>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</div>
                        <div>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</div>
                        <div>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</div>
                        <div>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</div>
                    <div class="col-sm-12">
                        <div>{{ __('custom.date_added') }}: {{ $dataset->created_at }}</div>
                        <h2>{{ $dataset->name }}</h2>
                        <div class="desc">
                            {{ $dataset->descript }}
                        </div>
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="pull-left history">
                                @foreach($resources as $resource)
                                    <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                        <a href="{{ route('resourceView', ['uri' => $resource->uri]) }}">
                                            <span>
                                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                            </span>
                                            <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="info-bar col-sm-7 col-xs-12 p-l-none">
                            <ul class="p-l-none">
                                <li>{{ __('custom.author') }}: {{ $dataset->author_name }}</li>
                                <li>{{ __('custom.contact_author') }}: {{ $dataset->author_email }}</li>
                                <li>{{ __('custom.contact_support_name') }}: {{ $dataset->support_name }}</li>
                                <li>{{ __('custom.contact_support') }}: {{ $dataset->support_email }}</li>
                                <li>{{ __('custom.created') }}: {{ $dataset->created_at }}</li>
                            </ul>
                        </div>
                        <!-- IF there are old versions of this article -->
                        <div class="col-xs-12 pull-left m-t-md p-l-none">
                            <div class="pull-left history">
                                <div class="m-b-sm">
                                    <span class="version">{{ __('custom.version') }}&nbsp;{{ $dataset->version }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- signals -->
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments signal p-lg">
                                <div class="comment-box p-lg m-b-lg">
                                    <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                    <p class="comment-author p-b-xs">{{ __('custom.profile_name') }}</p>
                                    <p class="p-b-xs">{{ __('custom.signal_contents') }}</p>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 m-t-lg p-l-none">
                            <div class="pull-left">
                                <a type="button" class="badge badge-pill m-b-sm" href="{{ url('/user/edit') }}">{{ utrans('custom.edit') }}</a>
                                <form method="POST" action="{{ url('/user/deleteDataset') }}">
                                    {{ csrf_field() }}
                                    <div class="col-xs-6 text-right">
                                        <button
                                            class="badge badge-pill m-b-sm"
                                            type="submit"
                                            name="delete"
                                            onclick="return confirm('Изтриване на данните?');"
                                        >{{ utrans('custom.remove') }}</button>
                                    </div>
                                    <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
