<?php

namespace app\modules\v1\controllers;

use Yii,app\modules\v1\model\Admin,
    app\modules\v1\models\AdminSearch,
    yii\web\NotFoundHttpException,
    yii\helpers\ArrayHelper,
    app\common\CheckData,
    app\common\Helper,
    app\common\Snowflake,
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
        $error_page=CheckData::checkPage($page, $limit);
        if ($error_page) {
            return Helper::reset([], 0, 1, $error_page);
        }
        $model = new Admin(['scenario' => 'search']);
        $model->attributes=[
            'name'=>$params['name'],
            'id'=>$params['id']
        ];
        if ($model->validate()) {
            $model_info=new AdminInfo(['scenario' => 'search']);
            $model_info->attributes=[
                'real_name'=>$params['real_name'],
                'email'=>$params['email'],
                'phone'=>$params['phone']
            ];
            if( $model_info->validate()){
                $dataProvider = $model->search($params);
                $result=[];
                $result=ArrayHelper::toArray($dataProvider);
                return Helper::reset($result['list'], $result['count'], 0);
            }
            return Helper::reset([],0,1,CheckData::getValidateError($model_info->errors));
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }


    /**
     * 新增
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post=Yii::$app->request->post();
        $model = new Admin(['scenario' => 'create']);
        $transaction = Admin::getDb()->beginTransaction();
        $post['id']=Helper::getCreateId();
        $model->setPassword($post['password']);
        $admin_arr=[
            'id'=>$post['id'],
            'name'=>$post['name'],
            'password'=>$post['password'],
            'password_repeat'=>$post['password_repeat'],
        ];
        $model->attributes=$admin_arr;
        if ($model->validate()) {
            if ($model->save(false)) {
                    $info=new AdminInfo(['scenario' => 'create']);
                    $info_arr=[
                        'id'=>$post['id'],
                        'sex'=>$post['sex'],
                        'phone'=>$post['phone'],
                        'real_name'=>$post['real_name'],
                        'email'=>$post['email'],
                        'img'=>$post['img'],
                        'create_time'=>date('Y-m-d H:i:s'),
                        'update_time'=>date('Y-m-d H:i:s')
                    ];
                    $info->attributes=$info_arr;
                    if($info->validate()){
                        $info->save(false);
                        $transaction->commit();
                        return Helper::reset([], 0, 0);
                    }
                    $transaction->rollBack();
                    return Helper::reset([],0,1,CheckData::getValidateError($info->errors));
                    
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
        $post=Yii::$app->request->post();
       $admin=new Admin(['scenario' => 'update']);
        $transaction = Admin::getDb()->beginTransaction();
        $admin->attributes=[
                'id'=>$id,
                'name'=> $post['name'],
                'status'=>$post['status']
            ];
        if ($admin->validate()) {
            $model=$this->findModel($id);
            $model->name=$post['name'];
            $model->status=$post['status'];
            if ($model->save(false)) {
               $admin_info=new AdminInfo(['scenario' => 'update']);
               unset($post['name']);unset($post['status']);unset($post['id']);
               $admin_info->attributes=$post;
                if($admin_info->validate()){
                    $info=$this->findInfoModel($id);
                    $info->sex=$post['sex'];
                    $info->phone=$post['phone'];
                    $info->real_name=$post['real_name'];
                    $info->email=$post['email'];
                    $info->img=$post['img'];
                    if($info->save(false)){
                        $transaction->commit();
                       return Helper::reset([], 0, 0);
                    }
                }
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
        $post=Yii::$app->request->post();
        $model=new Admin(['scenario' => 'reset_password']);
        $post['id']=$id;
        $model->attributes=$post;
        if ($model->validate()) {
            $model_reset=$this->findModel($id);
            $model_reset->password=$post['password'];
            if ($model_reset->save(false)) {
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

    protected function findInfoModel($id)
    {
        if (($model = AdminInfo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
