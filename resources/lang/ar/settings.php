<?php

return [
    'navigation' => [
        'label' => 'الإعدادات',
        'group' => 'إعدادات الحزمة',
    ],

    'labels' => [
        'singular' => 'إعداد',
        'plural' => 'الإعدادات',
        'validation_rule_name' => 'اسم القاعدة',
        'validation_rule_value' => 'معلمات القاعدة',
        'add_validation_rule' => 'إضافة قاعدة تحقق'
    ],

    'form' => [
        'section' => [
            'general' => 'معلومات عامة',
            'validation' => 'قواعد التحقق'
        ],
    ],

    'fields' => [
        'module'      => 'الوحدة',
        'key'         => 'المفتاح',
        'type'        => 'النوع',
        'value'       => 'القيمة',
        'label'       => 'التسمية',
        'description' => 'الوصف',
        'order'       => 'الترتيب',
        'options'     => 'الخيارات',
        'is_active'   => 'نشط',
        'created_at'  => 'تاريخ الإنشاء',
        'updated_at'  => 'تاريخ التحديث',
        'validation_rules' => 'قواعد التحقق',
        'custom_validation_message' => 'رسالة تحقق مخصصة',
        'tenant'       => "المستأجر"
    ],

    'types' => [
        'text'     => 'نص',
        'textarea' => 'منطقة نصية',
        'number'   => 'رقم',
        'boolean'  => 'قيمة منطقية',
        'select'   => 'اختيار',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'حدد الخيارات المتاحة لحقول الاختيار',
        'validation_rules' => 'حدد قواعد التحقق في Laravel (مثل required, max:255, email, إلخ)',
        'validation_rules_examples' => 'أمثلة: required → (فارغ), max → 255, min → 5, email → (فارغ)',
    ],
];
