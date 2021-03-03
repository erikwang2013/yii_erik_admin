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
            [['id'], 'required'],
            [['id', 'sex'], 'integer'],
            [['img','phone'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['real_name', 'email'], 'string', 'max' => 100],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => 'app\modules\v1\models\AdminInfo','message'=>Yii::t('app','Email already exists')],
            ['phone', 'unique', 'targetClass' => 'app\modules\v1\models\AdminInfo','message'=>Yii::t('app','Mobile number already exists')],
            [['id'], 'unique'],
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
