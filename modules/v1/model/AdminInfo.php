<?php

namespace app\modules\v1\model;

use Yii;

/**
 * This is the model class for table "{{%admin_info}}".
 *
 * @property int $admin_id
 * @property string $real_name 姓名
 * @property int $sex 性别 0=女 1=男
 * @property int $phone 手机号
 * @property string $email 邮箱
 * @property string $img 头像
 * @property string $create_time
 * @property string $update_time
 */
class AdminInfo extends \yii\db\ActiveRecord
{
    const SCENARIO_ADMIN_INFO_UPDATE='update';
    const SCENARIO_ADMIN_INFO_CREATE='create';
    const SCENARIO_ADMIN_INFO_SEARCH='search';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','email','phone'], 'unique','on'=>['create','update']],
            [['id'], 'required','on'=>['create','update']],
            [['sex'], 'in','range'=>[0,1]],
            [['id','sex'], 'integer'],
            [['phone'],'match','pattern'=>'/^[1][345678][0-9]{9}$/'],
            [['img'], 'string','max'=>200],
            [['real_name'],'string','max'=>18],
            ['email', 'email'],
            [['email'],'string','max'=>60],
            [['create_time', 'update_time'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return [
            self::SCENARIO_ADMIN_INFO_UPDATE=>['sex','phone','real_name','email','img'],
            self::SCENARIO_ADMIN_INFO_CREATE=>['id','sex','phone','real_name','email','img','create_time','update_time'],
            self::SCENARIO_ADMIN_INFO_SEARCH=>['real_name','phone','email','sex']
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Admin Id'),
            'real_name' => Yii::t('app', 'Real Name'),
            'sex' => Yii::t('app', 'Sex'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'img' => Yii::t('app', 'Header Img'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }
}
