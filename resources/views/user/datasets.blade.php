@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
                    <span class="my-profile m-b-lg m-b-lg">Моят профил</span>
                    <span class="badge badge-pill m-t-lg new-data"><a  href="{{ url('/user/create') }}">Добави нов набор</a></span>
                </div>
                <div class="col-sm-9 cl-xs-12">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li><a class="active" href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="articles m-t-lg">
                        @foreach ( $datasets as $set)
                            <div class="article m-b-lg">
                                <div>Дата на добавяне: {{ $set->created_at }}</div>
                                <div class="col-sm-12 p-l-none">
                                    <a href="{{ url('/user/datasetView', ['uri' => $set->uri]) }}">
                                        <h2 class="m-t-xs">{{ $set->name }}</h2>
                                    </a>
                                    <p>
                                        {{ $set->descript }}
                                    </p>
                                    <div class="col-sm-12 p-l-none">
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
                                                <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                            </form>

                                        </div>
                                        <div class="pull-right">
                                            <span><a href="{{ url('/user/datasetView') }}">Виж още</a></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
