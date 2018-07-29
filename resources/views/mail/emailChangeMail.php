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
                <br/>Вие сменихте електронният си адрес
                <br/>За да го потвърдите, моля натиснете тук:<br/>
                <a href="<?= url('/mailConfirmation?hash='. $hash .'&mail='. $mail) ?>">Потвърди</a>
            </p>
        </td>
    </tr>
</table>
