<?php

namespace app\modules\v1\model;

use Yii;

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
            [['id', 'name'], 'required'],
            [['id', 'parent_id', 'show', 'status'], 'integer'],
            [['code'], 'string', 'max' => 20],
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
            'id' => Yii::t('app', 'Authority ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'code' => Yii::t('app', 'Authority Code'),
            'name' => Yii::t('app', 'Authority Name'),
            'show' => Yii::t('app', 'Authority Show'),
            'status' => Yii::t('app', 'Authority Status'),
        ];
    }
}
