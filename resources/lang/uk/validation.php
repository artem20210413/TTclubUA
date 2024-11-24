<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Наступні мовні рядки містять стандартні повідомлення про помилки, які використовує
    | клас валідатора. Деякі з цих правил мають кілька таких версій
    | за правилами розміру. Ви можете налаштувати кожне з цих повідомлень тут.
    |
    */

    'accepted' => 'Поле :attribute повинно бути прийнято.',
    'accepted_if' => 'Поле :attribute повинно бути прийнято, коли :other дорівнює :value.',
    'active_url' => 'Поле :attribute повинно бути дійсним URL.',
    'after' => 'Поле :attribute повинно бути датою після :date.',
    'after_or_equal' => 'Поле :attribute повинно бути датою після або дорівнює :date.',
    'alpha' => 'Поле :attribute може містити лише літери.',
    'alpha_dash' => 'Поле :attribute може містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num' => 'Поле :attribute може містити лише літери та цифри.',
    'array' => 'Поле :attribute повинно бути масивом.',
    'ascii' => 'Поле :attribute може містити лише ASCII символи.',
    'before' => 'Поле :attribute повинно бути датою до :date.',
    'before_or_equal' => 'Поле :attribute повинно бути датою до або дорівнює :date.',
    'between' => [
        'array' => 'Поле :attribute повинно містити від :min до :max елементів.',
        'file' => 'Поле :attribute повинно бути від :min до :max кілобайт.',
        'numeric' => 'Поле :attribute повинно бути від :min до :max.',
        'string' => 'Поле :attribute повинно бути від :min до :max символів.',
    ],
    'boolean' => 'Поле :attribute повинно бути true або false.',
    'can' => 'Поле :attribute містить недопустиме значення.',
    'confirmed' => 'Поле :attribute не співпадає з підтвердженням.',
    'current_password' => 'Пароль невірний.',
    'date' => 'Поле :attribute повинно бути дійсною датою.',
    'date_equals' => 'Поле :attribute повинно бути датою, рівною :date.',
    'date_format' => 'Поле :attribute не відповідає формату :format.',
    'decimal' => 'Поле :attribute повинно мати :decimal десяткових знаків.',
    'declined' => 'Поле :attribute повинно бути відхилено.',
    'declined_if' => 'Поле :attribute повинно бути відхилено, коли :other дорівнює :value.',
    'different' => 'Поля :attribute та :other повинні бути різними.',
    'digits' => 'Поле :attribute повинно містити :digits цифр.',
    'digits_between' => 'Поле :attribute повинно містити від :min до :max цифр.',
    'dimensions' => 'Поле :attribute має неприпустимі розміри зображення.',
    'distinct' => 'Поле :attribute містить дубльоване значення.',
    'doesnt_end_with' => 'Поле :attribute повинно закінчуватися одним із значень: :values.',
    'doesnt_start_with' => 'Поле :attribute повинно починатися одним із значень: :values.',
    'email' => 'Поле :attribute повинно бути дійсною адресою електронної пошти.',
    'ends_with' => 'Поле :attribute повинно закінчуватися одним із значень: :values.',
    'enum' => 'Вибране значення :attribute є недопустимим.',
    'exists' => 'Вибране значення :attribute є недопустимим.',
    'extensions' => 'Поле :attribute повинно мати одне з наступних розширень: :values.',
    'file' => 'Поле :attribute повинно бути файлом.',
    'filled' => 'Поле :attribute повинно мати значення.',
    'gt' => [
        'array' => 'Поле :attribute повинно мати більше :value елементів.',
        'file' => 'Поле :attribute повинно бути більше :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути більше :value.',
        'string' => 'Поле :attribute повинно бути більше :value символів.',
    ],
    'gte' => [
        'array' => 'Поле :attribute повинно мати :value елементів або більше.',
        'file' => 'Поле :attribute повинно бути більше або дорівнює :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути більше або дорівнює :value.',
        'string' => 'Поле :attribute повинно бути більше або дорівнює :value символів.',
    ],
    'hex_color' => 'Поле :attribute повинно бути дійсним шестнадцятковим кольором.',
    'image' => 'Поле :attribute повинно бути зображенням.',
    'in' => 'Вибране значення :attribute є недопустимим.',
    'in_array' => 'Поле :attribute повинно існувати в :other.',
    'integer' => 'Поле :attribute повинно бути цілим числом.',
    'ip' => 'Поле :attribute повинно бути дійсною IP-адресою.',
    'ipv4' => 'Поле :attribute повинно бути дійсною IPv4-адресою.',
    'ipv6' => 'Поле :attribute повинно бути дійсною IPv6-адресою.',
    'json' => 'Поле :attribute повинно бути дійсною JSON-стрічкою.',
    'lowercase' => 'Поле :attribute повинно бути в нижньому регістрі.',
    'lt' => [
        'array' => 'Поле :attribute повинно мати менше :value елементів.',
        'file' => 'Поле :attribute повинно бути менше :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути менше :value.',
        'string' => 'Поле :attribute повинно бути менше :value символів.',
    ],
    'lte' => [
        'array' => 'Поле :attribute повинно мати не більше :value елементів.',
        'file' => 'Поле :attribute повинно бути менше або дорівнює :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути менше або дорівнює :value.',
        'string' => 'Поле :attribute повинно бути менше або дорівнює :value символів.',
    ],
    'mac_address' => 'Поле :attribute повинно бути дійсною MAC-адресою.',
    'max' => [
        'array' => 'Поле :attribute повинно мати не більше :max елементів.',
        'file' => 'Поле :attribute повинно бути менше або дорівнює :max кілобайт.',
        'numeric' => 'Поле :attribute повинно бути менше або дорівнює :max.',
        'string' => 'Поле :attribute повинно бути менше або дорівнює :max символів.',
    ],
    'max_digits' => 'Поле :attribute повинно мати не більше :max цифр.',
    'mimes' => 'Поле :attribute повинно бути файлом типу: :values.',
    'mimetypes' => 'Поле :attribute повинно бути файлом типу: :values.',
    'min' => [
        'array' => 'Поле :attribute повинно мати принаймні :min елементів.',
        'file' => 'Поле :attribute повинно бути принаймні :min кілобайт.',
        'numeric' => 'Поле :attribute повинно бути принаймні :min.',
        'string' => 'Поле :attribute повинно бути принаймні :min символів.',
    ],
    'min_digits' => 'Поле :attribute повинно мати принаймні :min цифр.',
    'missing' => 'Поле :attribute повинно бути відсутнім.',
    'missing_if' => 'Поле :attribute повинно бути відсутнім, коли :other дорівнює :value.',
    'missing_unless' => 'Поле :attribute повинно бути відсутнім, якщо :other не знаходиться серед :values.',
    'missing_with' => 'Поле :attribute повинно бути відсутнім, коли :values присутні.',
    'missing_with_all' => 'Поле :attribute повинно бути відсутнім, коли :values присутні.',
    'multiple_of' => 'Поле :attribute повинно бути кратним :value.',
    'not_in' => 'Вибране значення :attribute є недопустимим.',
    'not_regex' => 'Формат поля :attribute є недопустимим.',
    'numeric' => 'Поле :attribute повинно бути числом.',
    'password' => [
        'letters' => 'Поле :attribute повинно містити принаймні одну літеру.',
        'mixed' => 'Поле :attribute повинно містити принаймні одну велику та одну малу літеру.',
        'numbers' => 'Поле :attribute повинно містити принаймні одну цифру.',
        'symbols' => 'Поле :attribute повинно містити принаймні один символ.',
        'uncompromised' => 'Зазначене :attribute з\'явилося у витоку даних. Будь ласка, виберіть інше :attribute.',
    ],
    'present' => 'Поле :attribute повинно бути присутнім.',
    'present_if' => 'Поле :attribute повинно бути присутнім, коли :other є :value.',
    'present_unless' => 'Поле :attribute повинно бути присутнім, якщо :other не є :value.',
    'present_with' => 'Поле :attribute повинно бути присутнім, коли присутні :values.',
    'present_with_all' => 'Поле :attribute повинно бути присутнім, коли присутні всі :values.',
    'prohibited' => 'Поле :attribute заборонено.',
    'prohibited_if' => 'Поле :attribute заборонено, коли :other є :value.',
    'prohibited_unless' => 'Поле :attribute заборонено, якщо :other є в :values.',
    'prohibits' => 'Поле :attribute забороняє :other бути присутнім.',
    'regex' => 'Формат поля :attribute неправильний.',
    'required' => 'Поле :attribute обов’язкове для заповнення.',
    'required_array_keys' => 'Поле :attribute повинно містити записи для: :values.',
    'required_if' => 'Поле :attribute обов’язкове, коли :other є :value.',
    'required_if_accepted' => 'Поле :attribute обов’язкове, коли :other прийнято.',
    'required_unless' => 'Поле :attribute обов’язкове, якщо :other не є в :values.',
    'required_with' => 'Поле :attribute обов’язкове, коли присутні :values.',
    'required_with_all' => 'Поле :attribute обов’язкове, коли присутні всі :values.',
    'required_without' => 'Поле :attribute обов’язкове, коли :values відсутні.',
    'required_without_all' => 'Поле :attribute обов’язкове, коли відсутні всі :values.',
    'same' => 'Поле :attribute повинно співпадати з :other.',
    'size' => [
        'array' => 'Поле :attribute повинно містити :size елементів.',
        'file' => 'Поле :attribute повинно бути :size кілобайт.',
        'numeric' => 'Поле :attribute повинно бути :size.',
        'string' => 'Поле :attribute повинно бути :size символів.',
    ],
    'starts_with' => 'Поле :attribute повинно починатися одним із наступних: :values.',
    'string' => 'Поле :attribute повинно бути рядком.',
    'timezone' => 'Поле :attribute повинно бути дійсним часовим поясом.',
    'unique' => 'Такий :attribute вже існує.',
    'uploaded' => 'Не вдалося завантажити :attribute.',
    'uppercase' => 'Поле :attribute повинно бути великими літерами.',
    'url' => 'Поле :attribute повинно бути дійсним URL.',
    'ulid' => 'Поле :attribute повинно бути дійсним ULID.',
    'uuid' => 'Поле :attribute повинно бути дійсним UUID.',

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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
