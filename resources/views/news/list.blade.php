@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class=" m-t-lg">
                @for ($i = 0; $i < 3; $i++)
                    <div class="m-b-lg">
                        <div>Дата на добавяне: {{ date('d.m.Y') }}</div>
                        <div class="col-sm-12 p-l-none article-underline">
                            <a href="{{ url('/news/view') }}">
                                <h2 class="m-t-xs">Lorem ipsum dolor sit amet</h2>
                            </a>
                            <p>
                                Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                            </p>
                            <div class="col-sm-12 p-l-none text-right">
                                <span><a href="{{ url('/news/view') }}">Виж още</a></span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
