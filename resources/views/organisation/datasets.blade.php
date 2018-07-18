@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar-org')
        <div class="col-sm-9 col-xs-11 page-content">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/organisation/profile') }}">профил</a></li>
                                    <li><a class="active" href="{{ url('/organisation/datasets') }}">данни</a></li>
                                    <li><a href="{{ url('/organisation/chronology') }}">поток на дейността</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="articles">
                @for ($i = 0; $i < 2; $i++)
                    <div class="article m-t-md m-b-md">
                        <div class="col-sm-12 p-l-none">
                            <a href="{{ url('/organisation/viewDataset') }}"><h2>Lorem ipsum dolor sit amet</h2></a>
                            <p>
                                Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                            </p>
                            <div class="col-sm-12 p-l-none m-t-md">
                                <div class="pull-right">
                                    <span class="badge badge-pill"><a href="{{ url('/organisation/viewDataset') }}">Разгледай</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
