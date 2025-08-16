<?php

return [
    'navigation' => [
        'label' => 'Einstellungen',
        'group' => 'Paket-Einstellungen',
    ],

    'labels' => [
        'singular' => 'Einstellung',
        'plural' => 'Einstellungen',
        'validation_rule_name' => 'Regelname',
        'validation_rule_value' => 'Regelparameter',
        'add_validation_rule' => 'Validierungsregel hinzufügen'
    ],

    'form' => [
        'section' => [
            'general' => 'Allgemeine Informationen',
            'validation' => 'Validierungsregeln'
        ],
    ],

    'fields' => [
        'module'      => 'Modul',
        'key'         => 'Schlüssel',
        'type'        => 'Typ',
        'value'       => 'Wert',
        'label'       => 'Bezeichnung',
        'description' => 'Beschreibung',
        'order'       => 'Reihenfolge',
        'options'     => 'Optionen',
        'is_active'   => 'Aktiv',
        'created_at'  => 'Erstellt Am',
        'updated_at'  => 'Aktualisiert Am',
        'validation_rules' => 'Validierungsregeln',
        'custom_validation_message' => 'Benutzerdefinierte Validierungsnachricht',
        'tenant'       => "Mandant"
    ],

    'types' => [
        'text'     => 'Text',
        'textarea' => 'Textbereich',
        'number'   => 'Nummer',
        'boolean'  => 'Boolesch',
        'select'   => 'Auswahl',
        'json'     => 'JSON',
    ],
    'hints' => [
        'options' => 'Definieren Sie die verfügbaren Optionen für Auswahlfelder',
        'validation_rules' => 'Definieren Sie Laravel-Validierungsregeln (z. B. required, max:255, email, usw.)',
        'validation_rules_examples' => 'Beispiele: required → (leer), max → 255, min → 5, email → (leer)',
    ],
];
