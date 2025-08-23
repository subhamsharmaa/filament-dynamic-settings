<?php

return [
    'navigation' => [
        'label' => 'सेटिंग्स',
        'group' => 'पैकेज सेटिंग्स',
    ],

    'labels' => [
        'singular' => 'सेटिंग',
        'plural' => 'सेटिंग्स',
        'validation_rule_name' => 'नियम का नाम',
        'validation_rule_value' => 'नियम पैरामीटर',
        'add_validation_rule' => 'मान्यकरण नियम जोड़ें'
    ],

    'form' => [
        'section' => [
            'general' => 'सामान्य जानकारी',
            'validation' => 'मान्यकरण नियम'
        ],
    ],

    'fields' => [
        'module'      => 'मॉड्यूल',
        'key'         => 'कुंजी',
        'type'        => 'प्रकार',
        'value'       => 'मान',
        'label'       => 'लेबल',
        'description' => 'विवरण',
        'order'       => 'क्रम',
        'options'     => 'विकल्प',
        'is_active'   => 'सक्रिय',
        'created_at'  => 'निर्मित तिथि',
        'updated_at'  => 'अपडेट तिथि',
        'validation_rules' => 'मान्यकरण नियम',
        'custom_validation_message' => 'कस्टम मान्यकरण संदेश',
        'tenant'       => "किरायेदार"
    ],

    'types' => [
        'text'     => 'टेक्स्ट',
        'textarea' => 'टेक्स्ट क्षेत्र',
        'number'   => 'संख्या',
        'boolean'  => 'बूलियन',
        'select'   => 'चयन',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'चयन फ़ील्ड्स के लिए उपलब्ध विकल्प निर्धारित करें',
        'validation_rules' => 'Laravel मान्यकरण नियम निर्धारित करें (जैसे required, max:255, email आदि)',
        'validation_rules_examples' => 'उदाहरण: required → (खाली), max → 255, min → 5, email → (खाली)',
    ],
    'actions' => [
        'submit' => 'सबमिट करें',
    ],
    'notifications' => [
        'saved' => 'सेटिंग्स सफलतापूर्वक सहेज दी गईं',
    ]
];
