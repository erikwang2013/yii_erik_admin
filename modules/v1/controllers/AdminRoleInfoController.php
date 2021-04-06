<?php

namespace app\modules\v1\controllers;

use Yii,
    app\modules\v1\model\AdminRoleInfo,
    app\modules\v1\controllers\DefaultController,
    yii\web\NotFoundHttpException,
    app\common\CheckData,
    app\common\Helper,
    yii\helpers\ArrayHelper,
    yii\filters\Cors,
    app\modules\v1\model\AdminRoleAuthority;

/**
 * AdminRoleInfoController implements the CRUD actions for AdminRoleInfo model.
 */
class AdminRoleInfoController extends DefaultController
{


    /**
     * Lists all AdminRoleInfo models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        $params_config=Yii::$app->params;
        $params['page']=Yii::$app->request->get('page',$params_config['page']);
        $params['limit']=Yii::$app->request->get('limit',$params_config['limit']);
        $error_page=CheckData::checkPage($params['page'],$params['limit']);
        if($error_page){
            return Helper::reset([],0,1,$error_page);
        }
        $model = new AdminRoleInfo(['scenario' => 'search']);
        $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
        $data=[];
        foreach($params as $name=>$value){
            if (isset($attributes[$name])) {
                $data[$name]=$value;
            }else{
                return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
            }
        }
        $model->attributes=$data;
        if ($model->validate()) {
            $dataProvider = $model->search($params);
            $result=ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'],$result['count'],0);
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }

    /**
     * Creates a new AdminRoleInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post=Yii::$app->request->post();
        $authority_id=Yii::$app->request->post('authority_ids');
        if (isset($authority_id)) {
            $authority_ids=explode(',', $authority_id);
            foreach ($authority_ids as $k=>$v) {
                $check_data=CheckData::checkId($v, Yii::t('app', 'Permission ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['authority_ids']);
        }
        $model = new AdminRoleInfo(['scenario' => 'create']);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $post['id']=Helper::getCreateId();
        $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
        $data=[];
        foreach($post as $name=>$value){
            if (isset($attributes[$name])) {
                $data[$name]=$value;
            }else{
                return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
            }
        }
        $model->attributes=$data;
        if ($model->validate()) {
            if ($model->save(false)) {
                if (isset($authority_id)) {
                    $authority=AdminRoleAuthority::tableName();
                    $insert=[];
                    foreach ($authority_ids as $m=>$n) {
                        $insert[]=[
                        'role_id'=>$post['id'],
                        'authority_id'=>$n
                    ];
                    }
                    Yii::$app->db->createCommand()->batchInsert($authority, ['role_id', 'authority_id'], $insert)->execute();
                }
                $transaction->commit();
                return Helper::reset([], 0, 0);
            }
            $transaction->rollBack();
            return Helper::reset([],0,1,$model->errors);
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }

    /**
     * Updates an existing AdminRoleInfo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $authority_id=Yii::$app->request->post('authority_ids');
        $post=Yii::$app->request->post();
        if (isset($authority_id)) {
            $authority_ids=explode(',', $authority_id);
            foreach ($authority_ids as $k=>$v) {
                $check_data=CheckData::checkId($v, Yii::t('app', 'Permission ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['authority_ids']);
        }
        
        
        if(count($post)==0){
            return Helper::reset([],0,1,Yii::t('app','Update at least one data'));
        }
        $model = new AdminRoleInfo(['scenario' => 'update']);
        $update_data=$post;
        $post['id']=$id;
        $model->attributes=$post;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        if ($model->validate()) {
            $update = $this->findModel($id);
            $attributes = array_flip($update->safeAttributes() ? $update->safeAttributes() : $update->attributes());
            foreach($update_data as $name=>$value){
                if (isset($attributes[$name])) {
                    $update->$name=$value;
                }else{
                    $transaction->rollBack();
                    return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
                }
            }
            
            if ($update->save(false)) {
                    //删除角色权限关系
                $authority=new AdminRoleAuthority();
                if($authority->deleteAll(['role_id'=>$id])){
                    if (isset($authority_id)) {
                        $insert=[];
                        foreach ($authority_ids as $m=>$n) {
                            $insert[]=[
                                'role_id'=>$id,
                                'authority_id'=>$n
                            ];
                        }
                        $authority_table=AdminRoleAuthority::tableName();
                        Yii::$app->db->createCommand()->batchInsert($authority_table, ['role_id', 'authority_id'], $insert)->execute();
                    }
                    $transaction->commit();
                    return Helper::reset([], 0, 0);
                }
            }
            $transaction->rollBack();
            return Helper::reset([],0,1,$update->errors);
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }

    /**
     * Deletes an existing AdminRoleInfo model.
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
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $model = new AdminRoleInfo();
        if($model->deleteAll(['id'=>$id])){
            $authority=new AdminRoleAuthority();
            if ($authority->deleteAll(['role_id'=>$id])) {
                $transaction->commit();
                return Helper::reset([], 0, 0);
            }
        }
        $transaction->rollBack();
        return Helper::reset([],0,1,$model->errors);
    }

    /**
     * Finds the AdminRoleInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdminRoleInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AdminRoleInfo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
