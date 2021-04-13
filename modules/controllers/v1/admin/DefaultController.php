<?php

namespace app\modules\controllers\v1\admin;

use yii\web\Controller,Yii,
    app\modules\model\v1\admin\Admin,
    app\common\Helper,
    yii\web\Response;


/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    public $enableCsrfValidation = false;
    public $login_admin_id;
    public $login_token;

    public function init()
    {
        parent::init();
    }
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $response = $this->response ? : Yii::$app->getResponse();
        $request = Yii::$app->request;
        if($this->publicUrl($request)){
            $this->checkToken($request);
        }
        return $behaviors;
    }

    //校验是否开放接口
    public function publicUrl($request){
        $url=$request->pathInfo;
        $params_config=Yii::$app->params;
        if(in_array($url,$params_config['public_url'])){
            return false;
        }
        return true;
    }
    //验证token
    public function checkToken($request){
        $authHeader=$request->getHeaders()->get('Authorization');
        $token=$this->getToken($authHeader,$request);
        if ($authHeader !== null && $token) {
            $identity = Admin::findIdentityByAccessToken($token);
            if ($identity === null) {
                $this->resetData(1,Yii::t('app','User authentication is invalid, please login again'));
            }
            return $identity;
        }
        $this->resetData(1,Yii::t('app','Illegal request!'));
    }

    //返回数据设置
    public function resetData($code=1,$msg){
        Yii::$app->response->format=Response::FORMAT_JSON;
        echo Helper::reset([],0,$code,$msg);
        exit;
    }

    //获取token值
    public function getToken($authHeader,$request){
        $token=$request->get('access-token');
        if(is_string($token)){
            return $token;
        }
        preg_match('/^access_token\s+(.*?)$/', $authHeader, $matches);
        if(count($matches)>0){
            return $matches[1];
        }
        preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches);
        if(count($matches)>0){
            return $matches[1];
        }
        preg_match('/^Basic\s+(.*?)$/', $authHeader, $matches);
        if(count($matches)>0){
            return $matches[1];
        }
    }
}
