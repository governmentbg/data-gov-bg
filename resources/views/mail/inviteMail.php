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
                Здравейте, получихте покана от <?= $user ?>
                <br/>Моля последвайте препратката, за да регистрирате профил
                <a href="<?= url('/registration?mail='. $mail) ?>">Потвърди</a>
            </p>
        </td>
    </tr>
</table>
