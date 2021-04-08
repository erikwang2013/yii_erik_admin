<?php

namespace app\modules\v1\model;

use Yii,
    yii\data\Pagination,
    app\modules\v1\model\AdminInfo;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property int $id
 * @property string $name 用户名
 * @property string $hash 校验hash
 * @property string $password 密码
 */
class Admin extends \yii\db\ActiveRecord
{
    const SCENARIO_ADMIN_RESET_PASSWORD= 'reset_password';
    const SCENARIO_ADMIN_UPDATE= 'update';
    const SCENARIO_ADMIN_CREATE= 'create';
    const SCENARIO_ADMIN_SEARCH= 'search';
    public $password_repeat;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required','on'=>['create','update']],
            [['name','password','password_repeat'], 'required','on'=>['create']],
            [['id','status'], 'integer'], 
            ['id', 'compare', 'compareValue' => 0, 'operator' => '>'],
            ['status','in','range'=>[0,1]],
            [['name'], 'string', 'length' => [2,15]],
            [['access_token'],'string','max'=>60],
            [['password','password_repeat'],'string','length' => [6, 12]],
            ['password', 'compare', 'compareAttribute' => 'password_repeat','message'=>Yii::t('app','The two passwords are inconsistent')],
            [['id'], 'unique','on'=>['create']],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ADMIN_RESET_PASSWORD => ['password','password_repeat'],
            self::SCENARIO_ADMIN_UPDATE=>['name','status','id'],
            self::SCENARIO_ADMIN_CREATE=>['id','name','password','password_repeat'],
            self::SCENARIO_ADMIN_SEARCH=>['id','name']
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Admin Id'),
            'name' => Yii::t('app', 'Admin Name'),
            'password' => Yii::t('app', 'Password'),
            'password_repeat'=>Yii::t('app','Repeat Password'),
            'status'=>Yii::t('app','Admin Status')
        ];
    }

    public function getAdminInfo(){
        return $this->hasOne(AdminInfo::className(), ['id' => 'id']);  
    }

    public function setPassword($password)
    {
        $this->hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 生成认证key
     */
    public function setToken()
    {
        return $this->access_token = Yii::$app->security->generateRandomString();
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
       
        $query = $this->find();
        $pageSize=$params['limit'];
        $page=$params['page'];
        $table=Admin::tableName();
        $query->andFilterWhere([$table.'.id' =>$params['id']])->andFilterWhere(['like',$table.'.name',$params['name']]);
        $query->joinWith(["adminInfo"=>function($query) use($params){
            $table=AdminInfo::tableName();
           return $query->andFilterWhere( ['like', $table.'.real_name',$params['real_name']])
           ->andFilterWhere(['like', $table.'.phone',$params['phone']])
           ->andFilterWhere(['like', $table.'.email',$params['email']])
           ->andFilterWhere([$table.'.sex' =>$params['sex']]);
        }]);

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
