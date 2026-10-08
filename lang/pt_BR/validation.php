<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Regras sem tradução aqui usam o idioma de fallback (APP_FALLBACK_LOCALE).
    | Mantenha os textos alinhados com resources/js/i18n/locales/pt-BR.json.
    |
    */

    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'max' => [
        'string' => 'O campo :attribute não pode ser superior a :max caracteres.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este :attribute já está em uso.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'password_confirmation' => [
            'same' => 'As senhas não conferem.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'e-mail',
        'name' => 'nome',
        'password' => 'senha',
        'password_confirmation' => 'confirmação de senha',
    ],

];
