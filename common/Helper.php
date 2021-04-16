<?php
namespace app\common;

use Yii,
app\common\Snowflake,
app\common\CodeImg;

class Helper
{

        /**
     * 返回响应
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-21 23:24:02
     * @param array $data
     * @param integer $count
     * @param integer $code
     * @param string $msg
     * @return void
     */
    public static function reset($data=[],$count=0,$code=0,$msg=''){
        $result= [
            'code'=>$code,
            'count'=>$count,
            'data'=>$data
        ];
        if(empty($msg)){
            if($code==0 ){
                $result['msg']=Yii::t('app', 'Ok');
            }else{
                $result['msg']=Yii::t('app',"Fail");
            }
        }else{
            $result['msg']=$msg;
        }
        return json_encode($result);
    }
        /**
     * 生成数据表id
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-02-25 00:13:47
     * @return void
     */
    public static function getCreateId(){
        $snowflake_config=Yii::$app->params['snowflake'];
        $snowflake=new Snowflake($snowflake_config['data_center_id'],$snowflake_config['unix_id']);
        return $snowflake->generateId();
    }
 
    /**
     * 获取缓存数据
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-01 21:31:26
     * @param [type] $key
     * @return void
     */
    public static function getCache($key){
        $cache=Yii::$app->cache;
        $data=$cache->get($key);
        return $data;
    }

    /**
     * 存储缓存数据
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-01 21:31:36
     * @param [type] $key
     * @param [type] $value
     * @param integer $time
     * @return void
     */
    public static function setCache($key,$value,$time=0){
        $cache=Yii::$app->cache;
        $data=$cache->set($key,$value,$time);
        return $data;
    }

    /**
     * 覆盖存储数据到缓存
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-02 21:21:46
     * @param [type] $key
     * @param [type] $value
     * @param integer $time
     * @return void
     */
    public static function addCache($key,$value,$time=0){
        $cache=Yii::$app->cache;
        $data=$cache->add($key,$value,$time);
        return $data;
    }

    /**
     * 删除缓存
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-02 21:22:28
     * @param [type] $key
     * @return void
     */
    public static function deleteCache($key){
        $cache=Yii::$app->cache;
        $data=$cache->delete($key);
        return $data;
    }
    /**
     * 获取验证码
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-03-01 22:08:51
     * @return void
     */
    public static function getCode(){
        $code=new CodeImg();
        return [
            'number'=>$code->getPhrase(),
            'img'=>$code->inline()
        ];
    }

    /**
     * 过滤存在的字段
     *
     * @Author erik
     * @Email erik@erik.xyz
     * @Url https://erik.xyz
     * @DateTime 2021-04-08 09:49:21
     * @param [type] $model
     * @param [type] $data
     * @param integer $status 0返回数组 1覆盖对象
     * @return void
     */
    public static function filterKey($model,$data,$status=1){
        if($status==0){
            $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
            $data_info=[];
            foreach($data as $name=>$value){
                if (isset($attributes[$name])) {
                    $data_info[$name]=$value;
                }
            }
            return $data_info;
        }else{
            $attributes = array_flip($model->safeAttributes() ? $model->safeAttributes() : $model->attributes());
            foreach($data as $name=>$value){
                if (isset($attributes[$name])) {
                    $model->$name=$value;
                }
            }
        }
        
    }
}