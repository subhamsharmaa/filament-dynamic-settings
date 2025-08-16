<?php

return [
    'navigation' => [
        'label' => 'Settings',
        'group' => 'Package Settings',
    ],

    'labels' => [
        'singular' => 'Setting',
        'plural' => 'Settings',
        'validation_rule_name' => 'Rule Name',
        'validation_rule_value' => 'Rule Parameters',
        'add_validation_rule' => 'Add validation rule'
    ],

    'form' => [
        'section' => [
            'general' => 'General Information',
            'validation' => 'Validation Rules'
        ],
    ],

    'fields' => [
        'module'      => 'Module',
        'key'         => 'Key',
        'type'        => 'Type',
        'value'       => 'Value',
        'label'       => 'Label',
        'description' => 'Description',
        'order'       => 'Order',
        'options'     => 'Options',
        'is_active'   => 'Active',
        'created_at'  => 'Created At',
        'updated_at'  => 'Updated At',
        'validation_rules' => 'Validation Rules',
        'custom_validation_message' => 'Custom Validation Message',
        'tenant'       => "Tenant"
    ],

    'types' => [
        'text'     => 'Text',
        'textarea' => 'Textarea',
        'number'   => 'Number',
        'boolean'  => 'Boolean',
        'select'   => 'Select',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'Define the available options for select fields',
        'validation_rules' => 'Define Laravel validation rules (e.g., required, max:255, email, etc.)',
        'validation_rules_examples' => 'Examples: required → (leave empty), max → 255, min → 5, email → (leave empty)',
    ],
];
