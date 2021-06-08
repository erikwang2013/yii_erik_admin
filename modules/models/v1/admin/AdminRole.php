<?php

namespace app\modules\models\v1\admin;

use Yii;

/**
 * This is the model class for table "{{%admin_role}}".
 *
 * @property int $admin_id 用户id
 * @property int $role_id 角色id
 */
class AdminRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'role_id'], 'required'],
            [['admin_id', 'role_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => Yii::t('app', 'Admin ID'),
            'role_id' => Yii::t('app', 'Role ID'),
        ];
    }
}
