<?php

/**
 * Role update view.
 *
 * @var \yii\base\View $this View
 * @var \yii\base\DynamicModel $model Model
 * @var array $roleArray Roles array
 * @var array $ruleArray Rules array
 * @var array $permissionArray Permissions array
 */

use app\themes\admin\widgets\Box;
use app\modules\rbac\Module;

$this->title = Module::t('rbac', 'Assignment');
$this->params['subtitle'] = Module::t('rbac', 'Update');
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
                'roleArray' => $roleArray,
                'update' => true
            ]
        ); ?>
    </div>
</div>
