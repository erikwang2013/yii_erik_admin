<?php

namespace app\modules\controllers\v1\admin;

use Yii,
    app\modules\models\v1\admin\AdminAuthority,
    yii\web\NotFoundHttpException,
    app\common\CheckData,
    app\common\Helper,
    yii\helpers\ArrayHelper,
    app\modules\controllers\DefaultController;

/**
 * AdminAuthorityController implements the CRUD actions for AdminAuthority model.
 */
class AdminAuthorityController extends DefaultController
{


    /**
     * Lists all AdminAuthority models.
     * @return mixed
     */
    public function actionIndex()
    {
        $params_config = Yii::$app->params;
        $params = Yii::$app->request->get();
        $page = Yii::$app->request->get('page', $params_config['page']);
        $limit = Yii::$app->request->get('limit', $params_config['limit']);
        $error_page = CheckData::checkPage($page, $limit);
        if ($error_page) {
            return Helper::reset([], 0, 1, $error_page);
        }
        $model = new AdminAuthority(['scenario' => 'search']);
        $data = Helper::filterKey($model, $params, 0) ?: [];
        $model->attributes = $data;
        $result = [];
        if ($model->validate()) {
            $dataProvider = $model->search($data, $page, $limit);
            $result = ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'], $result['count'], 0);
        }
        return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
    }


    /**
     * Creates a new AdminAuthority model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $post['parent_id'] = Yii::$app->request->post('parent_id', 0);
        $model = new AdminAuthority(['scenario' => 'create']);
        $post['id'] = Helper::getCreateId();
        $data = Helper::filterKey($model, $post, 0);
        $model->attributes = $data;
        if (!$model->validate()) {
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }

        if ($model->save(false)) {
            return Helper::reset([], 0, 0);
        }
        return Helper::reset([], 0, 1, $model->errors);
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
        $post = Yii::$app->request->post();
        if (count($post) == 0) {
            return Helper::reset([], 0, 1, Yii::t('app', 'Update at least one data'));
        }
        $model = new AdminAuthority(['scenario' => 'update']);
        $update_data = $post;
        $post['id'] = $id;
        $data = Helper::filterKey($model, $post, 0);
        $model->attributes = $data;
        if (!$model->validate()) {
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }
        $update = $this->findModel($id);
        Helper::filterKey($update, $update_data);
        if ($update->save(false)) {
            return Helper::reset([], 0, 0);
        }
        return Helper::reset([], 0, 1, $update->errors);
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
        $id = explode(',', $id);
        foreach ($id as $k => $v) {
            $check_data = CheckData::checkId($v);
            if ($check_data) {
                return Helper::reset([], 0, 1, $check_data);
            }
        }
        $model = new AdminAuthority(['scenarios' => 'search']);
        if ($model->deleteAll(['id' => $id])) {
            return Helper::reset([], 0, 0);
        }
        return Helper::reset([], 0, 1, $model->errors);
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
