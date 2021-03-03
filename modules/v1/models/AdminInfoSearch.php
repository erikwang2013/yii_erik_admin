<?php

namespace app\modules\v1\models;

use yii\base\Model, 
    yii\data\ActiveDataProvider,
    app\modules\v1\model\AdminInfo;

/**
 * AdminInfoSearch represents the model behind the search form of `app\modules\v1\models\AdminInfo`.
 */
class AdminInfoSearch extends AdminInfo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'sex', 'phone'], 'integer'],
            [['real_name', 'email', 'img', 'create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AdminInfo::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'admin_id' => $this->id,
            'sex' => $this->sex,
            'phone' => $this->phone,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'real_name', $this->real_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'img', $this->img]);

        return $dataProvider;
    }
}
