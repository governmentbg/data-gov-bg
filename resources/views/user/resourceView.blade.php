@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => Request::segment(2)])
    <div class="col-xs-12 m-t-md">
        <div class="articles">
            <div class="article m-b-md">
                <div class="m-t-lg">
                    <div class="col-sm-12 col-xs-12 p-l-none">
                        <ul class="p-l-none">
                            <li>{{ __('custom.contact_support_name') }}:</li>
                            <li>{{ __('custom.version') }}: {{ $resource->version }}</li>
                            <li>{{ __('custom.last_update') }}: {{ $resource->updated_by }}</li>
                            <li>{{ __('custom.created') }}: {{ $resource->created_by }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-12 p-l-none art-heading-bar">
                    <div class="socialPadding">
                        <div class="social fb"><a href="#"><i class="fa fa-facebook"></i></a></div>
                        <div class="social tw"><a href="#"><i class="fa fa-twitter"></i></a></div>
                        <div class="social gp"><a href="#"><i class="fa fa-google-plus"></i></a></div>
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
                            >{{ uctrans('custom.download') }}</a>
                        @endif
                    </div>

                    <div class="col-xs-12 m-t-md p-l-r-none text-right">
                        <form method="POST">
                            {{ csrf_field() }}
                            <button
                                name="delete"
                                class="badge badge-pill m-b-sm del-btn"
                                data-confirm="{{ __('custom.remove_data') }}"
                            >{{ uctrans('custom.remove') }}</button>
                        </form>
                    </div>
                    <!-- IF there are old versions of this article -->
                    <div class="col-sm-12 pull-left m-t-md p-l-none">
                        <div class="pull-left history">
                            <div>
                                <a href="#">
                                    <span class="version-heading">{{ __('custom.title') }}</span>
                                    <span class="version">&nbsp;&#8211;&nbsp;версия 1</span>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
