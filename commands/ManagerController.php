<?php

namespace artkost\rbac\commands;

use artkost\rbac\Event;
use artkost\rbac\Module;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * RBAC console manager.
 * @property Module $module
 */
class ManagerController extends Controller
{

    /**
     * Refreshes RBAC rules definitions from module files
     */
    public function actionRefresh()
    {
        $this->module->on(Module::EVENT_ROLE_ADD, [$this, 'onRoleAdd']);
        $this->module->on(Module::EVENT_PERMISSION_ADD, [$this, 'onPermissionAdd']);
        $this->module->on(Module::EVENT_ASSIGNMENT_ADD, [$this, 'onAssignmentAdd']);
        $this->module->on(Module::EVENT_RULE_ADD, [$this, 'onRuleAdd']);

        $this->module->refresh();
    }

    protected function onRuleAdd(Event $event)
    {
        $this->stdout('Rule ');
        $this->stdout($event->params['rule']->name, Console::FG_BLUE);
        echo PHP_EOL;
    }

    protected function onPermissionAdd(Event $event)
    {
        $this->stdout('Permission ');
        $this->stdout($event->params['permission']->name, Console::FG_BLUE);
        echo PHP_EOL;
    }

    protected function onRoleAdd(Event $event)
    {
        $this->stdout('Role ');
        $this->stdout($event->params['role']->name, Console::FG_YELLOW);
        echo PHP_EOL;
    }

    protected function onAssignmentAdd(Event $event)
    {
        $this->stdout('Assign permission ');
        $this->stdout($event->params['permission']->name, Console::FG_BLUE);
        $this->stdout(' to role ');
        $this->stdout($event->params['role']->name, Console::FG_YELLOW);
        echo PHP_EOL;
    }

    /**
     * Assign role to user
     * @param $id int
     * @param $roleName string
     */
    public function actionAssign($id, $roleName)
    {
        $this->module->on(Module::EVENT_ROLE_ASSIGN, [$this, 'onAssign']);
        $this->module->assign($id, $roleName);
    }

    protected function onAssign(Event $event)
    {
        $this->stdout('Role ' . $event->params['name'] . ' assigned to user with ID:' . $event->params['userID']);
        echo PHP_EOL;
    }

    /**
     * Revoke role to user
     * @param $id int
     * @param $roleName string
     */
    public function actionRevoke($id, $roleName)
    {
        $this->module->on(Module::EVENT_ROLE_REVOKE, [$this, 'onRevoke']);
        $this->module->revoke($id, $roleName);
    }

    protected function onRevoke(Event $event)
    {
        $this->stdout('Role ' . $event->params['name'] . ' revoked from user with ID:' . $event->params['userID']);
        echo PHP_EOL;
    }
}
