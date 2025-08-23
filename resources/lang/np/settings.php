<?php

return [
    'navigation' => [
        'label' => 'सेटिङ्स',
        'group' => 'प्याकेज सेटिङ्स',
    ],

    'labels' => [
        'singular' => 'सेटिङ',
        'plural' => 'सेटिङ्स',
        'validation_rule_name' => 'नियमको नाम',
        'validation_rule_value' => 'नियमका प्यारामिटर',
        'add_validation_rule' => 'भ्यालिडेसन नियम थप्नुहोस्'
    ],

    'form' => [
        'section' => [
            'general' => 'सामान्य जानकारी',
            'validation' => 'भ्यालिडेसन नियमहरू'
        ],
    ],

    'fields' => [
        'module'      => 'मोड्युल',
        'key'         => 'कुञ्जी',
        'type'        => 'प्रकार',
        'value'       => 'मान',
        'label'       => 'लेबल',
        'description' => 'विवरण',
        'order'       => 'क्रम',
        'options'     => 'विकल्पहरू',
        'is_active'   => 'सक्रिय',
        'created_at'  => 'सिर्जना मिति',
        'updated_at'  => 'अपडेट मिति',
        'validation_rules' => 'भ्यालिडेसन नियमहरू',
        'custom_validation_message' => 'कस्टम भ्यालिडेसन सन्देश',
        'tenant'       => "भाडामा लिने"
    ],

    'types' => [
        'text'     => 'पाठ',
        'textarea' => 'पाठ क्षेत्र',
        'number'   => 'संख्या',
        'boolean'  => 'बूलियन',
        'select'   => 'चयन',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'चयन फिल्डका लागि उपलब्ध विकल्पहरू निर्धारण गर्नुहोस्',
        'validation_rules' => 'Laravel भ्यालिडेसन नियमहरू निर्धारण गर्नुहोस् (जस्तै required, max:255, email आदि)',
        'validation_rules_examples' => 'उदाहरणहरू: required → (खाली), max → 255, min → 5, email → (खाली)',
    ],
    'actions' => [
        'submit' => 'पेश गर्नुहोस्',
    ],
    'notifications' => [
        'saved' => 'सेटिङहरू सफलतापूर्वक सुरक्षित गरियो',
    ]
];
