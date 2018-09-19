@extends('layouts.empty')

@section('content')
    @if ($resource->type == App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
        <a href="{{ $resource->resource_url }}">{{ $resource->resource_url }}</a>
    @else
        @if (empty($data))
            <div class="col-sm-12 m-t-lg text-center">{{ __('custom.no_info') }}</div>
        @else
            @if ($resource->format_code == App\Resource::FORMAT_CSV)
                <div class="m-b-lg overflow-x-auto js-show-on-load">
                    <table class="data-table">
                        <thead>
                            @foreach ($data as $index => $row)
                                @if ($index == 0)
                                    @foreach ($row as $key => $value)
                                        <th><p>{{ $value }}</p></th>
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
                        </tbody>
                    </table>
                </div>
            @elseif ($resource->format_code == App\Resource::FORMAT_XML)
                <textarea
                    class="js-xml-prev col-xs-12 m-b-md"
                    data-xml-data="{{ $data }}"
                    rows="20"
                ></textarea>
            @elseif ($resource->format_code == App\Resource::FORMAT_JSON && isset($data->text))
                <p>@php echo nl2br(e($data->text)) @endphp</p>
            @else
                <p>{{ uctrans('custom.resource_no_visualization') }}</p>
            @endif
        @endif
    @endif
@endsection