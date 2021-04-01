<?php

namespace app\modules\v1\model;

use Yii,
yii\data\Pagination;

/**
 * This is the model class for table "{{%admin_role_info}}".
 *
 * @property int $id
 * @property string $name 角色名称
 * @property int $status 角色状态 0=开启 1=禁止
 * @property string $create_time
 */
class AdminRoleInfo extends \yii\db\ActiveRecord
{
    const SCENARIO_ADMIN_ROLE_INFO_UPDATE='update';
    const SCENARIO_ADMIN_ROLE_INFO_CREATE='create';
    const SCENARIO_ADMIN_ROLE_INFO_SEARCH='search';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_role_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required','on'=>['create','update']],
            [['name','status'], 'required','on'=>['create']],
            [['id', 'status'], 'integer'],
            [['status'], 'in','range'=>[0,1]],
            ['id', 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique','on'=>['create']],
        ];
    }
    public function scenarios()
    {
        return [
            self::SCENARIO_ADMIN_ROLE_INFO_UPDATE=>['id','name','status'],
            self::SCENARIO_ADMIN_ROLE_INFO_CREATE=>['id','name','status','create_time'],
            self::SCENARIO_ADMIN_ROLE_INFO_SEARCH=>['id','name','status']
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Role ID'),
            'name' => Yii::t('app', 'Role Name'),
            'status' => Yii::t('app', 'Role Status'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }
    public function search($params=[]){
        $query = $this->find();
        $pageSize=$params['limit'];
        $page=$params['page'];
        $query->andFilterWhere(['id' =>$params['id']])
            ->andFilterWhere(['status' =>$params['status']])
            ->andFilterWhere( ['like', 'name',$params['name']]);
        $count=$query->count();
        if($count==0){
            return [
                'list'=>[],
                'count'=>(int)$count
            ];
        }
        $page=$page-1>=0?$page-1:0;
        $pages = new Pagination(['totalCount' => $count,'pageSize' => $pageSize,'page'=>$page]);
        $dataProvider=$query->offset($pages->offset)->limit($pages->limit)->all();
        foreach($dataProvider as $k=>$v){
            $dataProvider[$k]=[
                'id'=>$v->id,
                'name'=>$v->name,
                'create_time'=>$v->create_time,
                'status'=>[
                    'key'=>$v->status,
                    'value'=>$v->status?Yii::t('app','Off'):Yii::t('app','On')
                ]
            ];
        }
        return [
            'list'=>$dataProvider,
            'count'=>(int)$count
        ];
    }
}
