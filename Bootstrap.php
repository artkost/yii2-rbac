<?php

namespace app\modules\rbac;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['rbac/rbac'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@app/modules/rbac/messages',
            'forceTranslation' => true,
            'fileMap' => [
                'rbac/rbac' => 'rbac.php',
            ]
        ];
    }
} 