<?php
// TO DO - IMPLEMENT MAIL DESIGN
?>

<table
    width="100%"
    height="100%"
    border="0"
    cellspacing="0"
    cellpadding="20"
    background="<?= asset('img/opendata-logo-large.png') ?>"
>
    <tr>
        <td>
            <p>
            {{ __('custom.greetings') }}, <?= $user ?>
                <br/>{{ __('custom.register_success') }}
                <br/>{{ __('custom.to_activate') }}:<br/>
                <a href="<?= url('/confirmation?hash='. $hash) ?>">{{ __('custom.confirm') }}</a>
            </p>
        </td>
    </tr>
</table>
