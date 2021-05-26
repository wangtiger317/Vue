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

    'accepted' => 'O :attribute é aceite.',
    'active_url' => 'O :attribute não é um URL válido.',
    'after' => 'O :attribute deve ser uma data depois de :date.',
    'after_or_equal' => 'O :attribute deve ser uma data depois ou igual a :date.',
    'alpha' => 'O :attribute só pode conter letras.',
    'alpha_dash' => 'O :attribute só pode conter letras, números, traços e underscores.',
    'alpha_num' => 'O :attribute só pode conter letras e números.',
    'array' => 'O :attribute tem de ter uma opção.',
    'before' => 'O :attribute deve ser uma data antes de :date.',
    'before_or_equal' => 'O :attribute deve ser uma data antes ou igual a :date.',
    'between' => [
        'numeric' => 'O :attribute deve ter entre :min e :max.',
        'file' => 'O :attribute deve ter entre :min e :max kilobytes.',
        'string' => 'O :attribute deve ter entre :min e :max caracteres.',
        'array' => 'O :attribute deve ter entre :min e :max itens.',
    ],
    'boolean' => 'O campo :attribute tem de ser verdadeiro ou falso.',
    'confirmed' => 'O :attribute confirmação não corresponde.',
    'date' => 'O :attribute não é uma data válida.',
    'date_equals' => 'O :attribute deve ser uma data igual a :date.',
    'date_format' => 'O :attribute não corresponde ao formato :format.',
    'different' => 'O :attribute e :other têm de ser diferentes.',
    'digits' => 'O :attribute deve ser :digits digítos.',
    'digits_between' => 'O :attribute deve ter entre :min e :max digítos.',
    'dimensions' => 'O :attribute tem dimensões da imagem inválidas.',
    'distinct' => 'O campo :attribute tem um valor duplicado.',
    'email' => 'O :attribute deve ser um endereço de e-mail válido.',
    'exists' => 'O selecionado :attribute é inválido.',
    'file' => 'O :attribute tem de ser um ficheiro.',
    'filled' => 'O campo :attribute tem de estar preenchido.',
    'gt' => [
        'numeric' => 'O :attribute tem de ser maior do que :value.',
        'file' => 'O :attribute tem de ter mais do que :value kilobytes.',
        'string' => 'O :attribute tem de ter mais do que :value caracteres.',
        'array' => 'O :attribute tem de ter mais do que :value itens.',
    ],
    'gte' => [
        'numeric' => 'O :attribute tem de ser maior ou igual a :value.',
        'file' => 'O :attribute tem de ter mais ou ser igual a :value kilobytes.',
        'string' => 'O :attribute tem de ter mais ou ser igual a :value caracteres.',
        'array' => 'O :attribute deverá ter :value itens ou mais.',
    ],
    'image' => 'O :attribute deve ser uma imagem.',
    'in' => 'O :attribute selecionado é inválido.',
    'in_array' => 'O campo :attribute não existe em :other.',
    'integer' => 'O :attribute deve ser um número inteiro.',
    'ip' => 'O :attribute tem de ser um IP válido.',
    'ipv4' => 'O :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O :attribute deve ser uma JSON string válida.',
    'lt' => [
        'numeric' => 'O :attribute deve ser menor do que :value.',
        'file' => 'O :attribute deve ter menos do que :value kilobytes.',
        'string' => 'O :attribute deve ter menos de :value caracteres.',
        'array' => 'O :attribute deve ter menos de :value itens.',
    ],
    'lte' => [
        'numeric' => 'O :attribute deve ser menor ou igual a :value.',
        'file' => 'O :attribute deve ter menos ou igual a :value kilobytes.',
        'string' => 'O :attribute deve ter menos ou igual a :value caracteres.',
        'array' => 'O :attribute não pode ter mais do que :value itens.',
    ],
    'max' => [
        'numeric' => 'O :attribute não pode ser superior a :max.',
        'file' => 'O :attribute não pode ter mais de :max kilobytes.',
        'string' => 'O :attribute não pode ter mais de :max caracteres.',
        'array' => 'O :attribute não pode ter mais de :max itens.',
    ],
    'mimes' => 'O :attribute tem de ser um ficheiro do tipo: :values.',
    'mimetypes' => 'O :attribute tem de ser um ficheiro do tipo: :values.',
    'min' => [
        'numeric' => 'O :attribute tem de ter pelo menos :min.',
        'file' => 'O :attribute tem de ter pelo menos :min kilobytes.',
        'string' => 'O :attribute tem de ter no mínimo :min caracteres.',
        'array' => 'O :attribute tem de ter no mínimo :min itens.',
    ],
    'not_in' => 'O :attribute selecionado é inválido.',
    'not_regex' => 'O formato :attribute é inválido.',
    'numeric' => 'O :attribute deve ser um número.',
    'present' => 'O campo :attribute tem de estar presente.',
    'regex' => 'O formato :attribute é inválido.',
    'required' => 'O campo :attribute é de preenchimento obrigatório.',
    'required_if' => 'O campo :attribute é necessário quando :other é :value.',
    'required_unless' => 'O campo :attribute é necessário a não ser que :other esteja em :values.',
    'required_with' => 'O campo :attribute é necessário quando :values está presente.',
    'required_with_all' => 'O campo :attribute é necessário quando :values está presente.',
    'required_without' => 'O campo :attribute é necessário quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é necessário quando nenhum de :values estão presentes.',
    'same' => 'O :attribute e :other deverão corresponder.',
    'size' => [
        'numeric' => 'O :attribute deve ter :size.',
        'file' => 'O :attribute deve ter :size kilobytes.',
        'string' => 'O :attribute deve ter :size caracteres.',
        'array' => 'O :attribute deve conter :size itens.',
    ],
    'starts_with' => 'O :attribute deve começar por um dos seguintes: :values',
    'string' => 'O :attribute deve ser uma palavra.',
    'timezone' => 'O :attribute deve ser uma zona válida.',
    'unique' => 'Este :attribute já está associado a outra conta.',
    'uploaded' => 'O :attribute falhou no upload.',
    'url' => 'O formato de :attribute é inválido.',
    'uuid' => 'O :attribute deve ser um UUID válido.',

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
