<div
    style="
        text-align: center;
        margin-right: 350px;
    "
>
    <a
        href="{{ url('/') }}"
        style="margin-right: 15px;"
    ><img src="{{ asset('img/op-logo.png') }}"></a>
    <a
        href="https://europa.eu/european-union/index_bg"
    ><img src="{{ asset('img/eu-logo.png') }}"></a>
    <a
        href="{{ url('/') }}"
        style="margin-left: 8px;"
    ><img src="{{ asset('img/opdu-logo.png') }}"></a>
    <br/>
</div>
<hr
    style="
        margin-left: 30%;
        margin-right: 30%;
        text-align: center;
    "
>
<table
    style="
        width: 100%;
        height: 700px;
        background: url({{ asset('img/watermark.png') }});
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
                <p>
                    @yield('content')
                </p>
            </td>
        </tr>
    </tbody>
</table>
