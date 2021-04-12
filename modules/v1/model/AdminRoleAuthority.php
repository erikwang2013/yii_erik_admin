<?php

namespace app\modules\v1\model;

use Yii;

/**
 * This is the model class for table "{{%admin_role_authority}}".
 *
 * @property int $role_id 角色id
 * @property int $authority_id 权限id
 */
class AdminRoleAuthority extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_role_authority}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'authority_id'], 'required'],
            [['role_id', 'authority_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role_id' => Yii::t('app', 'Role ID'),
            'authority_id' => Yii::t('app', 'Authority ID'),
        ];
    }
}
