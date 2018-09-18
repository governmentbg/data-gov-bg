@extends('layouts.app')
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @if (Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'organisation'])
        @else
            @include('partials.user-nav-bar', ['view' => 'organisation'])
        @endif
        @if (isset($dataset))
            <div class="sidenav text-center">
                <div class="profile-name m-l-lg">{{ $dataset }}</div>
            </div>
        @endif
        @if (isset($fromOrg) && !is_null($fromOrg))
            @include('partials.org-nav-bar', ['view' => 'datasets', 'organisation' => $fromOrg])
        @endif
        @if (isset($resource->name))
            <div class="row">
                @if (isset($fromOrg) && !is_null($fromOrg))
                    @include('partials.org-info', ['organisation' => $fromOrg])
                @endif
                <div class="col-sm-9 col-xs-12">
                    <div class="articles m-t-lg">
                        <div class="article m-b-md">
                            <div>
                                <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none m-b-md">
                                    <ul class="p-l-none">
                                        <li>{{ __('custom.contact_support_name') }}:</li>
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
                                    @endif
                                </div>
                            </div>
                            @if (!empty($data))
                                <div class="{{ !empty($buttons['deleteResource']) ? 'col-xs-10' : 'col-xs-12' }} p-l-r-none text-right">
                                    <a
                                        class="badge badge-pill pull-right"
                                        href="{{ url('/resource/download/'. $resource->es_id. '/'. $resource->name) }}"
                                    >{{ __('custom.download') }}</a>
                                </div>
                            @endif
                            @if ($buttons['deleteResource'])
                                <div class="{{ !empty($data) ? 'col-xs-2' : 'col-xs-12' }} m-t-md p-l-r-none text-right">
                                    <form method="POST">
                                        {{ csrf_field() }}
                                        <button
                                            name="delete"
                                            class="badge badge-pill m-b-sm del-btn"
                                            data-confirm="{{ __('custom.remove_data') }}"
                                        >{{ uctrans('custom.remove') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-12 m-t-xl no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
