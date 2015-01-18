<?php
namespace app\modules\rbac\rules;

use Yii;
use yii\rbac\Rule;

class UserRoleRule extends Rule
{
    public $name = 'userRole';

    public function execute($user, $item, $params)
    {
        $manager = Yii::$app->authManager;

        if ($user) {
            $roles = $manager->getRolesByUser($user);

            foreach ($roles as $role) {
                if ($role->name == $item->name) return true;
            }
        }

        return false;
    }
}