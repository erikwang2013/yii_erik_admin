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


    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params_config=Yii::$app->params;
        $params=[
            'id'=>Yii::$app->request->get('id'),
            'name'=>Yii::$app->request->get('name',''),
            'real_name'=>Yii::$app->request->get('real_name',''),
            'phone'=>Yii::$app->request->get('phone',''),
            'email'=>Yii::$app->request->get('email',''),
            'sex'=>Yii::$app->request->get('sex'),
            'page'=>Yii::$app->request->get('page',$params_config['page']),
            'limit'=>Yii::$app->request->get('limit',$params_config['limit'])
        ];
        $error_page=CheckData::checkPage($params['page'], $params['limit']);
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
                'phone'=>$params['phone'],
                'sex'=>$params['sex']
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
        $model->attributes=[
            'id'=>$post['id'],
            'name'=>$post['name'],
            'password'=>$post['password'],
            'password_repeat'=>$post['password_repeat'],
        ];
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
                       if($info->save(false)){
                            $transaction->commit();
                            return Helper::reset([], 0, 0);
                       }
                       $transaction->rollBack();
                        return Helper::reset([],0,1,$info->errors);
                    }
                    $transaction->rollBack();
                    return Helper::reset([],0,1,CheckData::getValidateError($info->errors));
            }
            $transaction->rollBack();
            return Helper::reset([],0,1,$model->errors);
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
        $post=Yii::$app->request->post();
        if(count($post)==0){
            return Helper::reset([],0,1,Yii::t('app','Update at least one data'));
        }
       $admin=new Admin(['scenario' => 'update']);
        $transaction = Admin::getDb()->beginTransaction();
        $update_data=$post;
        $post['id']=$id;
        $admin->attributes=$post;
        if ($admin->validate()) {
            $model=$this->findModel($id);
            $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
            foreach($update_data as $name=>$value){
                if (isset($attributes[$name])) {
                    $model->$name=$value;
                }else{
                    return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
                }
            }
            if ($model->save(false)) {
                //更新用户详情
                unset($post['name']);unset($post['status']);
                if(count($post)==0){
                    $transaction->commit();
                    return Helper::reset([], 0, 0);
                }
               $admin_info=new AdminInfo(['scenario' => 'update']);
               $admin_info->attributes=$post;
                if($admin_info->validate()){
                    $info=$this->findInfoModel($id);
                    unset($attributes);
                    $attributes = array_flip($info->safeAttributes() ? $info->safeAttributes() : $info->attributes());
                    unset($update_data['name']);unset($update_data['status']);
                    foreach($update_data as $info_name=>$info_value){
                        if (isset($attributes[$info_name])) {
                            $info->$info_name=$info_value;
                        }else{
                            return Helper::reset([$info_name=>$info_value],0,1,Yii::t('app','Illegal request!'));
                        }
                    }
                    if($info->save(false)){
                        $transaction->commit();
                       return Helper::reset([], 0, 0);
                    }
                    $transaction->rollBack();
                    return Helper::reset([],0,1,$info->errors);
                }
                $transaction->rollBack();
                return Helper::reset([],0,1,CheckData::getValidateError($admin_info->errors));
            }
            $transaction->rollBack();
            return Helper::reset([],0,1,$model->errors);
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,CheckData::getValidateError($admin->errors));
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
        $id=explode(',',$id);
        foreach($id as $k=>$v){
            $check_data=CheckData::checkId($v);
            if($check_data){
                return Helper::reset([],0,1,$check_data);
            }
        }
        $model = new Admin();
        $transaction = Admin::getDb()->beginTransaction();
        if($model->deleteAll(['id'=>$id])){
            $info=new AdminInfo();
            if($info->deleteAll(['id'=>$id])){
                $transaction->commit();
                return Helper::reset([],0,0);
            }
            $transaction->rollBack();
            return Helper::reset([],0,1,$info->errors);
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,$model->errors);
        
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
