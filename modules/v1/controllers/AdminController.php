<?php

namespace app\modules\v1\controllers;

use Yii,app\modules\v1\model\Admin,
    app\modules\v1\models\AdminSearch,
    yii\web\NotFoundHttpException,
    yii\helpers\ArrayHelper,
    app\common\CheckData,
    app\common\Helper,
    app\modules\v1\model\AdminInfo,
    app\modules\v1\model\AdminRole;

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
        $role_id=Yii::$app->request->post('role_ids');
        $post=Yii::$app->request->post();
        if (isset($role_id)) {
            $role_ids=explode(',', $role_id);
            foreach ($role_ids as $k=>$v) {
                $check_data=CheckData::checkId($v, Yii::t('app', 'Role ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['role_ids']);
        }
        
        $model = new Admin(['scenario' => 'create']);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
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
                unset($post['name']);unset($post['password']);unset($post['password_repeat']);
                $info->attributes=$post;
                if($info->validate()){
                    if($info->save(false)){
                        if (isset($role_id)) {
                            $insert=[];
                            foreach ($role_ids as $m=>$n) {
                                $insert[]=[
                                    'admin_id'=>$post['id'],
                                    'role_id'=>$n
                                ];
                            }
                            $table=AdminRole::tableName();
                            Yii::$app->db->createCommand()->batchInsert($table, ['admin_id', 'role_id'], $insert)->execute();
                        }
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
        $role_id=Yii::$app->request->post('role_ids');
        $post=Yii::$app->request->post();
        if (isset($role_id)) {
            $role_ids=explode(',', $role_id);
            foreach ($role_ids as $k=>$v) {
                $check_data=CheckData::checkId($v, Yii::t('app', 'Role ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['role_ids']);
        }
        if(count($post)==0){
            return Helper::reset([],0,1,Yii::t('app','Update at least one data'));
        }
        $post['id']=$id;
       $admin=new Admin(['scenario' => 'update']);
       $db = Yii::$app->db;
       $transaction = $db->beginTransaction();
        $update_data=$post;
        $admin->attributes=$post;
        if ($admin->validate()) {
            $model=$this->findModel($post['id']);
            $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
            foreach($update_data as $name=>$value){
                if (isset($attributes[$name])) {
                    $model->$name=$value;
                }else{
                    return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
                }
            }
            //保存用户基本信息
            if ($model->save(false)) {
                        //更新用户详情
                unset($post['name']);unset($post['status']);
                if(count($post)==0){
                    $transaction->commit();
                    return Helper::reset([], 0, 0);
                }
                $admin_info=new AdminInfo(['scenario' => 'update']);
                $admin_info->attributes=$post;
                //校验用户详情
                if($admin_info->validate()){
                    $info=$this->findInfoModel($post['id']);
                    unset($attributes);
                    $attributes = array_flip($info->safeAttributes() ? $info->safeAttributes() : $info->attributes());
                    unset($post['name']);unset($post['status']);
                    foreach($post as $info_name=>$info_value){
                        if (isset($attributes[$info_name])) {
                            $info->$info_name=$info_value;
                        }else{
                            return Helper::reset([$info_name=>$info_value],0,1,Yii::t('app','Illegal request!'));
                        }
                    }
                    //保存用户详情
                    if($info->save(false)){
                        //删除用户角色
                        $admin_role=new AdminRole();
                        if ($admin_role->deleteAll(['admin_id'=>$post['id']])) {
                            if (isset($role_id)) {
                                //新增用户角色
                                $insert=[];
                                foreach ($role_ids as $m=>$n) {
                                    $insert[]=[
                                            'admin_id'=>$post['id'],
                                            'role_id'=>$n
                                        ];
                                }
                                $table=AdminRole::tableName();
                                Yii::$app->db->createCommand()->batchInsert($table, ['admin_id', 'role_id'], $insert)->execute();
                            }
                        }
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
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        if($model->deleteAll(['id'=>$id])){
            $info=new AdminInfo();
            if($info->deleteAll(['id'=>$id])){
                $admin_role=new AdminRole();
                if ($admin_role->deleteAll(['admin_id'=>$id])) {
                    $transaction->commit();
                    return Helper::reset([], 0, 0);
                }
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
