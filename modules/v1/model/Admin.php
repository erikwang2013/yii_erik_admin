<?php

namespace app\modules\v1\model;

use Yii;

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
            [['id', 'name','password','password_repeat'], 'required'],
            [['id','status'], 'integer'], 
            ['status','in','range'=>[0,1]],
            [['name'], 'string', 'length' => [4,15]],
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
            'password_repeat'=>Yii::t('app','Repeat Password')
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
}
