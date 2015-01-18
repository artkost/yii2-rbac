<?php

use app\modules\rbac\rules\UserSuperAdminRule;

return [
    'permissions' => [
        'adminAssignmentView' => 'View Assignment in Admin Panel',
        'adminAssignmentCreate' => 'Create Assignment in Admin Panel',
        'adminAssignmentUpdate' => 'Update Assignment in Admin Panel',
        'adminAssignmentDelete' => 'Delete Assignment in Admin Panel'
    ],
    'roles' => [
        'superAdmin' => 'Super Administrator',
        'guest' => 'Guest',
        'adminAssignmentReader' => 'Read Assignments',
        'adminAssignmentManager' => 'Manage Assignments',
    ],
    'assignments' => [
        'adminAssignmentReader' => ['adminAssignmentView'],
        'adminAssignmentManager' => [
            'adminAssignmentReader',
            'adminAssignmentCreate',
            'adminAssignmentUpdate',
            'adminAssignmentDelete'
        ],
    ]
];