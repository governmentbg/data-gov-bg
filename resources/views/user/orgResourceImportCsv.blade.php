@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'organisation'])
    @if (isset($fromOrg) && !is_null($fromOrg))
        @include('partials.org-nav-bar', ['view' => 'datasets', 'organisation' => $fromOrg])
        <div class="row">
            <div class="org col-sm-3 col-xs-12 m-t-lg m-l-md">
                <div><img class="full-size" src="{{ $fromOrg->logo }}"></div>
                <h2 class="elipsis-1">{{ $fromOrg->name }}</h2>
                <h4>{{ truncate($fromOrg->descript, 150) }}</h4>
                <p class="text-right show-more">
                    <a href="{{ url('/admin/organisations/view/'. $fromOrg->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                </p>
            </div>
        </div>
    @endif
    <div class="col-xs-12 m-t-lg">
        <form
            class="form-horizontal"
            method="POST"
            @if (isset($fromOrg) && !is_null($fromOrg))
                action="{{ route('orgResourceCreate', ['uri' => $uri, 'orguri' => $fromOrg->uri]) }}"
            @else
                action="{{ route('orgResourceCreate', ['uri' => $uri]) }}"
            @endif
        >
            {{ csrf_field() }}

            <table class="table">
                @foreach ($csvData as $index => $row)
                    @if ($index == 0)
                        @foreach ($row as $key => $value)
                            <th>
                                <p>{{ $value }}</p>
                                <div class="js-check">
                                    <label>
                                        <input type="checkbox" name="keepcol[{{ $key }}]" checked>
                                    </label>
                                </div>
                            </th>
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

            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <input type="hidden" name="resource_uri" value="{{ $resourceUri }}">
                    <button name="ready_data" type="submit" class="m-l-md btn btn-primary">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
