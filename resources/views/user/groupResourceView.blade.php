@extends('layouts.app')
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @if (Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'group'])
        @else
            @include('partials.user-nav-bar', ['view' => 'group'])
        @endif
        @include('partials.group-nav-bar', ['view' => 'datasets', 'group' => $group])
        @if (isset($resource->name))
            <div class="row m-t-md">
                <div class="col-sm-3 col-xs-12 sidenav">
                    @include('partials.group-info', ['group' => $group])
                </div>
                <div class="col-sm-9 col-xs-12">
                    <div class="articles">
                        <div class="article m-b-md">
                            <div>
                                <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none m-b-md">
                                    <ul class="p-l-none">
                                        <li>{{ uctrans('custom.dataset') }}: {{ isset($dataset) ? $dataset : '' }}</li>
                                        <li>{{ __('custom.contact_support_name') }}: {{ isset($supportName) ? $supportName : '' }}</li>
                                        <li>{{ utrans('custom.version') }}:&nbsp;{{ $resource->version }}</li>
                                        <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                                        <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                                        <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                                        <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-12 p-l-none">
                                <h2>{{ $resource->name }}</h2>
                                <p>{{ $resource->description }}</p>
                                <div class="col-sm-12 p-l-none">
                                    <div class="tags pull-left">
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
                                           href="{{ url('/resource/download/'. $resource->es_id. '/'. $resource->name) }}"
                                        >{{ uctrans('custom.download') }}</a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 m-t-md p-l-r-none text-right">
                                @if ($buttons['editResource'])
                                    <a
                                        class="badge badge-pill m-b-sm m-r-sm"
                                        href="{{ url(\Auth::user()->is_admin
                                            ? '/admin/resource/edit/'. $resource->uri .'/'. $group->uri
                                            : '/user/resource/edit/'. $resource->uri .'/'. $group->uri) }}"
                                    >{{ uctrans('custom.edit') }}</a>
                                @endif
                                @if ($buttons['delResource'])
                                        <form method="POST" class="inline-block">
                                            {{ csrf_field() }}
                                            <button
                                                name="delete"
                                                class="badge badge-pill m-b-sm del-btn"
                                                data-confirm="{{ __('Изтриване на данните?') }}"
                                            >{{ uctrans('custom.remove') }}</button>
                                        </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-12 m-t-xl text-center no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
