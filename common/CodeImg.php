<?php
namespace app\common;

use Yii,
    yii\captcha\CaptchaAction;

class CodeImg extends CaptchaAction
{
    private $verify_code;
 
    public function __construct()
    {
        $this->init();
        // 更多api请访问yii\captcha\CaptchaAction类文档
        // 这里可以初始化默认样式
        $this->maxLength = 4;            // 最大显示个数
        $this->minLength = 4;            // 最少显示个数
        $this->backColor = 0x000000;     // 背景颜色
        $this->foreColor = 0x00ff00;     // 字体颜色
        $this->width = 80;               // 宽度
        $this->height = 45;              // 高度
    }
 
    /**
     * 返回图片二进制
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-01 22:05:08
     * @return void
     */
    public function inline()
    {
        return base64_encode($this->renderImage($this->getPhrase()));
        //$this->renderImage($this->getPhrase());
    }
 
    /**
     * 返回图片验证码
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-01 22:04:59
     * @return void
     */
    public function getPhrase()
    {
        if($this->verify_code){
            return $this->verify_code;
        }else{
            return $this->verify_code = $this->generateVerifyCode();
        }
    }

}