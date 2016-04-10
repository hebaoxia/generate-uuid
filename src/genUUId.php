<?php
// .----------------------------------------------------------------------------
// | 借助redis原子性操作，生成uuid(Universally Unique Identifier)
// |----------------------------------------------------------------------------
// | Author: mr2longly <mr2longly@gmail.com>
// |----------------------------------------------------------------------------

/**
 * 生成一个uuid
 *
 * @param integer $length
 * @return false | uuid String
 */
function genUUId($length = 16)
{
    global $redis;

    $table  = 'uuidGenerator';
    $today  = date('Ymd');
    $maxLen = $length - strlen($today);

    $counter    = $redis->hIncrBy($table, $today, 1);
    $counterLen = strlen($counter);

    if ($counterLen > $maxLen) {
        $redis->hIncrBy($table, $today, -1);
        return false;
    }

    if ($counter == 1 && $redis->hLen($table) > 7) {
        cleanGenerator();
    }

    return $today . str_repeat('0', ($maxLen - $counterLen)) . $counter;
}

/**
 * 生成多个uuid
 *
 * @param integer $uuidCount
 * @param integer $uuidLength
 * @return false | uuid Array
 */
function mGenUUId($uuidCount = 1, $uuidLength = 16)
{
    global $redis;

    $table  = 'uuidGenerator';
    $today  = date('Ymd');
    $maxLen = $uuidLength - strlen($today);

    $counter    = $redis->hIncrBy($table, $today, $uuidCount);
    if (strlen($counter) > $maxLen) {
        $redis->hIncrBy($table, $today, -$uuidCount);
        return false;
    }

    if ($counter == 1 && $redis->hLen($table) > 7) {
        cleanGenerator();
    }

    for ($i = $uuidCount; $i > 0; $i--) {
        $counterLen = strlen($counter);
        $uuidList[] = $today . str_repeat('0', ($maxLen - $counterLen)) . $counter;
        $counter--;
    }

    return array_reverse($uuidList);
}

/**
 * clean the expired generator
 *
 * @param integer $remainSize 要保留的计数器个数
 * @return integer 成功清理的计数器个数
 */
function cleanGenerator($remainSize = 7)
{
    global $redis;

    $success = 0;
    $table   = 'uuidGenerator';

    $generator     = $redis->hGetAll($table);
    $generatorSize = sizeof($generator);
    if ($generatorSize > $remainSize) {
        ksort($generator);

        $expiredGenerator = array_slice($generator, 0, ($generatorSize - $remainSize), true);
        foreach ($expiredGenerator as $field => $_null) {
            if (false !== $redis->hDel($table, $field)) {
                $success++;
            }
        }
    }

    return $success;
}
