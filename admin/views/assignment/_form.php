<?php

/**
 * Assignment form view.
 *
 * @var \yii\base\View $this View
 * @var \yii\widgets\ActiveForm $form Form
 * @var \yii\base\DynamicModel $model Model
 * @var array $roleArray Roles array
 * @var array $ruleArray Rules array
 * @var array $permissionArray Permissions array
 */

use app\modules\rbac\Module;
use vova07\select2\Widget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-sm-6">
        <?= $form->field($model, 'item_name')->widget(Widget::className(), [
            'options' => [
                'prompt' => Module::t('rbac', 'Select Assignment'),
            ],
            'settings' => [
                'width' => '100%',
            ],
            'items' => $roleArray
        ]) ?>
    </div>
</div>

<?= Html::submitButton(!isset($update) ? Module::t('rbac', 'Create') : Module::t('rbac', 'Update'), [
    'class' => !isset($update) ? 'btn btn-primary btn-large' : 'btn btn-success btn-large'
]) ?>

<?php ActiveForm::end(); ?>