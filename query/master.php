<?php
require_once("consts.php");

function queryMasterServer($host, $port, $requestID = 0)
{
    $fp = @fsockopen("udp://" . $host, $port, $errno, $errstr, 1);
    if (!$fp) {
        printf("Error opening socket to: %s:%d\nError#:%d\nError:%s", $host, $port, $errno, $errstr);
        return [];
    }
    socket_set_timeout($fp, 1);

    //time the search
    $start = microtime(true);

    //request server info
    $request = pack("C4v2", PROTOCOL_VERSION, STATUS_REQUEST, 0xff, 0x00, $requestID, 0x00);
    fwrite($fp, $request, 8);

    $packet = [];
    $packet['header'] = bin2hex(
        fread($fp, 8)
    ); // PROTOCOL_VERSION, MASTER_SERVER_STATUS_RESPONSE, 0x01, 0x01, 0x45, 0x00, 0x00, 0x02
    $packet['hostname_length'] = fread($fp, 1);
    $packet['motd'] = "";

    do {
        $byte = fread($fp, 1);
        if (ord($byte) > 0) {
            $packet['motd'] .= $byte;
        }
    } while (ord($byte) != 0);

    // handle multiple nulls after string
    do {
        $serverCount = ord(fread($fp, 1));
    } while ($serverCount == 0);
    $packet['server_count'] = $serverCount;

    $packet['servers'] = [];
    for ($i = 0; $i < $packet['server_count']; $i++) {
        fread($fp, 1); // separator \0x06

        $ip = sprintf(
            "%d.%d.%d.%d",
            unpack("C", fread($fp, 1))[1],
            unpack("C", fread($fp, 1))[1],
            unpack("C", fread($fp, 1))[1],
            unpack("C", fread($fp, 1))[1]
        );
        if ($ip == "127.0.0.1") { // discard gameservers reporting as localhost
            fread($fp, 2); // discard the localhost server port
            continue;
        }
        $packet['servers'][$i]["host"] = $ip;
        $packet['servers'][$i]["port"] = unpack("v", fread($fp, 2))[1];
    }

    $end = microtime(true);
    $packet['ping'] = round((($end - $start) * 1000), 0);

    fclose($fp);
    return $packet;
}