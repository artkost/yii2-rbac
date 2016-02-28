<?php

namespace artkost\rbac\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\web\User as WebUser;

class User extends WebUser
{
    public $superAdminRoleName = 'superAdmin';

    /**
     * Result of superAdminRole check
     * @var boolean
     */
    protected $superAdminCheck;

    protected $defaultRolesPermissions;

    /**
     * @inheritdoc
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        return $this->isSuperAdmin() || $this->isGuestCan($permissionName) || parent::can($permissionName, $params, $allowCaching);
    }

    protected function isSuperAdmin()
    {
        /** @var DbManager $auth */
        $auth = Yii::$app->getAuthManager();

        if (is_null($this->superAdminCheck)) {
            $this->superAdminCheck = $auth->getAssignment($this->superAdminRoleName, $this->getId());
        }

        return (bool) $this->superAdminCheck;
    }

    protected function isGuestCan($permissionName)
    {
        /** @var DbManager $auth */
        $auth = Yii::$app->getAuthManager();

        if ($this->defaultRolesPermissions == null) {
            foreach ($auth->defaultRoles as $roleName) {
                $this->defaultRolesPermissions[$roleName] = ArrayHelper::index($auth->getPermissionsByRole($roleName), 'name');
            }
        }

        foreach ($auth->defaultRoles as $roleName) {
            return isset($this->defaultRolesPermissions[$roleName][$permissionName]);
        }

        return false;
    }
}