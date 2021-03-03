<?php

namespace app\modules\v1\controllers;

use Yii,
    app\modules\v1\model\AdminAuthority,
    app\modules\v1\models\AdminAuthoritySearch,
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
        $searchModel = new AdminAuthoritySearch();
        $searchModel->attributes=Yii::$app->request->queryParams;
        $result=[];
        $page=Yii::$app->request->get('page');
        $limit=Yii::$app->request->get('limit');
        $error_page=CheckData::checkPage($page,$limit);
        if($error_page){
            return Helper::reset([],0,1,$error_page);
        }
        if ($searchModel->validate()) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $result=ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'],$result['count'],0);
        }
        return Helper::reset([],0,1,CheckData::getValidateError($searchModel->errors));
    }


    /**
     * Creates a new AdminAuthority model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AdminAuthority();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
