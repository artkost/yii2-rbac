<?php

/**
 * Roles list view.
 *
 * @var \yii\base\View $this View
 * @var \yii\data\ArrayDataProvider $provider Data provider
 */

use app\modules\rbac\Module;

use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;

$this->title = Module::t('rbac', 'BACKEND_ROLES_INDEX_TITLE');
$this->params['subtitle'] = Module::t('rbac', 'BACKEND_ROLES_INDEX_SUBTITLE');
$this->params['breadcrumbs'] = [
    $this->title
];
$gridId = 'roles-grid';
$gridConfig = [
    'id' => $gridId,
    'dataProvider' => $provider,
    'columns' => [
        [
            'class' => CheckboxColumn::classname()
        ],
        'item_name',
        [
            'attribute' => 'user',
            'format' => 'html',
            'value' => function ($model) {
                if ($model->user) {
                    return Html::a("{$model->user->username} ({$model->user->email})", ['/user/default/view', 'id' => $model->user->id]);
                } else {
                    return '';
                }

            }
        ],
        'created_at:date',
    ]
];

$boxButtons = $actions = [];
$showActions = false;

if (Yii::$app->user->can('adminAssignmentCreate')) {
    $boxButtons[] = '{create}';
}
if (Yii::$app->user->can('adminAssignmentUpdate')) {
    $actions[] = '{update}';
    $showActions = $showActions || true;
}
if (Yii::$app->user->can('adminAssignmentDelete')) {
    $boxButtons[] = '{batch-delete}';
    $actions[] = '{delete}';
    $showActions = $showActions || true;
}

if ($showActions === true) {
    $gridConfig['columns'][] = [
        'class' => ActionColumn::className(),
        'template' => implode(' ', $actions)
    ];
}

$boxButtons = !empty($boxButtons) ? implode(' ', $boxButtons) : null;

?>

<div class="row">
    <div class="col-xs-12">
        <?php Box::begin([
            'title' => $this->params['subtitle'],
            'bodyOptions' => [
                'class' => 'table-responsive'
            ],
            'batchParam' => 'names',
            'buttonsTemplate' => $boxButtons,
            'grid' => $gridId,
        ]); ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Box::end(); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <?php Box::begin([
            'title' => 'Items',
            'bodyOptions' => [
                'class' => 'table-responsive'
            ]
        ]); ?>
        <div class="panel-group" id="roles" role="tablist" aria-multiselectable="true">
            <?php foreach ($rolesArray as $row): ?>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="role-<?= $row['role']->name ?>">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#roles" href="#role-<?= $row['role']->name ?>-collapse" aria-expanded="true" aria-controls="collapseOne">
                                <?= $row['role']->description ?>
                            </a>
                        </h4>
                    </div>
                    <div id="role-<?= $row['role']->name ?>-collapse" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="role-<?= $row['role']->name ?>">
                        <div class="panel-body">
                            <ul class="list-group">
                                <?php foreach ($row['permissions'] as $permission): ?>
                                    <li class="list-group-item"><?= $permission->description ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php Box::end(); ?>
    </div>
</div>
