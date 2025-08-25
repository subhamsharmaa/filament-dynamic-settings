<?php

return [
    'navigation' => [
        'label' => 'Configuraciones',
        'group' => 'Configuraciones del Paquete',
    ],

    'labels' => [
        'singular' => 'Configuración',
        'plural' => 'Configuraciones',
        'validation_rule_name' => 'Nombre de Regla',
        'validation_rule_value' => 'Parámetros de Regla',
        'add_validation_rule' => 'Agregar regla de validación'
    ],

    'form' => [
        'section' => [
            'general' => 'Información General',
            'validation' => 'Reglas de Validación'
        ],
    ],

    'fields' => [
        'module'      => 'Módulo',
        'key'         => 'Clave',
        'type'        => 'Tipo',
        'value'       => 'Valor',
        'label'       => 'Etiqueta',
        'description' => 'Descripción',
        'order'       => 'Orden',
        'options'     => 'Opciones',
        'is_active'   => 'Activo',
        'created_at'  => 'Creado En',
        'updated_at'  => 'Actualizado En',
        'validation_rules' => 'Reglas de Validación',
        'custom_validation_message' => 'Mensaje de Validación Personalizado',
        'tenant'       => "Inquilino"
    ],

    'types' => [
        'text'     => 'Texto',
        'textarea' => 'Área de Texto',
        'number'   => 'Número',
        'boolean'  => 'Booleano',
        'select'   => 'Seleccionar',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'Defina las opciones disponibles para los campos de selección',
        'validation_rules' => 'Defina las reglas de validación de Laravel (ej., required, max:255, email, etc.)',
        'validation_rules_examples' => 'Ejemplos: required → (vacío), max → 255, min → 5, email → (vacío)',
    ],
    'actions' => [
        'submit' => 'Enviar',
    ],
    'notifications' => [
        'saved' => 'Configuraciones guardadas correctamente',
    ]
];
