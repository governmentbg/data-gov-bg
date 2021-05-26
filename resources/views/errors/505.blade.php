@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-xs-12 m-l-sm mt-10">
            <div class="alert alert-danger">
                Настъпи грешка! Можете да опитате да презаредите отново страницата или да отидете на началния екран на портала
                <a href="{{ url('/') }}" class="btn btn-primary">тук</a>
            </div>
        </div>
    </div>
@endsection
