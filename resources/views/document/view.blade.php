@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class=" m-t-lg">
                <div class="articles">
                    <div class="article">
                        <div class="m-b-lg">
                            <div class="col-sm-12 p-l-none article-underline">
                                <a href="{{ url('/news/view') }}">
                                    <h2 class="m-t-xs">Lorem ipsum dolor sit amet</h2>
                                </a>
                                <p>
                                    Tortor at auctor urna nunc id cursus metus. Sed euismod nisi porta lorem mollis aliquam ut porttitor leo.
                                    Velit dignissim sodales ut eu. Justo eget magna fermentum iaculis eu non diam phasellus. Fames ac turpis
                                    egestas integer eget aliquet nibh. In est ante in nibh mauris cursus mattis molestie. Nisl condimentum id
                                    venenatis a condimentum vitae sapien pellentesque. Nunc mattis enim ut tellus elementum sagittis. Amet nisl
                                    suscipit adipiscing bibendum. Nulla facilisi etiam dignissim diam quis enim. Auctor augue mauris augue neque
                                    gravida in. Nibh tellus molestie nunc non blandit massa enim nec. Condimentum id venenatis a condimentum vitae.
                                    Tortor id aliquet lectus proin. Amet justo donec enim diam. Enim sit amet venenatis urna cursus. Nulla pharetra
                                    diam sit amet nisl. Pellentesque pulvinar pellentesque habitant morbi. Sem integer vitae
                                    justo eget magna fermentum iaculis eu.
                                </p>
                                <div class="col-xs-12 m-t-sm p-l-none text-right">
                                    <span class="badge badge-pill"><a href="#">коментар</a></span>
                                    <span class="badge badge-pill"><a href="#">изтегляне</a></span>
                                </div>
                            </div>
                        </div>
                        <!-- IF there are commnets -->
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments p-lg">
                                @for ($i=0; $i<2; $i++)
                                    <div class="comment-box p-lg m-b-lg">
                                        <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                        <p class="comment-author p-b-xs">Име на профила</p>
                                        <p>
                                            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                                            sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation
                                            ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                        </p>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
