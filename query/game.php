<?php
require_once("consts.php");

function queryGameServers($host, $port, $requestID = 0)
{
    $fp = @fsockopen("udp://" . $host, $port, $errno, $errstr, 1);
    if (!$fp) {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }
    socket_set_timeout($fp, 1);

    // printf("\tQuerying %s:%d\n", $host, $port);

    //time the search
    $start = microtime(true);

    //request server info
    $request = pack("C4v2", PROTOCOL_VERSION, STATUS_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);

    $packet = [];
    $packet['host'] = sprintf('%s:%s', $host, $port);

    // fixed packet size: 40 bytes
    $packet['header'] = bin2hex(
        fread($fp, 06)
    ); // header (6 bytes) (PROTOCOL_VERSION, GAME_SERVER_STATUS_RESPONSE, 0xff, 0xfd, $requestID, 0x00)
    $packet['max_players'] = ord(fread($fp, 01));
    $packet['players'] = ord(fread($fp, 01));
    $packet['game_type'] = fread($fp, 04); // game type (4 bytes) "es3a"
    $packet['status_bytes'] = parseGameServerStatusResponseBytes(ord(fread($fp, 01)));
    $packet['version'] = fread($fp, 10); // game server version string (10 bytes)
    $packet['server_name'] = ""; // server name (17 bytes max) null terminated
    do {
        $byte = fread($fp, 1);
        if (ord($byte) > 0) {
            $packet['server_name'] .= $byte;
        }
    } while (ord($byte) != 0);

    if (empty($packet['header'])) {
        return false;
    }

    $end = microtime(true);
    $packet["ping"] = round((($end - $start) * 1000), 0);
    fclose($fp);

//    $packet["info"] = queryGameServerInfo($host, $port, ++$requestID);

    return $packet;
}

function parseGameServerStatusResponseBytes($bytesIn)
{
    $out = [
        "protected" => (GAME_STATUS_PROTECTED & $bytesIn) ? true : false,
        "dedicated" => (GAME_STATUS_DEDICATED & $bytesIn) ? true : false,
        "unknown1" => (GAME_STATUS_UNKNOWN1 & $bytesIn) ? true : false,
        "started" => (GAME_STARTED & $bytesIn) ? true : false,
        "dynamix" => (GAME_DYNAMIX_LOGO & $bytesIn) ? true : false,
        "won" => (GAME_WON_LOGO & $bytesIn) ? true : false,
        "unknown2" => (GAME_STATUS_UNKNOWN2 & $bytesIn) ? true : false,
        "unknown3" => (GAME_STATUS_UNKNOWN3 & $bytesIn) ? true : false,
    ];
    if ($out["dedicated"] === false && ($out["dynamix"] === true || $out["won"] === true)) {
        $out["dynamix"] = false;
        $out["won"] = false;
    }
    return $out;
}

function queryGameServerInfo($host, $port, $requestID = 0)
{
    $fp = @fsockopen("udp://" . $host, $port, $errno, $errstr, 1);
    if (!$fp) {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }
    socket_set_timeout($fp, 1);

    // printf("\tGameinfo Request %s:%d\n", $host, $port);

    //time the search
    $start = microtime(true);

    //request game info
    $request = pack("C4v2", PROTOCOL_VERSION, GAME_INFO_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);


    return [];
}
