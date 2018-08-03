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
                {{ __('custom.greeting_invite') }} <?= $user ?>
                <br/>{{ __('custom.please_follow_link_register') }}
                <a href="<?= url('/registration?mail='. $mail) ?>">{{ __('custom.confirm') }}</a>
            </p>
        </td>
    </tr>
</table>
