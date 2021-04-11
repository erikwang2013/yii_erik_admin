<?php

namespace app\modules\v1\model;

use Yii,
    yii\data\Pagination,
    app\modules\v1\model\AdminInfo,
    yii\db\ActiveRecord,
    app\common\Helper;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property int $id
 * @property string $name 用户名
 * @property string $hash 校验hash
 * @property string $password 密码
 */

class Admin extends ActiveRecord 
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
            [['id'], 'required','on'=>['create']],
            [['name','password','password_repeat'], 'required','on'=>['create']],
            [['phone'],'match','pattern'=>'/^[1][3456789][0-9]{9}$/'],
            [['id','status'], 'integer'], 
            ['id', 'compare', 'compareValue' => 0, 'operator' => '>'],
            ['status','in','range'=>[0,1]],
            ['email', 'email'],
            [['email'],'string','max'=>60],
            [['name','nick_name'], 'string', 'length' => [2,15]],
            [['access_token'],'string','max'=>200],
            [['password','password_repeat'],'string','length' => [6, 12]],
            ['password', 'compare', 'compareAttribute' => 'password_repeat','message'=>Yii::t('app','The two passwords are inconsistent')],
            [['id','phone','nick_name'], 'unique','on'=>['create','update']],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ADMIN_RESET_PASSWORD => ['password','password_repeat','phone'],
            self::SCENARIO_ADMIN_UPDATE=>['name','status','id','phone','nick_name'],
            self::SCENARIO_ADMIN_CREATE=>['id','name','password','password_repeat','phone','nick_name'],
            self::SCENARIO_ADMIN_SEARCH=>['id','name','phone','nick_name']
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
            'nick_name' => Yii::t('app', 'Nickname'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'password_repeat'=>Yii::t('app','Repeat Password'),
            'status'=>Yii::t('app','Admin Status')
        ];
    }

    public function getAdminInfo(){
        return $this->hasOne(AdminInfo::className(), ['id' => 'id']);  
    }

    public function getAdminRole(){
        return $this->hasMany(AdminRoleInfo::className(),['id'=>'role_id'])
        ->viaTable(AdminRole::tableName(), ['admin_id' => 'id']);  
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

    public static function findIdentityByAccessToken($token, $type = null)
   {
        $admin_info=Helper::getCache($token);
        if(empty($admin_info) ||!isset($admin_info)){
            return static::findOne(['access_token' => $token]);
        }
        return true;
   }
     /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params=[],$page, $limit)
    {
        $query = $this->find();
        $table=$this->tableName();
        $query->andFilterWhere([$table.'.id' =>isset($params['id'])?$params['id']:'']);
        if (preg_match('/^[1][3456789][0-9]{9}$/',$params['name']) && isset($params['name'])) {
            $query->andFilterWhere(['like', $table.'.phone',$params['name']]);
        }elseif(preg_match('/^[a-z0-9A-Z]+[- | a-z0-9A-Z . _]+@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-z]{2,}$/',$params['name'])  && isset($params['name'])){
            $query->andFilterWhere(['like', $table.'.email',$params['name']]);
        }else{
            $query->andFilterWhere(['like',$table.'.name',$params['name']]);
        }

        $query->andFilterWhere(['like', $table.'.nick_name',isset($params['nick_name'])?$params['nick_name']:'']);
        
        $query->joinWith(["adminInfo"=>function($query) use($params){
            $info=AdminInfo::tableName();
           return $query->andFilterWhere( ['like', $info.'.real_name',isset($params['real_name'])?$params['real_name']:''])
           ->andFilterWhere([$info.'.sex' =>isset($params['sex'])?$params['sex']:'']);
        }]);
        $query->joinWith(["adminRole"]);

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
            $admin_info=$v->adminInfo;
            $admin_role=$v->adminRole;
            $role=[];
            if(isset($admin_role)){
                foreach($admin_role as $m=>$n){
                    $role[]=[
                        'id'=>$n->id,
                        'name'=>$n->name
                    ];
                }
            }
            $dataProvider[$k]=[
                'id'=>$v->id,
                'name'=>$v->name,
                'nick_name'=>$v->nick_name,
                'phone'=>$v->phone,
                'email'=>$v->email,
                'role'=>$role,
                'real_name'=>$admin_info->real_name,
                'status'=>[
                    'key'=>$v->status,
                    'value'=>$v->status?Yii::t('app','Off'):Yii::t('app','On')
                ],
                'sex'=>[
                    'key'=>$admin_info->sex,
                    'value'=>$admin_info->sex?Yii::t('app','Man'):Yii::t('app','Woman')
                ],
                'img'=>$admin_info->img,
                'create_time'=>$admin_info->create_time,
                'update_time'=>$admin_info->update_time
            ];
           unset($v->adminInfo);
        }
        return [
            'list'=>$dataProvider,
            'count'=>(int)$count
        ];
    }
}
