<?php

namespace app\modules\rbac\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "rbac_assignment".
 *
 * @property string $item_name
 * @property string $user_id
 * @property integer $created_at
 *
 * @property RbacItem $itemName
 */
class RbacAssignment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rbac_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['created_at'], 'integer'],
            [['item_name', 'user_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(RbacItem::className(), ['name' => 'item_name']);
    }

    public static function rolesWithPermissionsArray()
    {
        $result = [];

        $roles = self::getAuth()->getRoles();

        foreach ($roles as $role) {
            $result[] = [
                'role' => $role,
                'permissions' => self::getAuth()->getPermissionsByRole($role->name)
            ];
        }

        return $result;
    }

    public function getRoles()
    {
        return self::getAuth()->getRoles();
    }

    public function getPermissions()
    {
        return self::getAuth()->getPermissions();
    }

    public function getRules()
    {
        return self::getAuth()->getRules();
    }

    public static function getAuth()
    {
        return Yii::$app->authManager;
    }

    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }

    public function assign($role, $userId)
    {
        return self::getAuth()->assign($role, $userId);
    }
}
