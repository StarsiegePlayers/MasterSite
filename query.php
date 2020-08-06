<?php
require_once("query/master.php");
require_once("query/game.php");
// set_time_limit (60);
$masters = [
    [
        "server" => "master1.starsiege.io",
        "port" => 29000,
    ],
    [
        "server" => "master2.starsiege.pw",
        "port" => 29000,
    ],
    [
        "server" => "master3.starsiege.io",
        "port" => 29000,
    ],
    [
        "server" => "starsiege1.no-ip.org",
        "port" => 29000,
    ],/*
    [
        "server"    => "starsiege.noip.us",
        "port"      => 29000,
    ], 
    [
        "server"    => "154.0.175.219",
        "port"      => 29000,
    ], */
];

$masterData = [];
$masterServer = [];

foreach ($masters as $master) {
    // printf("probing {$master['server']}");
    $query = queryMasterServer($master['server'], $master['port']);
    $masterServer[] = $query;
    if (count($query['servers']) <= 0) {
        printf("Error server count was 0");
        exit;
    }
    // printf("Acquired %d servers in %s ms\n", count($query['servers']), $query['ping']);

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

// printf("Queried %d servers in %s ms\n", count($gameServers), $end - $start);
// print_r($gameServers);
