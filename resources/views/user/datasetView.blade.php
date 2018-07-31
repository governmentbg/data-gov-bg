@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 col-xs-12 sidenav">
                    <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
                </div>
                <div class="col-sm-9 col-xs-12">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li>
                                                <!-- if there is resource with signal -->
                                                <div class="col-xs-12 text-center exclamation-sign">
                                                    <img src="{{ asset('img/reported.svg') }}">
                                                </div>
                                                <!-- end-->
                                                <a class="active" href="{{ url('/user/datasets') }}">моите данни</a>
                                            </li>
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                            <li><a href="{{ url('/user/invite') }}">покана</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 m-t-lg">
                    <div class="articles">
                        <div class="article m-b-md">
                            <div class="col-sm-12">
                                <div>Дата на добавяне: {{ $dataset->created_at }}</div>
                                <h2>{{ $dataset->name }}</h2>
                                <p>
                                    {{ $dataset->descript }}
                                </p>

                                <div class="col-sm-12 pull-left m-t-md p-l-none">
                                    <div class="pull-left history">
                                        @for($i = 1; $i <= 3; $i++ )
                                            <div>
                                                <a href="{{ url('/user/resourceView') }}">
                                                    <span>
                                                        <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                                    </span>
                                                    <span class="version-heading">Ресурс</span>
                                                    <span class="version">&nbsp;&#8211;&nbsp;име</span>
                                                </a>
                                            </div>
                                        @endfor
                                        <!-- if resource has signals add class signaled-->
                                        <div class="signaled">
                                            <a href="{{ url('/user/resourceView') }}">
                                                <span>
                                                    <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                                </span>
                                                <span class="version-heading">Ресурс</span>
                                                <span class="version">&nbsp;&#8211;&nbsp;име</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-bar col-sm-7 col-xs-12 p-l-none">
                                    <ul class="p-l-none">
                                        <li>Отговорник подръжка:</li>
                                        <li>Създаден:</li>
                                    </ul>
                                </div>
                                <!-- IF there are old versions of this article -->
                                <div class="col-xs-12 pull-left m-t-md p-l-none">
                                    <div class="pull-left history">
                                        <div class="m-b-sm">
                                            <a href="#">
                                                <span class="version">Версия 1</span>
                                            </a>
                                        </div>
                                        <div>
                                            <a href="#">
                                                <span class="version">Версия 2</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- signals -->
                                <div class="col-sm-12 pull-left m-t-md p-l-none">
                                    <div class="comments signal p-lg">
                                        <div class="comment-box p-lg m-b-lg">
                                            <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                            <p class="comment-author p-b-xs">Име на профила</p>
                                            <p class="p-b-xs">Съдържание на сигнала</p>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 m-t-lg p-l-none">
                                    <div class="pull-left">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/edit') }}">редактиране</a></span>

                                        <form method="POST" action="{{ url('/user/deleteDataset') }}">
                                            {{ csrf_field() }}
                                            <div class="col-xs-6 text-right">
                                                <button
                                                    class="badge badge-pill m-b-sm"
                                                    type="submit"
                                                    name="delete"
                                                    onclick="return confirm('Изтриване на данните?');"
                                                >премахване</button>
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
    </div>
</div>
@endsection
