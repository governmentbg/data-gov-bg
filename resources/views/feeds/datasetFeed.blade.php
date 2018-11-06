<rss version="2.0">
    @if (!empty($history))
        <channel>
            <title>data.egov.bg</title>
            <description>data.egov.bg</description>
            <link>{{ url('data') }}</link>
            @foreach ($history as $item)
                <item>
                    @if ($item->module_name == App\Module::getModuleName(App\Module::DATA_SETS))
                    <dataset id="{{ $item->dataset_id }}">
                        <title>{{ $item->action_msg .' - '. $translation[(int)$item->dataset_name] }}</title>
                        <itemName>{{ $translation[(int)$item->dataset_name] }}</itemName>
                        @if (is_null($item->dataset_deleted))
                            <link>{{ url('data/view/'. $item->dataset_uri) }}</link>
                        @else
                            <link>{{ url('data') }}</link>
                        @endif
                        <description>{{ $item->action_msg .' - '. $translation[(int)$item->dataset_name] .' - '. $translation[(int)$item->dataset_descript] }}</description>
                        <moment>{{ $item->occurrence }}</moment>
                        <guid>{{ $item->ahId }}</guid>
                    </dataset>
                    @elseif ($item->module_name == App\Module::getModuleName(App\Module::RESOURCES) && !empty($item->resource_name))
                        <resource id="{{ $item->resource_id }}">
                            <title>{{ $item->action_msg .' - '. $translation[$item->resource_name] }}</title>
                            <itemName>{{ $translation[$item->resource_name] }}</itemName>
                            @if (is_null($item->resource_deleted))
                                <link>{{ url('data/resourceView/'. $item->resource_uri) }}</link>
                            @else
                                <link>{{ url('data') }}</link>
                            @endif
                            <description>{{ $item->action_msg .' - '. $translation[$item->resource_name] .' - '. $translation[$item->resource_descript] }}</description>
                            <moment>{{ $item->occurrence }}</moment>
                            <guid>{{ $item->ahId }}</guid>
                        </resource>
                    @endif
                </item>
            @endforeach
        </channel>
    @endif
</rss>
