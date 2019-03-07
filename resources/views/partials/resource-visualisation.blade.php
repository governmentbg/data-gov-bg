@php
    $format = empty($resource) ? false : $resource->format_code;
    $type = empty($resource) ? App\Resource::TYPE_FILE : $resource->type;
    $perPageArr = [10, 25, 50, 100];
@endphp
@if ($type == App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
    <a href="{{ $resource->resource_url }}">{{ $resource->resource_url }}</a>
@else
    @if (empty($data))
        <div class="col-sm-12 m-t-lg text-center">{{ __('custom.no_info') }}</div>
    @elseif (is_array($data) || is_object($data))
        @if ($format == App\Resource::FORMAT_CSV)
            <div class="m-b-lg overflow-x-auto js-show-on-load">
                @if (isset($resPagination))
                    <div class="row m-b-lg">
                        <form method="GET">
                            <div class="col-xs-6">
                                @if (isset(app('request')['q']))
                                    <input name="q" type="hidden" value="{{ app('request')['q'] }}">
                                @endif
                                @if (isset(app('request')['rpage']))
                                    <input name="rpage" type="hidden" value="{{ app('request')['rpage'] }}">
                                @endif
                                <select class="js-records-per-page" name="per_page">
                                    @foreach ($perPageArr as $opt)
                                        <option
                                            value="{{ $opt }}"
                                            {{
                                                !isset(app('request')['per_page'])
                                                && isset($dataPerPage)
                                                && $opt == $dataPerPage
                                                    ? 'selected'
                                                    : ''
                                            }}
                                            {{
                                                isset(app('request')['per_page'])
                                                && app('request')['per_page'] == $opt
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >{{ $opt }}</option>
                                    @endforeach
                                </select>
                                {{ __('custom.rec_per_page') }}
                            </div>
                        </form>
                        <form method="GET">
                            <div class="col-xs-6">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    placeholder="{{ __('custom.search') }}.."
                                    value="{{ isset($search) ? $search : '' }}"
                                    name="q"
                                >
                            </div>
                            @if (isset(app('request')['rpage']))
                                <input name="rpage" type="hidden" value="{{ app('request')['rpage'] }}">
                            @endif
                            @if (isset(app('request')['per_page']))
                                <input name="per_page" type="hidden" value="{{ app('request')['per_page'] }}">
                            @endif
                        </form>
                    </div>
                @endif
                <table class="data-table <?= isset($resPagination) ? 'paging-off' : '' ?>">
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
                                    @if (count($data[0]) > count($row))
                                        @for ($x = count($data[0]) - count($row); $x >= 0; $x--)
                                            <td></td>
                                        @endfor
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                @include('partials.resource-pagination')
            </div>
        @elseif ($format == App\Resource::FORMAT_XML || $format == App\Resource::FORMAT_RDF)
            <textarea
                class="js-xml-prev col-xs-12 m-b-md"
                data-xml-data="{{ $data }}"
                rows="20"
            ></textarea>
        @elseif ($format == App\Resource::FORMAT_JSON && isset($data->text))
            <p>@php echo nl2br(e($data->text)) @endphp</p>
        @elseif ($format == App\Resource::FORMAT_XSD)
            @foreach ($data as $row)
                <p>{{ $row }}</p>
            @endforeach
        @else
            <div class="data-preview">{{ json_encode($data, JSON_PRETTY_PRINT) }}</div>
        @endif
        @if (!config('app.IS_TOOL'))
            <form method="POST" action="{{ url('/resource/download') }}">
                {{ csrf_field() }}
                <input
                    hidden
                    name="resource"
                    type="text"
                    value="{{ $resource->id }}"
                >
                <input
                    hidden
                    name="version"
                    type="text"
                    value="{{ $versionView }}"
                >
                <input
                    hidden
                    name="name"
                    type="text"
                    value="{{ $resource->name }}"
                >
                <div class="form-group row">
                    <label
                        for="format"
                        class="col-sm-3 col-xs-12 col-form-label"
                    >{{ uctrans('custom.format') }}:</label>
                    <div class="col-sm-9">
                        <select
                            id="format"
                            name="format"
                            class="js-select form-control"
                        >
                            @foreach ($formats as $id => $format)
                                <option
                                    value="{{ $format }}"
                                    {{ $format == $resource->file_format ? 'selected' : '' }}
                                >{{ $format }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row text-right download-btns">
                    <button
                        name="download"
                        type="submit"
                        class="btn btn-primary js-ga-event"
                        data-ga-action="download"
                        data-ga-label="resource download"
                        data-ga-category="data"
                    >{{ uctrans('custom.download') }}</button>
                </div>
            </form>
        @endif
    @else
        <div class="data-preview">{!! nl2br($data) !!}</div>
    @endif
@endif
