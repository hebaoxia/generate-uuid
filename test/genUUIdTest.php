<?php

$redis = new Redis();
$redis->connect('0.0.0.0', 6379);

include '../src/genUUId.php';

$begin = microtime(true);
$uuid = genUUId();
$end = microtime(true);

echo "\n\n";
echo '测试 genUUId() 结果：', "\n";
if (false === $uuid) {
    echo 'Failed! 生成 uuid 失败：计数器已经超出最大值.', "\n";
} else {
    echo 'Success! 产生的 uuid = ' . $uuid, "\n";
}
echo "耗时：" . ($end - $begin) . " s\n";
echo "\n\n";

$begin = microtime(true);
$uuidList = mGenUUId(50000, 16);
$end = microtime(true);

echo '测试 mGenUUId(50000, 16) 结果：', "\n";
//var_export($uuidList);
echo "uuid list length:" . sizeof($uuidList), "\n";
echo "耗时：" . ($end - $begin) . " s\n";
echo "\n\n";

cleanGenerator(0);
