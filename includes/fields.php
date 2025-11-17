<?php
if (!defined('ABSPATH')) exit;

return [
    'file' => [
        'label' => 'Upload računa',
        'type'  => 'file',
        'required' => false,
    ],
    'ime' => [
        'label' => 'Ime',
        'type'  => 'text',
        'required' => true,
    ],
    'email' => [
        'label' => 'Email',
        'type' => 'email',
        'required' => true,
    ],
    'broj_racuna' => [
        'label' => 'Broj računa',
        'type' => 'text',
        'required' => true,
    ],
    'prezime' => [
        'label' => 'Prezime',
        'type'  => 'text',
        'required' => true,
    ],
    'adresa' => [
        'label' => 'Adresa',
        'type'  => 'text',
        'required' => true,
    ],
    'kucni_broj' => [
        'label' => 'Kućni broj',
        'type'  => 'text',
        'required' => true,
    ],
    'mjesto' => [
        'label' => 'Mjesto',
        'type'  => 'text',
        'required' => true,
    ],
    'postanski_broj' => [
        'label' => 'Poštanski broj',
        'type'  => 'number',
        'attrs' => 'min="1" max="100000"',
        'required' => true,
    ],
    'drzava' => [
        'label' => 'Država',
        'type'  => 'select',
        'required' => true,
        'options' =>[
            'HR' => 'Hrvatska',
            'BA' => 'Bosna i Hercegovina',
            'RS' => 'Srbija',
            'ME' => 'Crna Gora',
            'SI' => 'Slovenija',
            'MK' => 'Sjeverna Makedonija',
            'DE' => 'Njemačka',
            'AT' => 'Austrija',
            'CH' => 'Švicarska',
            'IT' => 'Italija',
            'FR' => 'Francuska',
            'US' => 'Sjedinjene Američke Države',
            'GB' => 'Ujedinjeno Kraljevstvo',
        ]
    ],
    'kontakt_telefon' => [
        'label' => 'Kontakt telefon',
        'type'  => 'text',
        'required' => true,
    ],
];