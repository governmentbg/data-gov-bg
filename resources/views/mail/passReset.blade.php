<?php
// TO DO - IMPLEMENT MAIL DESIGN
?>

<table
    width="100%"
    height="100%"
    border="0"
    cellspacing="0"
    cellpadding="20"
    background="{{ asset('img/opendata-logo-large.png') }}"
>
    <tr>
        <td>
            <p>
                {{ __('custom.hello') .', '. $user }}
                <br/>{{ __('custom.reset_pass_info') }}
                <br/>{{ __('custom.reset_pass_link_info') }}<br/>
                <a href="{{ url('/password/reset?hash='. $hash .'&username='. $username) }}">
                    {{ url('/password/reset?hash='. $hash .'&username='. $username) }}
                </a>
            </p>
        </td>
    </tr>
</table>
