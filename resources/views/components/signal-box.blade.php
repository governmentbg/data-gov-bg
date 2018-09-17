<div class="col-md-12">
    @foreach ($signals as $signal)
        <div class="row signal-box">
            <div class="col-md-1 col-sm-4"></div>
            <div class="col-md-10 col-sm-8">
                <p class=" m-t">
                    <strong>{{ utrans('custom.resource') }}:</strong>
                    <a class="h3"
                        href="{{ url('/user/resource/view/'. $signal->resource_uri) }}">
                        {{ $signal->resource_name }}
                    </a>
                </p>
                <p>
                    <strong>{{ utrans('custom.date') }}:</strong>
                    &nbsp;{{ $signal->created_at }}
                </p>
                <p>
                    <strong>{{ utrans('custom.name') }}:</strong>
                    &nbsp;{{ $signal->firstname .' '. $signal->lastname }}
                </p>
                <p>
                    <strong>{{ utrans('custom.email') }}:</strong>
                    &nbsp;{{ $signal->email }}
                </p>
                <p>
                    <strong>{{ utrans('custom.description') }}:</strong>
                    &nbsp;{{ $signal->description }}
                </p>
                <form method="POST" action="{{ url('/signal/remove') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="signal" value="{{ $signal->id }}">
                    <button
                        class="btn del-btn btn-primary m-b"
                        type="submit"
                        name="remove_signal"
                    >{{ uctrans('custom.signal_remove') }}</button>
                </form>
            </div>
        </div>
    @endforeach
</div>