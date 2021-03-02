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
        //$validator=new DynamicModel();
        $validator=new DynamicModel(compact('page', 'limit'));
        $attribute=[
            'page'=>Yii::t('app', 'Page'),
            'limit'=>Yii::t('app','Limit')
        ];
        $validator->setAttributeLabels($attribute)
        ->addRule(['page'],'integer')
        ->addRule(['page'],'required')
        ->addRule(['limit'],'integer')
        ->addRule(['limit'],'required')
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

    public static function checkId($id){
        $validator=DynamicModel::validateData(compact('id'),
            [
                ['id','integer'],
                ['id','required']
            ]
        );
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

    public static function checkPassword($password){
        $validator=new DynamicModel(compact('password'));
        $validator->setAttributeLabels(['password'=>Yii::t('app', 'Password')])
        ->addRule(['password'],'required')
        ->addRule(['password'],'filter',[ 'filter' => 'trim'])
        ->addRule(['password'],'string',['length' => [6, 12]])
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
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
    public static function checkLogin($user_name,$password,$code,$code_number){
        $validator=new DynamicModel(compact('user_name','password','code','code_number'));
        $validator->setAttributeLabels([
            'password'=>Yii::t('app', 'Password'),
            'user_name'=>Yii::t('app','User Name'),
            'code'=>Yii::t('app','Salt Code'),
            'code_number'=>Yii::t('app','Verification Code')])
        ->addRule(['password'],'required')
        ->addRule(['password'],'filter',[ 'filter' => 'trim'])
        ->addRule(['password'],'string',['length' => [6, 12]])
        ->addRule(['user_name'],'required')
        ->addRule(['user_name'],'filter',[ 'filter' => 'trim'])
        ->addRule(['user_name'],'string',['length' => [4, 15]])
        ->addRule(['code'],'required')
        ->addRule(['code'],'filter',[ 'filter' => 'trim'])
        ->addRule(['code'],'string',['length' => [4, 10]])
        ->addRule(['code_number'],'required')
        ->addRule(['code_number'],'filter',[ 'filter' => 'trim'])
        ->addRule(['code_number'],'string',['max' =>4])
        ->validate();
        if($validator->hasErrors()){
            return self::getValidateError($validator->errors);
        }
        return false;
    }

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