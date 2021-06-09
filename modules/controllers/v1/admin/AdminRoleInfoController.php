<?php

namespace app\modules\controllers\v1\admin;

use Yii,
    app\modules\models\v1\admin\AdminRoleInfo,
    yii\web\NotFoundHttpException,
    app\common\CheckData,
    app\common\Helper,
    yii\helpers\ArrayHelper,
    app\modules\models\v1\admin\AdminRoleAuthority,
    app\modules\controllers\DefaultController;

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

        $params_config = Yii::$app->params;
        $params = Yii::$app->request->get();
        $page = Yii::$app->request->get('page', $params_config['page']);
        $limit = Yii::$app->request->get('limit', $params_config['limit']);
        $error_page = CheckData::checkPage($page, $limit);
        if ($error_page) {
            return Helper::reset([], 0, 1, $error_page);
        }
        $model = new AdminRoleInfo(['scenario' => 'search']);
        $data = Helper::filterKey($model, $params, 0) ?: [];
        $model->attributes = $data;
        if ($model->validate()) {
            $dataProvider = $model->search($data, $page, $limit);
            $result = ArrayHelper::toArray($dataProvider);
            return Helper::reset($result['list'], $result['count'], 0);
        }
        return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
    }

    /**
     * Creates a new AdminRoleInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $authority_id = Yii::$app->request->post('authority_ids');
        if (isset($authority_id)) {
            $authority_ids = explode(',', $authority_id);
            foreach ($authority_ids as $k => $v) {
                $check_data = CheckData::checkId($v, Yii::t('app', 'Permission ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['authority_ids']);
        }
        $model = new AdminRoleInfo(['scenario' => 'create']);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $post['id'] = Helper::getCreateId();
        $data = Helper::filterKey($model, $post, 0);
        $model->attributes = $data;
        if (!$model->validate()) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }

        if (!$model->save(false)) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $model->errors);
        }

        if (isset($authority_id)) {
            $authority = AdminRoleAuthority::tableName();
            $insert = [];
            foreach ($authority_ids as $m => $n) {
                $insert[] = [
                    'role_id' => $post['id'],
                    'authority_id' => $n
                ];
            }
            Yii::$app->db->createCommand()->batchInsert($authority, ['role_id', 'authority_id'], $insert)->execute();
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
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
        $authority_id = Yii::$app->request->post('authority_ids');
        $post = Yii::$app->request->post();
        if (isset($authority_id)) {
            $authority_ids = explode(',', $authority_id);
            foreach ($authority_ids as $k => $v) {
                $check_data = CheckData::checkId($v, Yii::t('app', 'Permission ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['authority_ids']);
        }


        if (count($post) == 0) {
            return Helper::reset([], 0, 1, Yii::t('app', 'Update at least one data'));
        }
        $model = new AdminRoleInfo(['scenario' => 'update']);
        $update_data = $post;
        $post['id'] = $id;
        $data = Helper::filterKey($model, $post, 0);
        $model->attributes = $data;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        if (!$model->validate()) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }

        $update = $this->findModel($id);
        Helper::filterKey($update, $update_data);
        if (!$update->save(false)) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $update->errors);
        }


        if (isset($authority_id)) {
            //删除角色权限关系
            $authority = new AdminRoleAuthority();
            if (!$authority->deleteAll(['role_id' => $id])) {
                $transaction->rollBack();
                return Helper::reset([], 0, 1, $authority->errors);
            }
            $insert = [];
            foreach ($authority_ids as $m => $n) {
                $insert[] = [
                    'role_id' => $id,
                    'authority_id' => $n
                ];
            }
            $authority_table = AdminRoleAuthority::tableName();
            Yii::$app->db->createCommand()->batchInsert($authority_table, ['role_id', 'authority_id'], $insert)->execute();
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
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
        $id = explode(',', $id);
        foreach ($id as $k => $v) {
            $check_data = CheckData::checkId($v);
            if ($check_data) {
                return Helper::reset([], 0, 1, $check_data);
            }
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $model = new AdminRoleInfo();
        if (!$model->deleteAll(['id' => $id])) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $model->errors);
        }
        $authority = new AdminRoleAuthority();
        if (!$authority->deleteAll(['role_id' => $id])) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $authority->errors);
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
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
