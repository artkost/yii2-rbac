<?php

/**
 * Assignment list view.
 *
 * @var \yii\base\View $this View
 * @var \yii\data\ArrayDataProvider $provider Data provider
 */

use app\modules\rbac\Module;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;

$this->title = Module::t('rbac', 'Assignment');
$this->params['subtitle'] = Module::t('rbac', 'List');
$this->params['breadcrumbs'] = [
    $this->title
];
$gridId = 'assignment-grid';
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

$actions = [];
$showActions = false;

if (Yii::$app->user->can('adminAssignmentUpdate')) {
    $actions[] = '{update}';
    $showActions = $showActions || true;
}
if (Yii::$app->user->can('adminAssignmentDelete')) {
    $actions[] = '{delete}';
    $showActions = $showActions || true;
}

if ($showActions === true) {
    $gridConfig['columns'][] = [
        'class' => ActionColumn::className(),
        'template' => implode(' ', $actions)
    ];
}

?>

<div class="row">
    <div class="col-xs-12">
        <?= GridView::widget($gridConfig); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
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
    </div>
</div>