@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <p class="m-b-md">{{ uctrans('custom.confirm_resource_import') }}</p>
        @if (!empty($csvData))
            <form
                class="form-horizontal"
                method="POST"
                action="{{ url('importCSV') }}"
            >
                {{ csrf_field() }}

                <div class="m-b-lg overflow-x-auto js-show-on-load">
                    <table class="data-table">
                        @foreach ($csvData as $index => $row)
                            @if ($index == 0)
                                <thead>
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
                                </thead>
                                <tbody>
                            @else
                                <tr>
                                    @foreach ($row as $key => $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                        <tbody>
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
        @elseif (!empty($xmlData))
            <form
                class="form-horizontal"
                method="POST"
                action="{{ url('importElastic') }}"
            >
                {{ csrf_field() }}
                <textarea class="js-xml-prev col-xs-12 m-b-md" data-xml-data="{{ $xmlData }}" rows="20"></textarea>
                <div class="form-group row">
                    <div class="col-sm-12 text-right m-b-sm">
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
        @else
            <form
                class="form-horizontal m-t-md"
                method="POST"
                action="{{ url('importElastic') }}"
            >
                {{ csrf_field() }}
                <p>@php echo isset($text) ? nl2br(e($text)) : uctrans('custom.resource_no_visualization') @endphp</p>
                <div class="form-group row">
                    <div class="col-sm-12 text-right m-b-sm">
                        <input type="hidden" name="resource_uri" value="{{ $resourceUri }}">
                        <button
                            name="ready_data"
                            type="submit"
                            class="m-l-md btn btn-primary"
                        >{{ __('custom.save') }}</button>
                        <a
                            type="button"
                            href="{{ route('cancelImport', ['uri' => $resourceUri]) }}"
                            class="m-l-md btn btn-danger">{{ __('custom.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
