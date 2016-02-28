<?php
namespace artkost\rbac\rules;

use Yii;
use yii\rbac\Rule;

class UserSuperAdminRule extends Rule
{
    public $name = 'superAdmin';
    public $role = '';

    public function execute($user, $item, $params)
    {
        if ($user && $item->name == $this->role) {
            return true;
        }

        return false;
    }
}