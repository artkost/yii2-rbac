<?php

namespace artkost\rbac;

use artkost\rbac\models\RbacDefinition;
use artkost\rbac\rules\UserRoleRule;
use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class Module extends \yii\base\Module implements BootstrapInterface
{
    const PARAM_ROOT = 'rbac';
    const TRANSLATE_CATEGORY = 'rbac';

    const EVENT_RULE_ADD = 'ruleAdd';
    const EVENT_ROLE_ADD = 'roleAdd';
    const EVENT_PERMISSION_ADD = 'permissionAdd';
    const EVENT_ASSIGNMENT_ADD = 'assignmentAdd';
    const EVENT_ROLE_ASSIGN = 'roleAssign';
    const EVENT_ROLE_REVOKE = 'roleRevoke';

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
    protected $definitions = [];

    public static function param($name, $default)
    {
        $key = self::PARAM_ROOT . '.' . $name;

        return ArrayHelper::getValue(Yii::$app->params, $key, $default);
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t(self::TRANSLATE_CATEGORY . '/' . $category, $message, $params, $language);
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->i18n->translations[Module::TRANSLATE_CATEGORY . '/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ .'/messages',
            'forceTranslation' => true,
            'fileMap' => [
                'rbac/rbac' => 'rbac.php',
            ]
        ];
    }

    public function init()
    {
        parent::init();

        $this->set('definitionManager', [
            'class' => DefinitionManager::className()
        ]);
    }

    /**
     * Refreshes all rbac rules.
     * Deletes all old and gets new from definition files
     */
    public function refresh()
    {
        $this->getAuthManager()->removeAllPermissions();
        $this->getAuthManager()->removeAllRoles();
        $this->getAuthManager()->removeAllRules();

        $this->setPermissions(ArrayHelper::index($this->getAuthManager()->getPermissions(), 'name'));
        $this->setRoles(ArrayHelper::index($this->getAuthManager()->getRoles(), 'name'));

        /** @var DefinitionManager $manager */
        $manager = $this->get('definitionManager');

        foreach ($manager->getDefinitions() as $id => $definition) {
            $this->mergeRules($definition);
        }

        $this->processRules();
    }

    /**
     * Merge rules from definition
     * @param RbacDefinition $definition
     */
    public function mergeRules(RbacDefinition $definition)
    {
        $this->definitions = ArrayHelper::merge($this->definitions, $definition->toArray());
    }

    /**
     *  Process rules array from all modules
     */
    protected function processRules()
    {
        if (isset($this->definitions['rules'])) {
            $this->addRules($this->definitions['rules']);
        }

        if (isset($this->definitions['roles'])) {
            $this->addRoles($this->definitions['roles']);
        }

        if (isset($this->definitions['permissions'])) {
            $this->addPermissions($this->definitions['permissions']);
        }

        if (isset($this->definitions['assignments'])) {
            $this->addAssignments($this->definitions['assignments']);
        }
    }

    /**
     * Assigns role to user by id
     * @param $userID
     * @param $roleName
     */
    public function assign($userID, $roleName)
    {
        foreach ($this->getAuthManager()->getRoles() as $name => $role) {
            if ($roleName == $name) {
                $this->getAuthManager()->assign($role, $userID);
                $this->trigger(self::EVENT_ROLE_ASSIGN, new Event([
                    'params' => [
                        'role' => $role,
                        'userID' => $userID
                    ]
                ]));
            }
        }
    }

    /**
     * Revokes role from user by id
     * @param $userID
     * @param $roleName
     */
    public function revoke($userID, $roleName)
    {
        foreach ($this->getAuthManager()->getRoles() as $name => $role) {
            if ($roleName == $name) {
                $this->getAuthManager()->revoke($role, $userID);

                $this->trigger(self::EVENT_ROLE_REVOKE, new Event([
                    'params' => [
                        'role' => $role,
                        'userID' => $userID
                    ]
                ]));
            }
        }
    }

    /**
     * Adds rules to Auth Manager
     * @param Rule[] $rules
     */
    public function addRules(array $rules)
    {
        $rules[] = $this->getDefaultUserRole();

        foreach ($rules as $rule) {
            if ($rule instanceof Rule) {
                $this->getAuthManager()->add($rule);

                $this->trigger(self::EVENT_RULE_ADD, new Event([
                    'params' => [
                        'rule' => $rule
                    ]
                ]));
            }
        }
    }

    /**
     * @param array $permissions
     */
    public function addPermissions(array $permissions)
    {
        foreach ($permissions as $id => $description) {
            if (!isset($this->permissions[$id])) {

                $permission = $this->getAuthManager()->createPermission($id);
                $permission->description = $description;
                $permission->ruleName = $this->getRuleNameForItem($id, false);
                $this->permissions[$id] = $permission;

                $this->getAuthManager()->add($this->permissions[$id]);

                $this->trigger(self::EVENT_PERMISSION_ADD, new Event([
                    'params' => [
                        'permission' => $this->permissions[$id]
                    ]
                ]));
            }
        }
    }

    /**
     * @param array $roles
     */
    public function addRoles(array $roles)
    {
        foreach ($roles as $id => $description) {
            if (!isset($this->roles[$id])) {

                $role = $this->getAuthManager()->createRole($id);
                $role->description = $description;
                $role->ruleName = $this->getRuleNameForItem($id);
                $this->roles[$id] = $role;

                $this->getAuthManager()->add($this->roles[$id]);

                $this->trigger(self::EVENT_ROLE_ADD, new Event([
                    'params' => [
                        'role' => $role
                    ]
                ]));
            }

        }
    }

    /**
     * @param array $assignments
     */
    public function addAssignments(array $assignments)
    {
        foreach ($assignments as $roleName => $permissions) {
            foreach ($permissions as $id => $name) {

                if (isset($this->permissions[$name])) {
                    $permission = $this->permissions[$name];
                } else if (isset($this->roles[$name])) {
                    $permission = $this->roles[$name];
                } else {
                    $permission = null;
                }

                if ($permission && isset($this->roles[$roleName])) {
                    $role = $this->roles[$roleName];

                    $this->getAuthManager()->addChild($role, $permission);

                    $this->trigger(self::EVENT_ASSIGNMENT_ADD, new Event([
                        'params' => [
                            'role' => $role,
                            'permission' => $permission
                        ]
                    ]));
                }

            }
        }
    }

    /**
     * Rule name for rbacItem or returns default UserRole name
     * @param $name
     * @param bool $returnDefault
     * @return null|string
     */
    public function getRuleNameForItem($name, $returnDefault = true)
    {
        if (isset($this->definitions['rules'])) foreach ($this->definitions['rules'] as $role => $rule) {
            if ($role == $name && $rule instanceof Rule) {
                return $rule->name;
            }
        }

        if ($returnDefault) {
            return $this->getDefaultUserRole()->name;
        } else {
            return null;
        }
    }

    /**
     * @return UserRoleRule
     */
    public function getDefaultUserRole()
    {
        return new UserRoleRule();
    }

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuthManager()
    {
        return Yii::$app->authManager;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param array $definitions
     */
    public function setDefinitions($definitions)
    {
        $this->definitions = $definitions;
    }
}
