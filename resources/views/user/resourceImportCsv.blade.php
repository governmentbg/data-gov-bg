@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <p> {{ uctrans('custom.confirm_resource_import') }} </p>
        <form
            class="form-horizontal"
            method="POST"
            action="{{ url('/user/importCSV') }}"
        >
            {{ csrf_field() }}

            <div class="m-b-sm overflow-x-auto">
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
            </div>

            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <input type="hidden" name="resource_uri" value="{{ $resourceUri }}">
                    <button name="ready_data" type="submit" class="m-l-md btn btn-primary">{{ __('custom.save') }}</button>
                    <a
                        type="button"
                        href="{{ route('cancelImport', ['uri' => $resourceUri]) }}"
                        class="m-l-md btn btn-danger">{{ __('custom.cancel') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
