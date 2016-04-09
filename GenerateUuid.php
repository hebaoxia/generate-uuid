<?php
/**
 * 借助redis原子操作，生成绝对唯一的uuid 支持批量产出
 *
 * @param integer $size 生成唯一id的个数
 * @param integer $length 唯一id的长度
 * @return mix size=1时返回一个string类型的uuid，当size>1时返回uuid数组
 * @author mr2longly <mr2longly@gmail.com>
 */
function generateUuid($size = 1, $length = 16)
{
    $date = date('Ymd');
    $placeholder = str_repeat('0', $length - strlen($date));

    // 删除上30天以前的计数器，防止redis字段过多
    $expired_key = 'generate_uuid:' . date('Ymd', time() - 30 * 86400) . $placeholder;
    if ($this->redis->exists($$expired_key)) {
        $this->redis->delete($expired_key);
    }

    $seek = $date . $placeholder;
    $counter = $this->redis->incr('generate_uuid:' . $seek, $count);
    if ($count > 1) {
        for ($i = $count; $i > 0; $i--) {
            $uuid_list[] = substr($seek, 0, -(strlen($counter))) . $counter;
            $counter--;
        }
        return array_reverse($uuid_list);
    }
    return substr($seek, 0, -(strlen($counter))) . $counter;
}
