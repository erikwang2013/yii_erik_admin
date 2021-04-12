<?php

namespace app\modules\v1\controllers;

use Yii,
    app\common\CheckData,
    app\common\Helper,
    app\modules\v1\model\Admin,
    app\modules\v1\validate\AdminValidate,
    app\modules\v1\model\AdminRoleAuthority,
    app\modules\v1\model\AdminAuthority;

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

        //检验验证码
        $code_cache=Helper::getCache($code);
        if(strcmp($code_number,$code_cache)!=0){
            return Helper::reset([],0,1,Yii::t('app','Verification code error'));
        }
        Helper::deleteCache($code);

        //查询用户
        $model_admin=new Admin();
        $table=$model_admin->tableName();
        $query_admin=$model_admin::find();
        
        if (preg_match('/^[1][3456789][0-9]{9}$/',$user_name)) {
            $query_admin->where($table.'.phone=:phone', [':phone'=>$user_name]);
        }elseif(preg_match('/^[a-z0-9A-Z]+[- | a-z0-9A-Z . _]+@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-z]{2,}$/',$user_name)){
            $query_admin->where($table.'.email=:email',[':email'=>$user_name]);
        }else{
            $query_admin->where($table.'.name=:name',[':name'=>$user_name]);
        }
        $model = $query_admin->joinWith("adminInfo")->joinWith(["adminRole"])->one();
        $admin_info=$model->adminInfo;
        $admin_role=$model->adminRole;
        if(!$model){
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
        }
        //校验密码
        if (!Yii::$app->getSecurity()->validatePassword($password, $model->hash)) {
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
        }
        //校验状态
        if($model->status==1){
            return Helper::reset([],0,1,Yii::t('app','Users are not allowed to log in, please contact the administrator'));
        }

        //获取管理员角色id
        if(!isset($admin_role)){
            return Helper::reset([],0,1,Yii::t('app','The user is not assigned permission, please contact the administrator'));
        }
        $role_ids=[];
        foreach($admin_role as $m=>$n){
            $role_ids[]=$n->id;
        }
        unset($admin_role);unset($m);unset($n);
        $role_ids=array_unique($role_ids);

        //查询角色权限关系
        $role_authority=new AdminRoleAuthority();
        $authority_data=$role_authority::find()->where(['in','role_id',$role_ids])->all();
        if(!$authority_data){
            return Helper::reset([],0,1,Yii::t('app',"The user's role does not have permission"));
        }

        $authority_ids=[];
        foreach($authority_data as $key=>$value){
            $authority_ids[]=$value->authority_id;
        }
        unset($authority_data);unset($key);unset($value);
        $authority_ids=array_unique($authority_ids);

        //查询权限
        $authority=new AdminAuthority();
        $authority_list=$authority::find()->where(['in','id',$authority_ids])->all();
        if(!$authority_list){
            return Helper::reset([],0,1,Yii::t('app',"The user's role does not have permission"));
        }

        foreach($authority_list as $k=>$v){
            $authority_list[$k]=[
                'id'=>$v->id,
                'name'=>$v->name,
                'code'=>$v->code,
                'status'=>[
                    'key'=>$v->status,
                    'value'=>$v->status?Yii::t('app','Off'):Yii::t('app','On')
                ],
                'show'=>[
                    'key'=>$v->show,
                    'value'=>$v->show?Yii::t('app','Hide'):Yii::t('app','Display')
                ],
                'parent'=>[
                    'key'=>$v->parent_id,
                    'value'=>$v->parent_id>0?$authority->getName($v->parent_id):'—',
                ]
            ];
        }

        //更新token
        $result_model=Admin::findOne($model->id); 
        $token=base64_encode(md5($result_model->setToken().time()));
        $result_model->access_token=$token;
        $result_model->save(false);
       if (!$result_model) {
            return Helper::reset([],0,1,Yii::t('app','Wrong user name or password.'));
       }
       
       //组合登录用户数据
       $data=[
            'id'=>$result_model->id,
            'sex'=>[
                'key'=>$admin_info->sex,
                'value'=>$admin_info->sex?Yii::t('app','Man'):Yii::t('app','Woman')
            ],
            'user_name'=>$result_model->name,
            'nick_name'=>$result_model->nick_name,
            'phone'=>$result_model->phone,
            'real_name'=>$admin_info->real_name,
            'email'=>$admin_info->email,
            'img'=>$admin_info->img,
            'token'=>$token,
            'authority'=>$authority_list
        ];
        //用户信息存储
        Helper::setCache($token, json_encode($data,true));
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
        Helper::deleteCache($this->login_token);
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