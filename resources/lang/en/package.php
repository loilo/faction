<?php

return [
    'none_available' => 'No packages available',
    'open_in_github' => 'Open on GitHub',
    'copy_install' => 'Copy Install Command',
    'latest_commit_on_branch' => 'on',
    'show_packages' => 'Show Packages',
    'show_all' => 'All Packages',
    'no_readme' => 'This package seems to have no readme.',
    'archived' =>
        'This repository is no longer maintained and has been archived.',
    'sections' => [
        'readme' => 'Readme',
        'versions' => 'Versions',
        'relations' => 'Relations',
    ],
    'versions' => [
        'headline' => 'Versions',
        'show_release' => 'Show release on GitHub',
        'show_commit' => 'Show commit on GitHub',
        'show_diff' => 'Show changes since :version on GitHub',
    ],
    'relations' => [
        'headline' => 'Relations',
        'no_relations' =>
            'This package has neither any dependencies nor dependants.',
        'no_dependencies' => 'This package has no dependencies.',
        'dependencies' => [
            'headline' => 'Dependencies',
            'package' => 'dependency',
            'constraint' => 'version constraint',
        ],
        'dev_dependencies' => [
            'headline' => 'Dev Dependencies',
            'package' => 'dependency',
            'constraint' => 'version constraint',
        ],
        'dependants' => [
            'headline' => 'Dependants',
            'intro' =>
                'The following internal packages depend on <code>:package_name</code>:',
            'package' => 'dependent package',
            'version' => ':package_name version',
        ],
    ],
];
