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
                Здравейте, <?= $user ?>
                <br/>Вие се регистрирахте успешно на ОПИНДАТЪТЪ
                <br/>За да активирате акаунта си, моля натиснете тук:<br/>
                <a href="<?= url('/confirmation?hash='. $hash) ?>">Потвърди</a>
            </p>
        </td>
    </tr>
</table>
