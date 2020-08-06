<?php
require_once("query/master.php");
require_once("query/game.php");
require_once("config.php");

$masterData = [];
$masterServer = [];

foreach ($masters as $master) {
    if (DEBUG)
        printf("probing {$master['server']}");

    $query = queryMasterServer($master['server'], $master['port']);
    $masterServer[] = $query;
    if (count($query['servers']) <= 0) {
        printf("Error server count was 0");
        exit;
    }

    if (DEBUG)
        printf("Acquired %d servers in %s ms\n", count($query['servers']), $query['ping']);

    foreach ($query['servers'] as $server) {
        $uniqueID = sprintf("%s:%s", $server["host"], $server['port']);
        $masterData[$uniqueID] = $server;
    }
}

$gameServers = [];
$start = microtime(true);
$id = 0;
foreach ($masterData as $server) {
    $gsinfo = queryGameServers($server["host"], $server["port"], ++$id);
    if ($gsinfo !== false) {
        $gameServers[] = $gsinfo;
    }
}
$end = microtime(true);

if (DEBUG) {
    printf("Queried %d servers in %s ms\n", count($gameServers), $end - $start);
    print_r($gameServers);
}
