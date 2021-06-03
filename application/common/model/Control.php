<?php

namespace app\common\model;

use think\Model;


class Control extends Model
{

    

    

    // 表名
    protected $name = 'control';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'expire_datetime_text',
        'expire_type_text'
    ];
    

    
    public function getExpireTypeList()
    {
        return ['1' => __('Expire_type 1'), '2' => __('Expire_type 2'), '3' => __('Expire_type 3')];
    }


    public function getExpireDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['expire_datetime']) ? $data['expire_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getExpireTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['expire_type']) ? $data['expire_type'] : '');
        $list = $this->getExpireTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setExpireDatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
    protected function setCreatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
