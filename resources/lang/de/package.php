<?php

return [
    'none_available' => 'Keine Pakete verfügbar',
    'open_in_github' => 'In GitHub öffnen',
    'copy_install' => 'Installationsbefehl kopieren',
    'latest_commit_on_branch' => 'in',
    'show_packages' => 'Pakete anzeigen',
    'show_all' => 'Alle Pakete',
    'no_readme' => 'Dieses Paket hat keine Readme.',
    'archived' => 'Dieses Repository wurde archiviert.',
    'sections' => [
        'readme' => 'Readme',
        'versions' => 'Versionen',
        'relations' => 'Beziehungen',
    ],
    'versions' => [
        'headline' => 'Versionen',
        'show_release' => 'Release in GitHub öffnen',
        'show_commit' => 'Commit in GitHub öffnen',
        'show_diff' => 'Änderungen seit :version in GitHub anzeigen',
    ],
    'relations' => [
        'headline' => 'Beziehungen',
        'no_relations' =>
            'Dieses Paket hat weder Abhängigkeiten noch abhängige Pakete.',
        'no_dependencies' => 'Dieses Paket hat keine Abhängigkeiten.',
        'dependencies' => [
            'headline' => 'Abhängigkeiten',
            'package' => 'Abhängigkeit',
            'constraint' => 'Versionen',
        ],
        'dev_dependencies' => [
            'headline' => 'Entwicklungs-Abhängigkeiten',
            'package' => 'Abhängigkeit',
            'constraint' => 'Versionen',
        ],
        'dependants' => [
            'headline' => 'Abhängige Pakete',
            'intro' =>
                'Die folgenden internen Pakete haben eine Abhängigkeit von <code>:package_name</code>:',
            'package' => 'Abhängige Pakete',
            'version' => ':package_name Version',
        ],
    ],
];
