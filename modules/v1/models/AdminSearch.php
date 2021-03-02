<?php

namespace app\modules\v1\models;

use Yii,yii\base\Model,yii\data\ActiveDataProvider,
app\modules\v1\models\AdminInfo,
app\modules\v1\models\Admin,yii\data\Pagination;
/**
 * AdminSearch represents the model behind the search form of `app\modules\v1\models\Admin`.
 */
class AdminSearch extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
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
    public function search($params=[])
    {
       
       
        //unset($params['limit']);unset($params['page']);
       
        $query = Admin::find();
        $pageSize=Yii::$app->params['pageSize'];
        $page=0;
        $this->load($params);
        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        $query->andFilterWhere(['like', 'admin.name', $this->name]);
        $pageSize=isset($params['limit']) && !empty($params['limit'])?$params['limit']:Yii::$app->params['pageSize'];
        $page=isset($params['page']) && !empty($params['page'])?$params['page']-1:0;
        //var_dump($this);exit;
        $count=$query->count();
        $pages = new Pagination(['totalCount' => $count,'pageSize' => $pageSize,'page'=>$page]);
        //$dataProvider=$query->with("adminInfo")->offset($pages->offset)->limit($pages->limit)->all();
        $dataProvider=$query->select("*")->joinWith("adminInfo")->all();
        foreach($dataProvider as $k=>$v){
            $adminInfo=$v->adminInfo;
            $dataProvider[$k]=[
                'id'=>$v->id,
                'name'=>$v->name,
                'real_name'=>$adminInfo->real_name,
                'status'=>[
                    'key'=>$v->status,
                    'value'=>$v->status?Yii::t('app','Off'):Yii::t('app','On')
                ],
                'sex'=>[
                    'key'=>$adminInfo->sex,
                    'value'=>$adminInfo->sex?Yii::t('app','Man'):Yii::t('app','Woman')
                ],
                'phone'=>$adminInfo->phone,
                'email'=>$adminInfo->email,
                'img'=>$adminInfo->img,
                'create_time'=>$adminInfo->create_time,
                'update_time'=>$adminInfo->update_time
            ];
           unset($v->adminInfo);
        }
        return [
            'list'=>$dataProvider,
            'count'=>(int)$count
        ];
    }
}
