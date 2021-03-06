<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/5/23
 * Time: 下午12:03
 */



$redis_pool = new \swoole_connpool(\swoole_connpool::SWOOLE_CONNPOOL_REDIS);
$r = $redis_pool->setConfig([
    "host" => "127.0.0.1",
    "port" => 6379,
    "hbIntervalTime" => 1,
    "hbTimeout" => 1,
//    connectTimeout
]);
assert($r === true);

$redis_pool->on("hbConstruct", function() {
    return [
        "method" => "ping",
        "args" => null,
    ];
});
$redis_pool->on("hbCheck", function(\swoole_connpool $pool, $conn, $data) {
    assert(false);
    swoole_event_exit();
});


$r = $redis_pool->createConnPool(1, 1);
assert($r === true);

$timeout = 1000;

$timerId = swoole_timer_after($timeout + 100, function() use(&$got){
    assert(false);
    swoole_event_exit();
});

$connId = $redis_pool->get($timeout, function(\swoole_connpool $pool, /*\swoole_client*/$cli) use($timerId) {
    swoole_timer_clear($timerId);
    if ($cli instanceof \swoole_redis) {
        assert($cli->isConnected());
        $pool->release($cli);

    } else {
        assert(false);
        swoole_event_exit();
    }

    swoole_timer_after(100, "swoole_event_exit");
});
assert($connId > 0);