<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute трябва да бъде приет.',
    'active_url'           => ':attribute не е валиден URL.',
    'after'                => ':attribute трябва да бъде след :date.',
    'after_or_equal'       => ':attribute трябва да бъде дата след или равна на :date.',
    'alpha'                => ':attribute може да съдържа само букви.',
    'alpha_dash'           => ':attribute може да съдържа само букви, числа и тирета.',
    'alpha_num'            => ':attribute може да съдържа само букви и числа.',
    'array'                => ':attribute трябва да бъде масив.',
    'before'               => ':attribute трябва да бъде дата преди :date.',
    'before_or_equal'      => ':attribute трябва да бъде дата преди или равна на :date.',
    'between'              => [
        'numeric' => ':attribute трябва да бъде между :min и :max.',
        'file'    => ':attribute трябва да бъде между :min и :max килобайта.',
        'string'  => ':attribute трябва да бъде между :min и :max символа.',
        'array'   => ':attribute трябва да бъде между :min и :max елемента.',
    ],
    'boolean'              => 'Поле :attribute трябва да бъде true или false.',
    'confirmed'            => ':attribute потвърждението не съвпада.',
    'date'                 => ':attribute не е валидна дата.',
    'date_format'          => ':attribute не съвпада с формат :format.',
    'different'            => ':attribute и :other трябва да бъдат различни.',
    'digits'               => ':attribute трябва да бъде :digits цифри.',
    'digits_between'       => ':attribute трябва да бъде между цифрите :min и :max.',
    'dimensions'           => ':attribute е с невалидни размери.',
    'distinct'             => 'Поле :attribute има дублирана стойност.',
    'email'                => ':attribute трябва да бъде валиден имейл адрес.',
    'exists'               => 'Посоченият :attribute е невалиден.',
    'file'                 => ':attribute трябва да бъде файл.',
    'filled'               => 'Поле :attribute трябва да има стойност.',
    'image'                => ':attribute трябва да бъде картинка.',
    'in'                   => 'Посоченият :attribute е невалиден.',
    'in_array'             => 'Поле :attribute не съществува в :other.',
    'integer'              => ':attribute трябва да бъде цяло число.',
    'ip'                   => ':attribute трябва да бъде валиден IP адрес.',
    'ipv4'                 => ':attribute трябва да бъде валиден IPv4 адрес.',
    'ipv6'                 => ':attribute трябва да бъде валиден IPv6 адрес.',
    'json'                 => ':attribute трябва да бъде валиден JSON низ.',
    'max'                  => [
        'numeric' => ':attribute не може да бъде по голямо от :max.',
        'file'    => ':attribute не може да бъде повече от :max килобайта.',
        'string'  => ':attribute не може да бъде повече от :max символа.',
        'array'   => ':attribute не може да бъде повече от :max елемента.',
    ],
    'mimes'                => ':attribute трябва да бъде файлов тип: :values.',
    'mimetypes'            => ':attribute трябва да бъде файлов тип: :values.',
    'min'                  => [
        'numeric' => ':attribute трябва да бъде поне :min.',
        'file'    => ':attribute трябва да бъде поне :min килобайта.',
        'string'  => ':attribute трябва да бъде поне :min символа.',
        'array'   => ':attribute трябва да бъде поне :min елемента.',
    ],
    'not_in'               => 'Посоченият :attribute е невалиден.',
    'numeric'              => ':attribute трябва да бъде число.',
    'present'              => ':attribute трябва да фигурира.',
    'regex'                => 'Форматът на :attribute е невалиден.',
    'required'             => 'Поле :attribute е задължително.',
    'required_if'          => 'Поле :attribute е задължително когато :other е :value.',
    'required_unless'      => 'Поле :attribute е задължително освен ако :other е в :values.',
    'required_with'        => 'Поле :attribute е задължително когато :values е налично.',
    'required_with_all'    => 'Поле :attribute е задължително когато :values е налично.',
    'required_without'     => 'Поле :attribute е задължително когато :values не е налично.',
    'required_without_all' => 'Поле :attribute е задължително когато нито едно от :values е налично.',
    'same'                 => ':attribute и :other трябва да са еднакви.',
    'size'                 => [
        'numeric' => ':attribute трябва да е :size.',
        'file'    => ':attribute трябва да е :size килобайта.',
        'string'  => ':attribute трябва да е :size символа.',
        'array'   => ':attribute трябва да съдържа :size елемента.',
    ],
    'string'               => ':attribute трябва да бъде низ.',
    'timezone'             => ':attribute трябва да бъде валидна зона.',
    'unique'               => ':attribute е зает.',
    'uploaded'             => ':attribute не е качен.',
    'url'                  => 'Форматът на :attribute е невалиден.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'username' => 'потребителско име',
        'password' => 'парола',
        'firstname' => 'име',
        'lastname' => 'фамилия',
        'email' => 'имейл адрес',
        'password_confirm' => 'потвърди парола',
    ],

];
