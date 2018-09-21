<?php

return [
    'preheader'       => 'Някой отговори на публикация във форум.',
    'greeting'        => 'Здравей,',
    'body'            => 'Някой отговори на публикация във форум в ',
    'view_discussion' => 'Виж '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
    'farewell'        => 'Приятен ден!',
    'unsuscribe'      => [
        'message' => 'Ако вече не искате да бъдете уведомявани, когато някой отговори на този формуляр, не забравяйте да премахнете отметката от настройката за известия в долната част на страницата :)',
        'action'  => 'Не харесвате тези имейли?',
        'link'    => 'Отпишете се за тази '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
    ],
];
