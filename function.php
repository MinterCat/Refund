<?php
// $json_api = JSON($site.'api');
function JSON ($url)
{
	$data = file_get_contents($url);
    return json_decode($data);
}
//$GetStatusPage = GetStatusPage();
function GetStatusPage()
{
	$data = file_get_contents('https://explorer-api.minter.network/api/v1/status-page');
    $jsonCalled = json_decode($data);
    return $jsonCalled->data;
}
//$GetBlocks = GetBlocks();
function GetBlocks()
{
	$data = file_get_contents('https://explorer-api.minter.network/api/v1/blocks');
    $json = json_decode($data,true);
	$jsonCalled = $json['data'][0];
	return json_decode(json_encode($jsonCalled));
}
//$db = new Reward();
class Reward extends SQLite3
{
    function __construct()
    {
        $this->open(explode('public_html', $_SERVER['DOCUMENT_ROOT'])[0] . 'config/refund/'.$from.'.sqlite');
    }
}
//getBlockByHash ($api,$hash)
function getBlockByHash ($api,$hash)
{
    $api = new MinterAPI($api);
    return $api->getTransaction($hash);
}
//TransactoinSendDebug ($api,$transaction)
function TransactoinSendDebug ($api,$transaction)
{
    $api = new MinterAPI($api);
    return $api->send($transaction);
}