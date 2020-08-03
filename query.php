<?php
$masters = [
    [
        "server"    => "master2.starsiege.pw",
        "port"      => 29000,
    ],
    [
        "server"    => "starsiege1.no-ip.org",
        "port"      => 29000
    ],
];

const PROTOCOL_VERSION = 0x10;
const STATUS_REQUEST = 0x03;
const GAME_SERVER_STATUS_RESPONSE = 0x04;
const MASTER_SERVER_STATUS_RESPONSE = 0x06;
const GAME_INFO_REQUEST = 0x07;
const GAME_INFO_RESPONSE = 0x08;

$masterData = [];

foreach ($masters as $master) {
    $query = queryMasterServer($master['server'], $master['port']);

    if (count($query['servers']) <= 0) {
        printf("Error server count was 0");
        exit;
    }
    printf("Acquired %d servers in %s s\n", count($query['servers']), $query['request_duration']);

    foreach ($query['servers'] as $server) {
        $uniqueID = sprintf("%s:%s", $server["host"], $server['port']);
        $masterData[$uniqueID] = $server;
    }
}

$gameServers = [];
$start = microtime(true);
foreach ($masterData as $id => $server) {
    $gsinfo = queryGameServers($server["host"], $server["port"], $id);
    if ($gsinfo !== false) {
        $gameServers[] = $gsinfo;
    }
}
$end = microtime(true);

printf("Queried %d servers in %s s\n", count($query['servers']), $end - $start);
print_r($gameServers);


function queryMasterServer($host, $port, $requestID = 0) {
    $fp = @fsockopen("udp://".$host, $port, $errno, $errstr, 1);
    if (!$fp)
    {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }

    //time the search
    $start = microtime(true);

    //request server info
    $request = pack("C4v2", PROTOCOL_VERSION, STATUS_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);
    $packet = [];
    $packet['header'] = fread($fp, 8);
    $packet['hostname_length'] = fread($fp, 1);
    $packet['motd'] = "";
    do {
        $byte = fread($fp, 1);
        if (ord($byte) > 0) {
            $packet['motd'] .= $byte;
        }
    }
    while (ord($byte) != 0);

    // handle multiple nulls after string
    do {
        $serverCount = ord(fread($fp, 1));
    }
    while ($serverCount == 0);
    $packet['server_count'] = $serverCount;

    $packet['servers'] = [];
    for ($i=0; $i < $packet['server_count']; $i++) {
        fread($fp, 1); // separator \0x06

        $ip = sprintf("%d.%d.%d.%d", unpack("C", fread($fp, 1))[1], unpack("C", fread($fp, 1))[1], unpack("C", fread($fp, 1))[1], unpack("C", fread($fp, 1))[1]);
        if ($ip == "127.0.0.1") { // discard gameservers reporting as localhost
            fread($fp, 2); // discard the localhost server port
            continue;
        }
        $packet['servers'][$i]["host"] = $ip;
        $packet['servers'][$i]["port"] = unpack("v", fread($fp, 2))[1];
    }

    $end = microtime(true);
    $packet['request_duration'] = $end - $start;

    fclose($fp);
    return $packet;
}

function queryGameServers($host, $port, $requestID = 0) {
    $fp = @fsockopen("udp://".$host, $port, $errno, $errstr, 1);
    if (!$fp)
    {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }
    socket_set_timeout($fp, 1);

    printf("\tQuerying %s:%d\n", $host, $port);

    //time the search
    $start = microtime(true);

    //request server info
    $request = pack("C4v2", PROTOCOL_VERSION, STATUS_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);

    // fixed packet size: 40 bytes
    $packet['host']         = sprintf('%s:%s', $host, $port);
    $packet['header']       = fread($fp, 06); // header (6 bytes)
    $packet['game_type']    = fread($fp, 07); // game type (7 bytes) "@\x00es3a\x06"
    $packet['version']      = fread($fp, 10); // game server version string (10 bytes)
    $packet['server_name']  = ""; // server name (17 bytes max) null terminated
    do {
        $byte = fread($fp, 1);
        if (ord($byte) > 0) {
            $packet['server_name'] .= $byte;
        }
    }
    while (ord($byte) != 0);

    if (empty($packet['header']))
    {
        return false;
    }

    $end = microtime(true);
    $packet["request_duration"] = $end - $start;
    fclose($fp);

//    $packet["info"] = queryGameServerInfo($host, $port, ++$requestID);

    return $packet;
}


function queryGameServerInfo($host, $port, $requestID = 0)
{
    $fp = @fsockopen("udp://".$host, $port, $errno, $errstr, 1);
    if (!$fp)
    {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }
    socket_set_timeout($fp, 1);

    printf("\tGameinfo Request %s:%d\n", $host, $port);

    //time the search
    $start = microtime(true);

    //request game info
    $request = pack("C4v2", PROTOCOL_VERSION, GAME_INFO_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);


    return [];
}