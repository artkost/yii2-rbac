<?php

/**
 * Role create view.
 *
 * @var \yii\base\View $this View
 * @var \yii\base\DynamicModel $model Model
 * @var \vova07\themes\admin\widgets\Box $box Box widget instance
 * @var array $roleArray Roles array
 * @var array $ruleArray Rules array
 * @var array $permissionArray Permissions array
 */

use app\themes\admin\widgets\Box;
use app\modules\rbac\Module;

$this->title = Module::t('rbac', 'BACKEND_ROLES_CREATE_TITLE');
$this->params['subtitle'] = Module::t('rbac', 'BACKEND_ROLES_CREATE_SUBTITLE');
$this->params['breadcrumbs'] = [
    [
        'label' => $this->title,
        'url' => ['index'],
    ],
    $this->params['subtitle']
]; ?>
<div class="row">
    <div class="col-sm-12">
        <?= $this->render(
            '_form',
            [
                'model' => $model,
                'roleArray' => $roleArray
            ]
        ); ?>
    </div>
</div>