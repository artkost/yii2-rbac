<?php

namespace app\modules\rbac\models;

use Yii;

/**
 * This is the model class for table "rbac_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property RbacAssignment[] $rbacAssignments
 * @property RbacRule $ruleName
 * @property RbacItem[] $rbacItemChildren
 */
class RbacItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rbac_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRbacAssignments()
    {
        return $this->hasMany(RbacAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(RbacRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRbacItemChildren()
    {
        return $this->hasMany(RbacItem::className(), ['child' => 'name']);
    }
}
