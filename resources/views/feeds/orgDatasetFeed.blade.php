<rss version="2.0">
    @if (!empty($history))
        <channel>
            @foreach ($history as $singleDataset)
                <item>
                    <dataset id="{{ $singleDataset->id }}">
                        <title>{{ $singleDataset->name . ' ' . $singleDataset->action_msg . ' ' . $singleDataset->descript }}</title>
                        <itemName>{{ $singleDataset->name }}</itemName>
                        @if (is_null($singleDataset->deleted_at))
                            <link>{{ url('data/view/' . $singleDataset->uri) }}</link>
                        @else
                            <link>{{ url('organisation/' . $organisation->uri . '/datasets') }}</link>
                        @endif
                        <description>{{ $singleDataset->name . ' ' . $singleDataset->action_msg }}</description>
                        <moment>{{ $singleDataset->occurrence }}</moment>
                        <guid>{{ $singleDataset->ahId }}</guid>
                    </dataset>
                </item>
            @endforeach
        </channel>
    @endif
</rss>
