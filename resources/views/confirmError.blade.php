@extends('layouts.app')

@section('content')
    <div class="confirm-error container text-center">
        <form method="post">
            {{ csrf_field() }}
            <div class="m-b-lg">
                <img class="responsive logo-error" src="{{ asset('img/opendata-logo-color.svg') }}">
            </div>
            <div class="wrap input-border-r-12">
                <span>Проблем при потвърждаване на Е-mail</span><br>
                <button
                    type="submit"
                    name="generate"
                    class="btn btn-primary"
                >Генерирай нов линк</button>
            </div>
        </form>
    </div>
@endsection
