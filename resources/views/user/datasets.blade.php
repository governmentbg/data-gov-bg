@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar')
    <div class="row">
        <span class="badge badge-pill m-t-lg new-data"><a href="{{ url('/user/datasetCreate') }}">Добави нов набор</a></span>
    </div>
    <div class="col-xs-12 m-t-md">
        <div class="articles m-t-lg">
            @foreach ($datasets as $set)
                <div class="article m-b-lg">
                    <div>Дата на добавяне: {{ $set->created_at }}</div>
                    <div class="col-sm-12 p-l-none">
                        <a href="{{ route('datasetView', ['uri' => $set->uri]) }}">
                            <h2 class="m-t-xs">{{ $set->name }}</h2>
                        </a>
                        <p>{{ $set->descript }}</p>
                        <div class="col-sm-12 p-l-none">
                            <div class="pull-left">
                                <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/datasetEdit') }}">редактиране</a></span>
                                <form method="POST" action="{{ url('/user/datasetDelete') }}">
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
@endsection
