<?php
namespace app\common;

use Yii,yii\base\DynamicModel ;

class CheckData
{
  /**
    * 获取验证返回报错并重新格式化
    *
    * @Author erik
    * @Email erik@erik.xyz
    * @Url https://erik.xyz
    * @DateTime 2021-02-21 23:23:22
    * @param [type] $data
    * @return void
    */
    public static function getValidateError($data){
        $msg='';
        foreach($data as $k=>$v){
            $msg=$v[0];
        }
       return $msg;
    }

    /**
     * 验证分页及数量
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-21
     * @param [type] $page
     * @param [type] $limit
     * @return void
     */
    public static function checkPage($page,$limit){
        $validator=new DynamicModel(compact('page', 'limit'));
        $attribute=[
            'page'=>Yii::t('app', 'Page'),
            'limit'=>Yii::t('app','Limit')
        ];
        $validator->setAttributeLabels($attribute)
        ->addRule(['page','limit'],'required')
        ->addRule(['limit','page'],'integer')
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

    /**
     * 验证管理员id
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-05 00:11:15
     * @param [type] $id
     * @return void
     */
    public static function checkId($id,$msg=''){
        $validator=new DynamicModel(compact('id'));
        $attribute=[
            'id'=>empty($msg)?Yii::t('app','Admin Id'):$msg
        ];
        $validator->setAttributeLabels($attribute)
        ->addRule(['id'],'integer')
        ->addRule(['id'],'required')
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

   

    /**
     * 验证密码
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-05 00:11:41
     * @param [type] $password
     * @return void
     */
    public static function checkPassword($password,$password_repeat){
        $validator=new DynamicModel(compact('password','password_repeat'));
        $validator->setAttributeLabels(['password'=>Yii::t('app', 'Password'),'password_repeat'=>Yii::t('app','Password Repeat')])
        ->addRule(['password','password_repeat'],'required')
        ->addRule(['password','password_repeat'],'filter',[ 'filter' => 'trim'])
        ->addRule(['password','password_repeat'],'string',['length' => [6, 12]])
        ->addRule('password','compare',['compareAttribute' => 'password_repeat'])
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

    

    /**
     * 验证码校验
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-05 00:11:59
     * @param [type] $code
     * @return void
     */
    public static function checkCode($code){
        $validator=new DynamicModel(compact('code'));
        $validator->setAttributeLabels(['code'=>Yii::t('app','Salt Code')])
        ->addRule(['code'],'required')
        ->addRule(['code'],'filter',[ 'filter' => 'trim'])
        ->addRule(['code'],'string',['length' => [4, 10]])
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }
}