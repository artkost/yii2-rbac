<?php

namespace app\modules\rbac\rules;

use yii\rbac\Rule;

class UserAuthorRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'author';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            return $params['model']['user_id'] == $user;
        }

        if (isset($params['user_id'])) {
            return $params['user_id'] == $user;
        }

        return false;
    }
}