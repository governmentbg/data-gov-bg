<?php

return [
    'words' => [
        'cancel'  => 'Отмени',
        'delete'  => 'Изтрий',
        'edit'    => 'Редактирай',
        'yes'     => 'Да',
        'no'      => 'Не',
        'minutes' => '1 минута| :count minutes',
    ],

    'discussion' => [
        'new'          => 'Нова '.trans('chatter::intro.titles.discussion'),
        'all'          => 'Всички '.trans('chatter::intro.titles.discussions'),
        'create'       => 'Създай '.trans('chatter::intro.titles.discussion'),
        'posted_by'    => 'Публикувано от',
        'head_details' => 'Публикувано в категория',

    ],
    'response' => [
        'confirm'     => 'Сигурни ли сте, че искате да изтриете този отговор?',
        'yes_confirm' => 'Да, изтрий',
        'no_confirm'  => 'Не, благодаря',
        'submit'      => 'Изпрати отговор',
        'update'      => 'Редактирай отговор',
    ],

    'editor' => [
        'title'               => 'Заглавие на '.trans('chatter::intro.titles.discussion'),
        'select'              => 'Избери категория',
        'tinymce_placeholder' => 'Въведи съдържание на дискусията тук...',
        'select_color_text'   => 'Избери цвят за тази '.trans('chatter::intro.titles.discussion').' (незадължително)',
    ],

    'email' => [
        'notify' => 'Уведоми ме когато някой отговори',
    ],

    'auth' => 'Само автентификирани потребители могат да добавят отговори към дискусия!',

];
