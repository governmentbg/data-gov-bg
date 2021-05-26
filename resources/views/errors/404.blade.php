@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-xs-12 m-l-sm mt-10">
            <div class="alert alert-warning">
                Страницата която търсите не може да бъде намерена! Можете да отидете на началния екран на портала
                <a href="{{ url('/') }}" class="btn btn-primary">тук</a>
            </div>
        </div>
    </div>
@endsection
