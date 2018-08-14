@extends('layouts.app')
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => $activeMenu])
        @if (isset($resource->name))
            <div class="row">
                <div class="col-sm-10 col-xs-12 m-t-lg m-l-10">
                    <div class="articles">
                        <div class="article m-b-md">
                            <div>
                                <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none m-b-md">
                                    <ul class="p-l-none">
                                        <li>{{ __('custom.contact_support_name') }}:</li>
                                        <li>{{ utrans('custom.version') }}:&nbsp;{{ $resource->version }}</li>
                                        <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                                        <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                                        <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                                        <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-12 p-l-none art-heading-bar">
                                <div class="socialPadding">
                                    <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                    <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                    <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                </div>
                                <div class="sendMail p-w-sm">
                                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                </div>
                            </div>
                            <div class="col-sm-12 p-l-none">
                                <h2>{{ $resource->name }}</h2>
                                <p>{{ $resource->description }}</p>
                                <div class="col-sm-12 p-l-none">
                                    <div class="tags pull-left">
                                        <span class="badge badge-pill">ТАГ</span>
                                        <span class="badge badge-pill">ДЪЛЪГ ТАГ</span>
                                        <span class="badge badge-pill">ТАГ</span>
                                    </div>
                                </div>

                                <div class="col-sm-12 m-t-lg p-l-r-none">
                                    @if (empty($data))
                                        <div class="col-sm-12 m-t-lg text-center">
                                            {{ __('custom.no_info') }}
                                        </div>
                                    @else
                                        <table class="table m-t-lg">
                                            @foreach ($data as $index => $row)
                                                @if ($index == 0)
                                                    @foreach ($row as $key => $value)
                                                        <th><p>{{ $value }}</p></th>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        @foreach ($row as $key => $value)
                                                            <td>{{ $value }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                        <a
                                           class="badge badge-pill pull-right"
                                           href="{{ url('/user/resource/download/'. $resource->es_id. '/'. $resource->name) }}"
                                        >{{ __('custom.download') }}</a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-md p-l-r-none text-right">
                                <form method="POST">
                                    {{ csrf_field() }}
                                    <button
                                        name="delete"
                                        class="badge badge-pill m-b-sm"
                                        data-confirm="{{ __('Изтриване на данните?') }}"
                                    >{{ __('custom.remove') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-12 m-t-xl no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
