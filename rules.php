<?php

use artkost\Module;
use artkost\rbac\rules\UserSuperAdminRule;

return [
    'permissions' => [
        'adminAssignmentView' => Module::t('rules', 'View Assignment'),
        'adminAssignmentCreate' => Module::t('rules', 'Create Assignment'),
        'adminAssignmentUpdate' => Module::t('rules', 'Update Assignment'),
        'adminAssignmentDelete' => Module::t('rules', 'Delete Assignment')
    ],
    'roles' => [
        'superAdmin' => Module::t('rules', 'Super Administrator'),
        'guest' => Module::t('rules', 'Guest'),
        'adminAssignmentReader' => Module::t('rules', 'Read Assignments'),
        'adminAssignmentManager' => Module::t('rules', 'Manage Assignments'),
    ],
    'assignments' => [
        'adminAssignmentReader' => ['adminAssignmentView'],
        'adminAssignmentManager' => [
            'adminAssignmentReader',
            'adminAssignmentCreate',
            'adminAssignmentUpdate',
            'adminAssignmentDelete'
        ],
    ],
    'rules' => [
        'superAdmin' => new UserSuperAdminRule(['role' => 'superAdmin'])
    ]
];
