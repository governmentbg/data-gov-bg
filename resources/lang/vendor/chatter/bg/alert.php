<?php

return [
    'success' => [
        'title'  => 'Много добре!',
        'reason' => [
            'submitted_to_post'       => 'Отговорът бе успешно изпратен до '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
            'updated_post'            => 'Успешно обновяване на '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
            'destroy_post'            => 'Успешно изтриване на отговор и '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
            'destroy_from_discussion' => 'Успешно изтриване на отговор от '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
            'created_discussion'      => 'Успешно създаване на нова '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
        ],
    ],
    'info' => [
        'title' => 'Внимание!',
    ],
    'warning' => [
        'title' => 'Ох!',
    ],
    'danger'  => [
        'title'  => 'Упс!',
        'reason' => [
            'errors'            => 'Моля коригирайте следните грешки:',
            'prevent_spam'      => 'За да предотвратите спам, моля, позволете поне 1 минута между изпращане на съдържанията.',
            'trouble'           => 'За съжаление възникна проблем при изпращане на отговора ви.',
            'update_post'       => 'За съжаление възникна проблем при актуализиране на отговора..',
            'destroy_post'      => 'За съжаление възникна проблем при изтриване на отговора..',
            'create_discussion' => 'Упс :( Изглежда има проблем при създаването на вашата '.mb_strtolower(trans('chatter::intro.titles.discussion')).'.',
        	'title_required'    => 'Моля въведете заглавие',
        	'title_min'		    => 'Заглавието трябва да съдържа поне :min знака.',
        	'title_max'		    => 'Заглавието трябва да съдържа не повече от :max знака.',
        	'content_required'  => 'Моля въведете съдържание',
        	'content_min'  		=> 'Съдържанието трябва да съдържа поне :min знака',
        	'category_required' => 'Моля изберете категория',
        ],
    ],
];
