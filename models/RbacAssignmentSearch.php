<?php

namespace artkost\rbac\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * Blog search model.
 */
class RbacAssignmentSearch extends RbacAssignment
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['user_id'], 'integer'],
            [['created_at'], 'date', 'format' => 'd.m.Y'],
            [['item_name', 'user_id'], 'string', 'max' => 64],
            [['user'], 'safe']
        ];
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params Search params
     *
     * @return ActiveDataProvider DataProvider
     */
    public function search($params)
    {
        $query = self::find();

        $query->joinWith(['user'], true);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $userClass = Yii::$app->user->identityClass;

        $dataProvider->sort->attributes['user'] = [
            'asc' => [$userClass::tableName() . '.username' => SORT_ASC],
            'desc' => [$userClass::tableName() . '.username' => SORT_DESC],
        ];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(
            [
                'id' => $this->id,
                'status_id' => $this->status_id,
                'FROM_UNIXTIME(created_at, "%d.%m.%Y")' => $this->created_at,
            ]
        );

        $query->andFilterWhere(['like', 'item_name', $this->item_name]);
        $query->andFilterWhere(['like', $userClass::tableName() . '.username', $this->user]);

        return $dataProvider;
    }
}
