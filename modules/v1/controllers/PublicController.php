<?php

namespace app\modules\v1\controllers;

use Yii,
    app\common\CheckData,
    app\common\Helper,
    app\modules\v1\model\Admin,
    app\modules\v1\validate\AdminValidate;

/**
 * 公用接口
 *
 * @Author erik
 * @Email erik@erik.xyz
 * @Url https://erik.xyz
 * @DateTime 2021-02-28 15:18:48
 */
class PublicController extends DefaultController
{
    /**
     * 登录
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-28 15:18:31
     * @return void
     */
    public function actionLogin()
    {
        $user_name=Yii::$app->request->get('user_name');
        $password=Yii::$app->request->get('password');
        $code=Yii::$app->request->get('code');
        $code_number=Yii::$app->request->get('code_number');
        $error_login=AdminValidate::checkLogin($user_name,$password,$code,$code_number);
        if($error_login){
            return Helper::reset([],0,1,$error_login);
        }
        $code_cache=Helper::getCache($code);
        if(strcmp($code_number,$code_cache)!=0){
            return Helper::reset([],0,1,Yii::t('app','Verification code error'));
        }
        Helper::deleteCache($code);
        $model = Admin::find()->where('name=:name',[':name'=>$user_name])->joinWith("adminInfo")->one();
        $adminInfo=$model->adminInfo;
        if(!$model){
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
        }
        
        if (!Yii::$app->getSecurity()->validatePassword($password, $model->hash)) {
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
        }
        if($model->status==1){
            return Helper::reset([],0,1,Yii::t('app','Users are not allowed to log in, please contact the administrator'));
        }
        
        $result_model=Admin::findOne($model->id); 
        $token=base64_encode(md5($result_model->setToken().time()));
        $result_model->access_token=$token;
        $result_model->save();
       if (!$result_model) {
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
       }
       $data=[
            'id'=>$model->id,
            'sex'=>[
                'key'=>$adminInfo->sex,
                'value'=>$adminInfo->sex?Yii::t('app','Man'):Yii::t('app','Woman')
            ],
            'user_name'=>$model->name,
            'real_name'=>$adminInfo->real_name,
            'phone'=>$adminInfo->phone,
            'email'=>$adminInfo->email,
            'img'=>$adminInfo->img,
            'token'=>$token
        ];
        $this->login_admin_id=$model->id;
        return Helper::reset($data,0,0);
    }
    /**
     * 登出
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-28 15:18:11
     * @return void
     */
    public function actionLoginOut(){
        $id=Yii::$app->request->get('id');
        $error_id=CheckData::checkId($id);
        if($error_id){
            return Helper::reset([],0,1,$error_id);
        }
        $model=Admin::findOne($id);
        $model->access_token=NULL;
        if($model->save(false)){
            return Helper::reset([],0,0);
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }
    /**
     * 获取验证码
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-28 15:18:38
     * @return void
     */
    public function actionCaptcha()
    {
        $code=Yii::$app->request->get('code');
        $error_code=CheckData::checkCode($code);
        if($error_code){
            return Helper::reset([],0,1,$error_code);
        }
        $captcha=Helper::getCode();
        if(Helper::setCache($code,$captcha['number'],60)){
            return Helper::reset(['code_img'=>$captcha['img']],0,0);
        }
        return Helper::reset([],0,1,Yii::t('app','Verification code acquisition failed'));
    }


}