<?php

namespace app\modules\validate\v1\admin;

use Yii,
    yii\base\DynamicModel,
    app\common\CheckData;

class AdminValidate
{


    /**
     * 验证管理员搜索条件
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-05 00:11:26
     * @param string $name
     * @param string $real_name
     * @param string $phone
     * @param string $email
     * @return void
     */
    public static function checkAdminKeyword($name, $real_name, $phone, $email)
    {
        $validator = new DynamicModel(compact('name', 'real_name', 'phone', 'email'));
        $attribute = [
            'name' => Yii::t('app', 'Admin Name'),
            'real_name' => Yii::t('app', 'Real Name'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
        ];
        $validator->setAttributeLabels($attribute)
            ->addRule(['name', 'real_name', 'phone', 'email'], 'default', ['value' => null])
            ->addRule(['name', 'real_name'], 'string', ['max' => 18])
            ->addRule(['phone'], 'match', ['pattern' => '/^[1][345678][0-9]{9}$/'])
            ->addRule(['phone'], 'string', ['max' => 11])
            ->addRule(['email'], 'string', ['max' => 60])
            ->addRule(['email'], 'email')
            ->validate();
        if ($validator->hasErrors()) {
            return CheckData::getValidateError($validator->errors);
        }
        return false;
    }

    /**
     * 登录校验
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-28 17:24:51
     * @param [type] $user_name
     * @param [type] $password
     * @param [type] $code
     * @param [type] $img_code
     * @return void
     */
    public static function checkLogin($user_name, $password, $code, $code_number)
    {
        $validator = new DynamicModel(compact('user_name', 'password', 'code', 'code_number'));
        $validator->setAttributeLabels([
            'password' => Yii::t('app', 'Password'),
            'user_name' => Yii::t('app', 'User Name'),
            'code' => Yii::t('app', 'Salt Code'),
            'code_number' => Yii::t('app', 'Verification Code')
        ])
            ->addRule(['password'], 'required')
            ->addRule(['password'], 'filter', ['filter' => 'trim'])
            ->addRule(['password'], 'string', ['length' => [6, 12]])
            ->addRule(['user_name'], 'required')
            ->addRule(['user_name'], 'filter', ['filter' => 'trim'])
            ->addRule(['user_name'], 'string', ['length' => [4, 15]])
            ->addRule(['code'], 'required')
            ->addRule(['code'], 'filter', ['filter' => 'trim'])
            ->addRule(['code'], 'string', ['length' => [4, 10]])
            ->addRule(['code_number'], 'required')
            ->addRule(['code_number'], 'filter', ['filter' => 'trim'])
            ->addRule(['code_number'], 'string', ['max' => 4])
            ->validate();
        if ($validator->hasErrors()) {
            return CheckData::getValidateError($validator->errors);
        }
        return false;
    }
}
