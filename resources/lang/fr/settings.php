<?php

return [
    'navigation' => [
        'label' => 'Paramètres',
        'group' => 'Paramètres du Paquet',
    ],

    'labels' => [
        'singular' => 'Paramètre',
        'plural' => 'Paramètres',
        'validation_rule_name' => 'Nom de la Règle',
        'validation_rule_value' => 'Paramètres de la Règle',
        'add_validation_rule' => 'Ajouter une règle de validation'
    ],

    'form' => [
        'section' => [
            'general' => 'Informations Générales',
            'validation' => 'Règles de Validation'
        ],
    ],

    'fields' => [
        'module'      => 'Module',
        'key'         => 'Clé',
        'type'        => 'Type',
        'value'       => 'Valeur',
        'label'       => 'Étiquette',
        'description' => 'Description',
        'order'       => 'Ordre',
        'options'     => 'Options',
        'is_active'   => 'Actif',
        'created_at'  => 'Créé Le',
        'updated_at'  => 'Mis à Jour Le',
        'validation_rules' => 'Règles de Validation',
        'custom_validation_message' => 'Message de Validation Personnalisé',
        'tenant'       => "Locataire"
    ],

    'types' => [
        'text'     => 'Texte',
        'textarea' => 'Zone de Texte',
        'number'   => 'Nombre',
        'boolean'  => 'Booléen',
        'select'   => 'Sélectionner',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'Définir les options disponibles pour les champs de sélection',
        'validation_rules' => 'Définir les règles de validation Laravel (ex: required, max:255, email, etc.)',
        'validation_rules_examples' => 'Exemples : required → (vide), max → 255, min → 5, email → (vide)',
    ],
    'actions' => [
        'submit' => 'Soumettre',
    ],
    'notifications' => [
        'saved' => 'Paramètres enregistrés avec succès',
    ]
];
