<?php

/**
 * 雪花算法生成id
 * 
 * $data=new snowflake(1,1);
*  $get_id=$data->generateId();
 *
 * @Author erik
 * @Email erik@erik.xyz
 * @Url https://erik.xyz
 * @DateTime 2021-02-22 21:52:31
 */
namespace app\common;
use Yii,yii\web\NotFoundHttpException;

class Snowflake
{
   const EPOCH_OFFSET = 0; //偏移时间戳
   const SIGN = 1;  //最高位树，始终为0，不可用
   const TIMESTAMP = 41;  //时间戳位数   默认41位，可以使用69年
   const DATA_CENTER = 5;  //IDC编号位数  最多32个节点
   const MACHINE_ID = 5;  //机器编号位数  最多32个节点
   const SEQUENCE = 12;  //计数序列号位数，即一系列自增id 每个节点每毫秒产生4096个ID序列

   protected $data_center_id; //数据中心编号

   protected $unix_id;  //机器编号

   protected $last_time = null;  //最后一次生成id使用的时间戳

   protected $serial = 1;
   protected $sign_left_shift = self::TIMESTAMP + self::DATA_CENTER + self::MACHINE_ID + self::SEQUENCE; //符号左位移
   protected $time_left_shift = self::DATA_CENTER + self::MACHINE_ID + self::SEQUENCE;  //时间戳左位移
   protected $data_center_left_shift = self::MACHINE_ID + self::SEQUENCE; //idc左位移
   protected $unix_left_shift = self::SEQUENCE;   //机器编号左位移位数
   protected $max_serial = -1 ^ (-1 << self::SEQUENCE);  //最大序列号
   protected $max_unix = -1 ^ (-1 << self::MACHINE_ID); //最大机器编号
   protected $max_data_center = -1 ^ (-1 << self::DATA_CENTER); //最大数据中心编号

   public function __construct($data_center_id, $unix_id)
   {
       if ($data_center_id > $this->max_data_center) {
           throw new NotFoundHttpException(Yii::t('app','Data center number value error, value range is').'：0-' . $this->max_data_center);
       }
       if ($unix_id > $this->max_unix) {
           throw new NotFoundHttpException(Yii::t('app','Wrong value of machine number, value range is').'：0-' . $this->max_unix);
       }
       $this->data_center_id = $data_center_id;
       $this->unix_id = $unix_id;
   }

   public function generateId()
   {
       $sign = 0;
       $unix_time = $this->getUnixTime();
       //判断时间戳
       if ($unix_time < $this->last_time) {
           throw new NotFoundHttpException(Yii::t('app','The current time cannot be less than the last time!'));
       }
       if ($unix_time == $this->last_time) {
           $serial = ++$this->serial;
           if ($serial == $this->max_serial) {
               $unix_time = $this->getUnixTime();
               while ($unix_time <= $this->last_time) {
                   $unix_time = $this->getUnixTime();
               }
               $this->serial = 0;
               $serial = ++$this->serial;
           }
       } else {
           $this->serial = 0;
           $serial = ++$this->serial;
       }
       $this->last_time = $unix_time;
       $time = (int)($unix_time - self::EPOCH_OFFSET);
       $id = ($sign << $this->sign_left_shift) | ($time << $this->time_left_shift)
           | ($this->data_center_id << $this->data_center_left_shift)
           | ($this->unix_id << $this->unix_left_shift) | $serial;
       return $id;
   }

   public function getUnixTime()
   {
       return floor(microtime(true) * 1000);
   }
}