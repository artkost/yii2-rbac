<?php

namespace app\modules\rbac\commands;

use app\modules\rbac\rules\UserRoleRule;
use Yii;
use yii\base\Module;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;

/**
 * RBAC console controller.
 */
class ManagerController extends Controller
{
    /**
     * @var array
     */
    protected $roles = [];
    /**
     * @var array
     */
    protected $permissions = [];
    /**
     * @var array
     */
    protected $rules = [];

    public function actionRefresh()
    {
        $this->getAuth()->removeAllPermissions();
        $this->getAuth()->removeAllRoles();
        $this->getAuth()->removeAllRules();

        $this->permissions = ArrayHelper::index($this->getAuth()->getPermissions(), 'name');
        $this->roles = ArrayHelper::index($this->getAuth()->getRoles(), 'name');

        foreach (Yii::$app->getModules() as $id => $module) {
            $this->stdout('Module ');
            $this->stdout($id, Console::FG_GREY);

            $rulesFile = Yii::getAlias("@app/modules/{$id}/") . 'rules.php';

            if (is_array($module) && isset($module['rules'])) {
                $this->mergeRules($module['rules']);
                $this->stdout(' support rules', Console::FG_GREEN);
            } else if ($module instanceof Module && property_exists($module, 'rules')) {
                /** @var $module Module */
                $this->mergeRules($module->rules);
                $this->stdout(' support rules', Console::FG_GREEN);
            } else if (file_exists($rulesFile)) {
                $this->mergeRulesFromFile($rulesFile);
                $this->stdout(' support rules', Console::FG_GREEN);
            } else {
                $this->stdout(' not support rules', Console::FG_RED);
            }
            echo PHP_EOL;
        }

        $this->processRules();
    }

    protected function mergeRulesFromFile($path)
    {
        if (file_exists($path)) {
            $rules = include $path;

            if (is_array($rules)) {
                $this->mergeRules($rules);
            } else {
                throw new Exception("rules.php file must return array");
            }
        }
    }

    protected function mergeRules(array $rules)
    {
        $this->rules = ArrayHelper::merge($this->rules, $rules);
    }

    protected function processRules()
    {
        if (isset($this->rules['rules'])) {
            $this->addRules($this->rules['rules']);
        }

        if (isset($this->rules['roles'])) {
            $this->addRoles($this->rules['roles']);
        }

        if (isset($this->rules['permissions'])) {
            $this->addPermissions($this->rules['permissions']);
        }

        if (isset($this->rules['assignments'])) {
            $this->addAssignments($this->rules['assignments']);
        }
    }

    /**
     * @param Rule[] $rules
     */
    protected function addRules(array $rules)
    {
        $rules[] = new UserRoleRule();

        foreach ($rules as $rule) {
            if ($rule instanceof Rule) {
                $this->getAuth()->add($rule);
            }
        }
    }

    protected function addPermissions(array $permissions)
    {
        foreach ($permissions as $id => $description) {

            if (!isset($this->permissions[$id])) {
                $permission = $this->getAuth()->createPermission($id);

                $permission->description = $description;
                $permission->ruleName = $this->getRuleNameForItem($id, false);

                $this->getAuth()->add($permission);

                $this->permissions[$id] = $permission;

                $this->stdout('Permission ');
                $this->stdout($id, Console::FG_BLUE);
                echo PHP_EOL;
            }

        }
    }

    protected function addRoles(array $roles)
    {
        foreach ($roles as $id => $description) {

            if (!isset($this->roles[$id])) {
                $role = $this->getAuth()->createRole($id);

                $role->description = $description;
                $role->ruleName = $this->getRuleNameForItem($id);

                $this->getAuth()->add($role);

                $this->roles[$id] = $role;

                $this->stdout('Role ');
                $this->stdout($id, Console::FG_YELLOW);
                echo PHP_EOL;
            }

        }
    }

    protected function addAssignments(array $assignments)
    {
        foreach ($assignments as $roleName => $permissions) {

            foreach ($permissions as $name) {

                if (isset($this->permissions[$name])) {
                    $permission = $this->permissions[$name];
                } else if (isset($this->roles[$name])) {
                    $permission = $this->roles[$name];
                } else {
                    $permission = null;
                }

                if ($permission && isset($this->roles[$roleName])) {
                    $role = $this->roles[$roleName];

                    $this->getAuth()->addChild($role, $permission);

                    $this->stdout('Assign permission ');
                    $this->stdout($permission->name, Console::FG_BLUE);
                    $this->stdout(' to role ');
                    $this->stdout($role->name, Console::FG_YELLOW);
                    echo PHP_EOL;
                }

            }

        }
    }

    protected function getRuleNameForItem($name, $returnDefault = true)
    {
        if (isset($this->rules['rules'])) foreach ($this->rules['rules'] as $role => $rule) {
            if ($role == $name && $rule instanceof Rule) {
                return $rule->name;
            }
        }

        if ($returnDefault) {
            return (new UserRoleRule())->name;
        } else {
            return null;
        }
    }

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuth()
    {
        return Yii::$app->authManager;
    }

    /**
     * Assign role to user
     * @param $id int
     * @param $roleName string
     */
    public function actionAssign($id, $roleName)
    {
        foreach ($this->getAuth()->getRoles() as $name => $role) {
            if ($roleName == $name) {
                $this->getAuth()->assign($role, $id);
                $this->stdout('Role ' . $name . ' assigned to user with ID:' . $id);
            }
        }
    }

    /**
     * Revoke role to user
     * @param $id int
     * @param $roleName string
     */
    public function actionRevoke($id, $roleName)
    {
        foreach ($this->getAuth()->getRoles() as $name => $role) {
            if ($roleName == $name) {
                $this->getAuth()->revoke($role, $id);
                $this->stdout('Role ' . $name . ' revoked from user with ID:' . $id);
            }
        }
    }
}