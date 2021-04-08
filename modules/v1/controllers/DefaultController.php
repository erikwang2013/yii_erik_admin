<?php

namespace app\modules\v1\controllers;

use yii\web\Controller,Yii,
    yii\filters\auth\HttpBasicAuth;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    public $enableCsrfValidation = false;
    public $login_admin_id;
    public $login_token;

    public function init()
    {
        parent::init();
    }
    // public function behaviors()
    // {
    //     return [
    //         'basicAuth' => [
    //             'class' => HttpBasicAuth::className(),
    //         ],
    //     ];
    // }




}
