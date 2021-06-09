<?php

namespace app\modules\services\v1\admin;

use  yii\helpers\ArrayHelper,
    app\common\Helper,
    Yii,
    app\common\CheckData,
    app\modules\models\v1\admin\AdminRole,
    app\modules\models\v1\admin\Admin,
    app\modules\models\v1\admin\AdminInfo,
    yii\web\NotFoundHttpException;

class AdminService
{
    public function index($model, $data_all, $page, $limit)
    {
        $dataProvider = $model->search($data_all, $page, $limit);
        $result = ArrayHelper::toArray($dataProvider);
        return Helper::reset($result['list'], $result['count'], 0);
    }

    public function create($post, $role_id, $role_ids, $model, $info)
    {
        //新增账号
        $model->scenarios = 'create';
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $post['id'] = Helper::getCreateId();
        $model->setPassword($post['password']);
        $data = Helper::filterKey($model, $post, 0);
        if (!isset($post['nick_name'])) {
            $data['nick_name'] = 'erik_' . time();
        }
        $model->attributes = $data;
        if (!$model->validate()) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, CheckData::getValidateError($model->errors));
        }
        if (!$model->save(false)) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $model->errors);
        }

        //新增详情
        $info->scenarios = 'create';
        $data_info = Helper::filterKey($info, $post, 0) ?: [];
        if (count($data_info) > 0) {
            $info->attributes = $data_info;
            if (!$info->validate()) {
                $transaction->rollBack();
                return Helper::reset([], 0, 1, CheckData::getValidateError($info->errors));
            }
            if (!$info->save(false)) {
                $transaction->rollBack();
                return Helper::reset([], 0, 1, $info->errors);
            }
        }

        //新增用户角色
        if (isset($role_id)) {
            $insert = [];
            foreach ($role_ids as $m => $n) {
                $insert[] = [
                    'admin_id' => $post['id'],
                    'role_id' => $n
                ];
            }
            $table = AdminRole::tableName();
            Yii::$app->db->createCommand()->batchInsert($table, ['admin_id', 'role_id'], $insert)->execute();
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
    }

    public function update($id, $post, $role_id, $role_ids)
    {

        $admin = new Admin(['scenario' => 'update']);
        //过滤存在的字段
        $data = Helper::filterKey($admin, $post, 0);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        //unset($post['id']);
        $admin->attributes = $data;
        if (!$admin->validate()) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, CheckData::getValidateError($admin->errors));
        }
        $model = $this->findModel($id);

        //过滤符合的数据
        Helper::filterKey($model, $post);
        //保存用户基本信息
        if (!$model->save(false)) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $model->errors);
        }

        //开始更新用户详情
        if (count($post) == 0) {
            $transaction->commit();
            return Helper::reset([], 0, 0);
        }
        $admin_info = new AdminInfo(['scenario' => 'update']);
        //过滤存在的字段
        $data_info = Helper::filterKey($admin_info, $post, 0);
        $admin_info->attributes = $data_info;
        //校验用户详情
        if (!$admin_info->validate()) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, CheckData::getValidateError($admin_info->errors));
        }
        $info = $this->findInfoModel($id);
        //过滤符合的数据
        Helper::filterKey($info, $data_info);
        //保存用户详情
        if (!$info->save(false)) {
            $transaction->rollBack();
            return Helper::reset([], 0, 1, $info->errors);
        }
        $admin_role = new AdminRole(['scenario' => 'update']);
        //删除用户角色
        if ($admin_role->deleteAll(['admin_id' => $id])) {
            if (isset($role_id)) {
                //新增用户角色
                $insert = [];
                foreach ($role_ids as $m => $n) {
                    $insert[] = [
                        'admin_id' => $id,
                        'role_id' => $n
                    ];
                }
                $table = $admin_role::tableName();
                Yii::$app->db->createCommand()->batchInsert($table, ['admin_id', 'role_id'], $insert)->execute();
            }
        }
        $transaction->commit();
        return Helper::reset([], 0, 0);
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
