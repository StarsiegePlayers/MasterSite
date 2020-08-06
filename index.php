<?php
$file = 'query.php';
if (is_file($file)) {
    include_once($file);
} else {
    die("Can't load $file");
}
?><!DOCTYPE html>
<html>
<head>
<title>Starsiege Servers</title>
<style>
body {
	background-color: #000000;
    margin:auto;
    padding:auto;
    text-align: center;
    color:white;
    font-size: 15px;
    background:black url('./imgs/mfd.png') no-repeat;
}

div.serverTable {
    overflow:scroll;
    max-width:708px;
    max-height:489px;
}

table {
	border: 3px solid #001e56;
	background-color: #050e15;
    padding:15px;
    width:100%;
}

tr {
	color: #d5ab00;
	background-color: #050e15;
}

tr:hover {
	color: #ffef68;
}

td {
	border: 1px double black;
	margin: 3px;
	padding: 3px;
	text-align: center;
    font-size: 15px;
}

td.server {
	border-bottom: 1px solid #462e01;
	border-right: 1px solid #462e01;
	border-left: 1px solid #462e01;
    padding-left:15px;
    padding-right:15px;
}

td.end {
	border-right: 0px solid #462e01;
	border-left: 1px solid #462e01;
}

td.start {
	border-right: 1px solid #462e01;
	border-left: 0px solid #462e01;
}

th.header {
	border: 1px solid #896600;
	background-color: #251c01;
	color: #0a7300;
    padding-left:15px;
    padding-right:15px;
    font-size: 18px;
}

.serverTable {
    display: inline-block;
}

</style>
</head>
<body>
    <div class="serverTable" id="main">
    Master Server Info
    <table cellspacing="0">
        <tr>
            <th class="header">No.</th>
            <th class="header">server</th>
            <th class="header">motd</th>
            <th class="header">server_count</th>
            <th class="header">ping</th>
        </tr>

<?php

$totalGameServers = 0;
foreach($masterServer as $key=>$server)
{    
    $totalGameServers += $server['server_count'];
    $motd = strtolower ($server['motd']);
    $motd = str_replace('\n' . $masters[$key]["server"] . ';0000000000',"", $motd);
    $motd = str_replace('\n',"\n", $motd);
    
    echo "<tr>
    <td class=\"server start\">". ($key+1) ."</td>
    <td class=\"server\">{$masters[$key]["server"]}:{$masters[$key]["port"]}</td>
    <td class=\"server\">{$motd}</td>
    <td class=\"server\">{$server['server_count']}</td>
    <td class=\"server end\">{$server['ping']}</td>
    </tr>";
}
?>
<!--         <tr>
            <td class="server start">&nbsp;</td>
            <td class="server">&nbsp;</td>
            <td class="server">&nbsp;</td>
            <td class="server">&nbsp;</td>
            <td class="server end">&nbsp;</td>
        </tr>
        <tr>
            <td class="server start">&nbsp;</td>
            <td class="server">&nbsp;</td>
            <td class="server">Total Game Server Count:</td>
            <td class="server"><?=$totalGameServers?></td>
            <td class="server end">&nbsp;</td>
        </tr>
        <tr>
            <td class="server start">&nbsp;</td>
            <td class="server">&nbsp;</td>
            <td class="server">Unique Game Server Count:</td>
            <td class="server"><?=count($gameServers)?></td>
            <td class="server end">&nbsp;</td>
        </tr> -->
    </table>
    Game Server Info
    <table cellspacing="0">
        <tr>
            <th class="header">No.</th>
            <th class="header">Server Name</th>
            <th class="header">Started</th>
            <th class="header">Players</th>
            <th class="header">Ping</th>
            <th class="header">Server Address</th>
        </tr>

<?php

foreach($gameServers as $key=>$server)
{

    $namePrefix  = ($server['status_bytes']['protected']) ? '<img src="./imgs/passwd.png" alt="P"> ' : "";
    $namePrefix .= ($server['status_bytes']['dedicated']) ? '<img src="./imgs/dedi.png" alt="D"> ' : "";
    $namePrefix .= ($server['status_bytes']['dynamix']) ? '<img src="./imgs/dyn.png" alt="Y"> ' : "";
    $namePrefix .= ($server['status_bytes']['won']) ? '<img src="./imgs/won.png" alt="W"> ' : "";
    $started = ($server['status_bytes']['started']) ? "yes" : "no";

    echo "<tr>
    <td class=\"server start\">". ($key+1) ."</td>
    <td class=\"server\">{$namePrefix}{$server['server_name']}</td>
    <td class=\"server\">{$started}</td>
    <td class=\"server\">{$server['players']} / {$server['max_players']}</td>
    <td class=\"server\">{$server['ping']}</td>
    <td class=\"server end\">{$server['host']}</td>
    </tr>";
}
?>
    </table>
</div>

</body>

</html>
