<?php

namespace app\modules\v1\model;

use Yii,
    yii\data\Pagination;

/**
 * This is the model class for table "{{%admin_authority}}".
 *
 * @property int $id
 * @property int $parent_id 父级  0=顶级
 * @property string|null $code 编码
 * @property string $name 名称
 * @property int $show 是否显示 0=显示 1=隐藏
 * @property int $status 状态 0=开启 1=禁止
 */
class AdminAuthority extends \yii\db\ActiveRecord
{
    const SCENARIO_ADMIN_AUTHORITY_UPDATE='update';
    const SCENARIO_ADMIN_AUTHORITY_CREATE='create';
    const SCENARIO_ADMIN_AUTHORITY_SEARCH='search';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_authority}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required','on'=>['create']],
            [['code','name','parent_id','show','status'],'required','on'=>['create']],
            [['id', 'parent_id', 'show', 'status'], 'integer','on'=>['create','update','search']],
            ['id', 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['code'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique','on'=>['create']],
            [['show'], 'in','range'=>[0,1]],
            [['status'], 'in','range'=>[0,1]],
        ];
    }
    public function scenarios()
    {
        return [
            self::SCENARIO_ADMIN_AUTHORITY_UPDATE=>['name','status','id','code','show','parent_id'],
            self::SCENARIO_ADMIN_AUTHORITY_CREATE=>['id','name','parent_id','show','status','code'],
            self::SCENARIO_ADMIN_AUTHORITY_SEARCH=>['id','name','parent_id','show','status']
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Authority ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'code' => Yii::t('app', 'Authority Code'),
            'name' => Yii::t('app', 'Authority Name'),
            'show' => Yii::t('app', 'Authority Show'),
            'status' => Yii::t('app', 'Authority Status'),
        ];
    }

    public function search($params=[]){
        $query = $this->find();
        $pageSize=$params['limit'];
        $page=$params['page'];
        $query->andFilterWhere(['id' =>$params['id']])
            ->andFilterWhere(['parent_id' =>$params['parent_id']])
            ->andFilterWhere(['show' =>$params['show']])
            ->andFilterWhere(['status' =>$params['status']])
            ->andFilterWhere( ['like', 'name',$params['name']])
            ->andFilterWhere(['like', 'code',$params['code']]);
        $count=$query->count();
        $page=$page-1>=0?$page-1:0;
        $pages = new Pagination(['totalCount' => $count,'pageSize' => $pageSize,'page'=>$page]);
        $dataProvider=$query->offset($pages->offset)->limit($pages->limit)->all();
        return [
            'list'=>$dataProvider,
            'count'=>(int)$count
        ];
    }
}
