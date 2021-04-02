<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/env.php';

//require  __DIR__ . '/GetCurrentNetwork.php';
require_once(__DIR__.'/GetCurrentNetwork.php');

$host = $mysql_host;
$user = $mysql_user;
$password = $mysql_password;
$db = $mysql_db;

$router = new \Bramus\Router\Router();

$router->get('/', function() {
    echo 'Google Ad Manager App';
});

$router->get('/about', function() {
    echo 'Google Ad Manager App About Page';
});

$router->get('/network', function() {
    //require __DIR__ . '/GetCurrentNetwork.php';
    //$network = new GetNetwork();
    //$network->main();
    GetNetwork::printtest();
    GetNetwork::main();
});

$router->get('/report', function() {
    require __DIR__ . '/RunInventoryReport.php';
});

$router->run();

function onclick_RunNetwork()
{	
    //require __DIR__ . '/GetCurrentNetwork.php';

}

function onclick_RunReport()
{
    require __DIR__ . '/RunInventoryReport.php';
}

if(array_key_exists('runnetwork', $_POST)) {
	onclick_RunNetwork();
}

if(array_key_exists('runreport', $_POST)) {
    onclick_RunReport();
}

?>

<form method="post">
	<input type="submit" name="runnetwork" id="runnetwork" value="Run Get Network"/><br/>
    <input type="submit" name="runreport" id="runreport" value="Run AD Manager Report"/><br/>
</form>