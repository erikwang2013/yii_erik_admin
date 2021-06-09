<?php

namespace app\modules\controllers\v1\admin;

use Yii,
    app\modules\models\v1\admin\Admin,
    yii\web\NotFoundHttpException,
    app\modules\services\v1\admin\AdminService,
    app\common\CheckData,
    app\common\Helper,
    app\modules\models\v1\admin\AdminInfo,
    app\modules\models\v1\admin\AdminRole,
    app\modules\controllers\DefaultController;

/**
 * AdminController implements the CRUD actions for Admin model.
 */
class AdminController extends DefaultController
{


    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex(AdminService $service, Admin $model, AdminInfo $model_info)
    {
        $params_config = Yii::$app->params;
        $params = Yii::$app->request->get();
        $page = Yii::$app->request->get('page', $params_config['page']);
        $limit = Yii::$app->request->get('limit', $params_config['limit']);
        $error_page = CheckData::checkPage($page, $limit);
        if ($error_page) {
            return Helper::reset([], 0, 1, $error_page);
        }

        $model->scenarios = 'search';
        $data = Helper::filterKey($model, $params, 0) ?: [];
        $model->attributes = $data;
        if (!$model->validate()) {
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }

        $model_info->scenarios = 'search';
        $data_info = Helper::filterKey($model_info, $params, 0) ?: [];
        if (count($data_info) > 0) {
            $model_info->attributes = $data_info;
            if (!$model_info->validate()) {
                return Helper::reset([], 0, 1, CheckData::getValidateError($model_info->errors));
            }
        }
        $data_all = array_merge($data, $data_info);
        return $service->index($model, $data_all, $page, $limit);
    }


    /**
     * 新增
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate(AdminService $service, Admin $model, AdminInfo $info)
    {
        $role_id = Yii::$app->request->post('role_ids');
        $post = Yii::$app->request->post();
        if (isset($role_id)) {
            $role_ids = explode(',', $role_id);
            foreach ($role_ids as $k => $v) {
                $check_data = CheckData::checkId($v, Yii::t('app', 'Role ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['role_ids']);
        }
        return $service->create($post, $role_id, $role_ids, $model, $info);
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
    public function actionUpdate($id, AdminService $service)
    {
        $role_id = Yii::$app->request->post('role_ids');
        $post = Yii::$app->request->post();
        if (isset($role_id)) {
            $role_ids = explode(',', $role_id);
            foreach ($role_ids as $k => $v) {
                $check_data = CheckData::checkId($v, Yii::t('app', 'Role ID'));
                if ($check_data) {
                    return Helper::reset([], 0, 1, $check_data);
                }
            }
            unset($post['role_ids']);
        }
        if (count($post) == 0) {
            return Helper::reset([], 0, 1, Yii::t('app', 'Update at least one data'));
        }
        return  $service->update($id, $post, $role_id, $role_ids);
    }

    /**
     * Deletes an existing Admin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, Admin $model, AdminInfo $info, AdminRole $admin_role)
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
        if (!$model->deleteAll(['id' => $id])) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $model->errors);
        }

        if (!$info->deleteAll(['id' => $id])) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $info->errors);
        }

        if (!$admin_role->deleteAll(['admin_id' => $id])) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $admin_role->errors);
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
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
    public function actionResetPassword($id, Admin $model)
    {
        $post = Yii::$app->request->post();
        $model->scenario = 'reset_password';
        $post['id'] = $id;
        $model->attributes = $post;
        if ($model->validate()) {
            $model_reset = $this->findModel($id);
            $model_reset->password = $post['password'];
            if ($model_reset->save(false)) {
                return Helper::reset([], 0, 0);
            }
        }
        return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
    }
}
