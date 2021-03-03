<?php

namespace app\modules\v1\model;

use Yii;

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
            [['id', 'name'], 'required'],
            [['id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique'],
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
}
