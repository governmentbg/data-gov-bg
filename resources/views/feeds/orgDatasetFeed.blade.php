
<rss version="2.0">
    @if (!empty($history))
        <channel>
            @foreach ($history as $singleItem)
                <item>
                    @foreach ($datasetsList as $singleDataset)
                        @if ($singleDataset->id == $singleItem->action_object)
                            <title>{{ $singleItem->action_msg }}</title>
                            <itemName>{{ $singleDataset->name }}</itemName>
                            <link>{{ url('data/view/' . $singleDataset->uri) }}</link>
                        @endif
                    @endforeach
                    <description>{{ $singleItem->action_msg . ' ' . $singleDataset->name}}</description>
                    <moment>{{ $singleItem->occurrence }}</moment>
                    <guid>{{ $singleItem->id }}</guid>
                </item>
            @endforeach
        </channel>
    @endif
</rss>


