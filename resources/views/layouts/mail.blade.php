<table
    style="
        width: 100%;
        height: 1000px;
        background-repeat: no-repeat;
        background-position: center center;
    "
>
    <tbody>
        <tr>
            <td
                style="
                    vertical-align: top;
                    padding-left: 30%;
                "
            >
                <a
                    href="{{ url('/') }}"
                    style="margin-right: 15px"
                ><img src="{{ $message->embed('img/op-logo.png') }}"></a>
                <a
                    href="https://europa.eu/european-union/index_bg"
                ><img src="{{ $message->embed('img/eu-logo.png') }}"></a>
                <a
                    href="{{ url('/') }}"
                    style="margin-left: 8px;"
                ><img src="{{ $message->embed('img/opdu-logo.png') }}"></a><br>
                <p
                    style="
                        font-family: 'Roboto', sans-serif;
                        font-size:16px;
                    "
                >
                    @yield('title')
                </p>
                <hr
                    style="
                        margin-top: 2em;
                        margin-right: 40%;
                        text-align: center;
                        margin-bottom: 2em;
                    "
                >
                <p
                    style="
                            font-family: 'Roboto', sans-serif;
                            font-size: 16px;
                    "
                >
                    @yield('content')
                </p><br>
                <div
                    style="
                        width: 100%;
                        height: 800px;
                        background: url({{ asset('img/watermark-logo.png') }});
                        background-repeat: no-repeat;
                        background-position: left top;
                        background-size: 60%;
                    "
                >
                </div>
            </td>
        </tr>
    </tbody>
</table>
