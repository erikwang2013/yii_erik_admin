<?php

namespace app\modules\v1\controllers;

use Yii,
    app\modules\v1\model\AdminAuthority,
    app\modules\v1\controllers\DefaultController,
    yii\web\NotFoundHttpException,
    app\common\CheckData,
    app\common\Helper,
    yii\helpers\ArrayHelper,
    yii\filters\Cors;

/**
 * AdminAuthorityController implements the CRUD actions for AdminAuthority model.
 */
class AdminAuthorityController extends DefaultController
{
    /**
     * {@inheritdoc}
     */
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
     * Lists all AdminAuthority models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params_config=Yii::$app->params;
        $params=[
            'id'=>Yii::$app->request->get('id'),
            'name'=>Yii::$app->request->get('name',''),
            'parent_id'=>Yii::$app->request->get('parent_id'),
            'show'=>Yii::$app->request->get('show'),
            'status'=>Yii::$app->request->get('status'),
            'code'=>Yii::$app->request->get('code',''),
            'page'=>Yii::$app->request->get('page',$params_config['page']),
            'limit'=>Yii::$app->request->get('limit',$params_config['limit'])
        ];
        $error_page=CheckData::checkPage($params['page'],$params['limit']);
        if($error_page){
            return Helper::reset([],0,1,$error_page);
        }
        $model = new AdminAuthority(['scenario' => 'search']);
        $model->attributes=[
            'id'=>$params['id'],
            'name'=>$params['name'],
            'parent_id'=>$params['parent_id'],
            'show'=>$params['show'],
            'status'=>$params['status'],
        ];
        $result=[];
        if ($model->validate()) {
            $dataProvider = $model->search($params);
            $result=ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'],$result['count'],0);
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }


    /**
     * Creates a new AdminAuthority model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post=Yii::$app->request->post();
        $post['parent_id']=Yii::$app->request->post('parent_id',0);
        $model = new AdminAuthority(['scenario' => 'create']);
        $post['id']=Helper::getCreateId();
        $model->attributes=[
            'id'=>$post['id'],
            'parent_id'=>$post['parent_id'],
            'code'=>$post['code'],
            'name'=>$post['name'],
            'show'=>$post['show'],
            'status'=>$post['status'],
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
     * Updates an existing AdminAuthority model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $params=Yii::$app->request->post();
        if(count($params)==0){
            return Helper::reset([],0,1,Yii::t('app','Update at least one data'));
        }
        $post=[
            'parent_id'=>Yii::$app->request->post('parent_id'),
            'code'=>Yii::$app->request->post('code'),
            'name'=>Yii::$app->request->post('name'),
            'show'=>Yii::$app->request->post('show'),
            'status'=>Yii::$app->request->post('status'),
        ];

        $model = new AdminAuthority(['scenario' => 'update']);
        $model->attributes=[
            'id'=>$id,
            'parent_id'=>$post['parent_id'],
            'code'=>$post['code'],
            'name'=>$post['name'],
            'show'=>$post['show'],
            'status'=>$post['status'],
        ];
        if ($model->validate()) {
            $update = $this->findModel($id);
            if(isset($post['parent_id'])) $update ->parent_id=$post['parent_id'];
            if(isset($post['name'])) $update ->name=$post['name'];
            if(isset($post['code'])) $update ->code=$post['code'];
            if(isset($post['show'])) $update ->show=$post['show'];
            if(isset($post['status'])) $update ->status=$post['status'];
            if ($update->save(false)) {
                return Helper::reset([], 0, 0);
            }
            return Helper::reset([],0,1,$update->errors);
        }
        return Helper::reset([],0,1,CheckData::getValidateError($model->errors));
    }

    /**
     * Deletes an existing AdminAuthority model.
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
        $model = new AdminAuthority();
        if($model->deleteAll(['id'=>$id])){
            return Helper::reset([],0,0);
        }
        return Helper::reset([],0,1,$model->errors);
    }

    /**
     * Finds the AdminAuthority model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdminAuthority the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AdminAuthority::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
