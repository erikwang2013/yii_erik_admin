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
            [['id','code','name','parent_id','show','status'],'required','on'=>['create']],
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
            self::SCENARIO_ADMIN_AUTHORITY_UPDATE=>['id','name','status','code','show','parent_id'],
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

    public function getName($id){
        $query = $this->findOne($id);
        return $query['name'];
    }

    public function search($params=[],$page,$limit){
        $query = $this->find();
        $query->andFilterWhere(['id' =>isset($params['id'])?$params['id']:''])
            ->andFilterWhere(['parent_id' =>isset($params['parent_id'])?$params['parent_id']:''])
            ->andFilterWhere(['show' =>isset($params['show'])?$params['show']:''])
            ->andFilterWhere(['status' =>isset($params['status'])?$params['status']:''])
            ->andFilterWhere( ['like', 'name',isset($params['name'])?$params['name']:''])
            ->andFilterWhere(['like', 'code',isset($params['code'])?$params['code']:'']);
        $count=$query->count();
        if($count==0){
            return [
                'list'=>[],
                'count'=>(int)$count
            ];
        }
        $page=$page-1>=0?$page-1:0;
        $pages = new Pagination(['totalCount' => $count,'pageSize' => $limit,'page'=>$page]);
        $dataProvider=$query->offset($pages->offset)->limit($pages->limit)->all();
        foreach($dataProvider as $k=>$v){
            $dataProvider[$k]=[
                'id'=>$v->id,
                'name'=>$v->name,
                'code'=>$v->code,
                'status'=>[
                    'key'=>$v->status,
                    'value'=>$v->status?Yii::t('app','Off'):Yii::t('app','On')
                ],
                'show'=>[
                    'key'=>$v->show,
                    'value'=>$v->show?Yii::t('app','Hide'):Yii::t('app','Display')
                ],
                'parent'=>[
                    'key'=>$v->parent_id,
                    'value'=>$v->parent_id>0?$this->getName($v->parent_id):'—',
                ]
            ];
        }
        return [
            'list'=>$dataProvider,
            'count'=>(int)$count
        ];
    }
}
