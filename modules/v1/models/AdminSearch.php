<?php

namespace app\modules\v1\models;

use Yii,
    yii\base\Model,
    yii\data\ActiveDataProvider,
    app\modules\v1\model\Admin,
    app\modules\v1\model\AdminInfo,
    yii\data\Pagination;
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
            [['id'],'default', 'value' => null],
            [['id'], 'integer'],
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
       
        $query = Admin::find();
        $pageSize=Yii::$app->params['pageSize'];
        $page=Yii::$app->params['page'];
        $pageSize=isset($params['limit']) && !empty($params['limit'])?$params['limit']:Yii::$app->params['pageSize'];
        $page=isset($params['page']) && !empty($params['page'])?$params['page']:$page;
        $table=Admin::tableName();
        $query->andFilterWhere([$table.'.id' =>$params['id']])->andFilterWhere(['like',$table.'.name',$params['name']]);
        $query->joinWith(["adminInfo"=>function($query) use($params){
            $table=AdminInfo::tableName();
           return $query->andFilterWhere( ['like', $table.'.real_name',$params['real_name']])
           ->andFilterWhere(['like', $table.'.phone',$params['phone']])
           ->andFilterWhere(['like', $table.'.email',$params['email']]);
        }]);

        $count=$query->count();
        $pages = new Pagination(['totalCount' => $count,'pageSize' => $pageSize,'page'=>$page-1]);
        //$dataProvider=$query->with("adminInfo")->offset($pages->offset)->limit($pages->limit)->all();
        $dataProvider=$query->offset($pages->offset)->limit($pages->limit)->all();
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
