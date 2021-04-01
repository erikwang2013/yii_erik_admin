<?php

namespace app\modules\v1\controllers;

use Yii,
    app\modules\v1\model\AdminRoleInfo,
    app\modules\v1\controllers\DefaultController,
    yii\web\NotFoundHttpException,
    app\common\CheckData,
    app\common\Helper,
    yii\helpers\ArrayHelper,
    yii\filters\Cors;

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
        $params=[
            'id'=>Yii::$app->request->get('id'),
            'name'=>Yii::$app->request->get('name',''),
            'status'=>Yii::$app->request->get('status'),
            'page'=>Yii::$app->request->get('page',$params_config['page']),
            'limit'=>Yii::$app->request->get('limit',$params_config['limit'])
        ];
        $error_page=CheckData::checkPage($params['page'],$params['limit']);
        if($error_page){
            return Helper::reset([],0,1,$error_page);
        }
        $model = new AdminRoleInfo(['scenario' => 'search']);
        $model->attributes=[
            'id'=>$params['id'],
            'name'=>$params['name'],
            'status'=>$params['status'],
        ];
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
        $model = new AdminRoleInfo(['scenario' => 'create']);
        $post['id']=Helper::getCreateId();
        $model->attributes=[
            'id'=>$post['id'],
            'name'=>$post['name'],
            'status'=>$post['status'],
            'create_time'=>date('Y-m-d H:i:s')
        ];
        if ($model->validate()) {
            if ($model->save(false)) {
                return Helper::reset([], 0, 0);
            }
            return Helper::reset([],0,1,$model->errors);
        }
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
        $post=Yii::$app->request->post();
        if(count($post)==0){
            return Helper::reset([],0,1,Yii::t('app','Update at least one data'));
        }
        $model = new AdminRoleInfo(['scenario' => 'update']);
        $update_data=$post;
        $post['id']=$id;
        $model->attributes=$post;
        if ($model->validate()) {
            $update = $this->findModel($id);
            $attributes = array_flip($update->safeAttributes() ? $update->safeAttributes() : $update->attributes());
            foreach($update_data as $name=>$value){
                if (isset($attributes[$name])) {
                    $update->$name=$value;
                }else{
                    return Helper::reset([$name=>$value],0,1,Yii::t('app','Illegal request!'));
                }
            }
            if ($update->save(false)) {
                return Helper::reset([], 0, 0);
            }
            return Helper::reset([],0,1,$update->errors);
        }
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
        $model = new AdminRoleInfo();
        if($model->deleteAll(['id'=>$id])){
            return Helper::reset([],0,0);
        }
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
