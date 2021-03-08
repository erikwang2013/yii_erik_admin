<?php

namespace app\modules\v1\controllers;

use Yii,app\modules\v1\model\Admin,
    app\modules\v1\models\AdminSearch,
    yii\web\NotFoundHttpException,
    yii\helpers\ArrayHelper,
    app\common\CheckData,
    app\common\Helper,
    yii\filters\Cors,
    app\modules\v1\model\AdminInfo,
    app\modules\v1\validate\AdminValidate;

/**
 * AdminController implements the CRUD actions for Admin model.
 */
class AdminController extends DefaultController
{

    public function behaviors()
    {
        $controller=Yii::$app->controller->id;
       $config=Yii::$app->params['controller_cors'];
       $config_data=$config[$controller];
        return ArrayHelper::merge([
            [
                'class' => Cors::className(),
                'cors' => [
                            'Origin' =>$config_data['cors']['origin'],                  //允许来源的数组
                            'Access-Control-Request-Method' =>$config_data['cors']['request'],     //允许动作
                ],
                // 'actions' => [
                //    $config_data['actions']
                // ]
            ],
        ], parent::behaviors());
    }

    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params=Yii::$app->request->queryParams;
        $params_config=Yii::$app->params;
        $page=Yii::$app->request->get('page')?:$params_config['page'];
        $limit=Yii::$app->request->get('limit')?:$params_config['limit'];
        $error_page=CheckData::checkPage($page,$limit);
        if($error_page){
            return Helper::reset([],0,1,$error_page);
        }
        unset($params['page']); unset($params['limit']);
        $id=Yii::$app->request->get('id');

        $name=Yii::$app->request->get('name');
        $real_name=Yii::$app->request->get('real_name');
        $phone=Yii::$app->request->get('phone');
        $email=Yii::$app->request->get('email');
        $error_keyword=AdminValidate:: checkAdminKeyword($name,$real_name,$phone,$email);
        if ($error_keyword) {
            return Helper::reset([],0,1,$error_keyword);
        }
        $searchModel = new AdminSearch();
            $dataProvider = $searchModel->search($params);
            $result=[];
            $result=ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'],$result['count'],0);
       
    }


    /**
     * 新增
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post=Yii::$app->request->post();
        $model = new Admin();
        $transaction = Admin::getDb()->beginTransaction();
        $post['id']=Helper::getCreateId();
        $model->setPassword($post['password']);
        $model->attributes=$post;
        if ($model->validate()) {
            if ($model->save()) {
                    $info=new AdminInfo();
                    $info_arr['id']=$post['id'];
                    if(isset($post['sex'])) $info_arr['sex']=$post['sex'];
                    if(isset($post['phone']) && empty($post['phone'])) $info_arr['phone']=$post['phone'];
                    if(isset($post['img']) && empty($post['img'])) $info_arr['img']=$post['img'];
                    if(isset($post['real_name']) && empty($post['real_name'])) $info_arr['real_name']=$post['real_name'];
                    if(isset($post['email']) && empty($post['email'])) $info_arr['email']=$post['email'];
                    $info->attributes=$info_arr;
                    if($info->validate()){
                        $info->save();
                        $transaction->commit();
                        return Helper::reset([], 0, 0);
                    }
                    $transaction->rollBack();
            }
        }
            $transaction->rollBack();
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }

    /**
     * 更新
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-24 21:38:20
     * @param [type] $id
     * @return void
     */
    public function actionUpdate($id){
        $check_data=CheckData::checkId($id);
        if($check_data){
            return Helper::reset([],0,1,$check_data);
        }
        $model=$this->findModel($id);
        $transaction = Admin::getDb()->beginTransaction();
        $post['name']=empty(Yii::$app->request->post('name')) ? $model->name : Yii::$app->request->post('name');
        $model->attributes=$post;
        if ($model->validate()) {
            if ($model->save()) {
                $info=new AdminInfo();
                $info_arr['id']=$id;
                if(isset($post['sex'])) $info_arr['sex']=$post['sex'];
                if(isset($post['phone']) && empty($post['phone'])) $info_arr['phone']=$post['phone'];
                if(isset($post['img']) && empty($post['img'])) $info_arr['img']=$post['img'];
                if(isset($post['real_name']) && empty($post['real_name'])) $info_arr['real_name']=$post['real_name'];
                if(isset($post['email']) && empty($post['email'])) $info_arr['email']=$post['email'];
                $info->attributes=$info_arr;
                if($info->validate()){
                    $info->save();
                    $transaction->commit();
                    return Helper::reset([], 0, 0);
                }
                $transaction->rollBack();
            }
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }
    /**
     * Deletes an existing Admin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if(empty($id))  return Helper::reset([],0,1,Yii::t('app','Request parameter cannot be empty!'));
        $id=explode(',',$id);
        foreach($id as $k=>$v){
            $check_data=CheckData::checkId($v);
            if($check_data){
                return Helper::reset([],0,1,$check_data);
            }
        }
        $model = new Admin();
        if($model->deleteAll(['id'=>$id])){
            $info=new AdminInfo();
            $info->deleteAll(['id'=>$id]);
        }
        return Helper::reset([],0,0);
    }

        /**
         * 重置密码
         *
         * @Author erik
         * @Email erik@erik.xyz
         * @Url https://erik.xyz
         * @DateTime 2021-02-23 21:18:36
         * @return void
         */
    public function actionResetPassword($id){
        $check_id=CheckData::checkId($id);
        if($check_id){
            return Helper::reset([],0,1,$check_id);
        }
        $post=Yii::$app->request->post();
        $model=$this->findModel($id);
        $model->setPassword($post['password']);
        $model->attributes=$post;
        if ($model->validate()) {
            if ($model->save()) {
                return Helper::reset([], 0, 0);
            }
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }
    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Admin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Admin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
