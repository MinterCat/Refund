<?php
declare(strict_types=1);
require_once('../config/minterapi/vendor/autoload.php');
use Minter\MinterAPI;
use Minter\SDK\MinterTx;
use Minter\SDK\MinterCoins\MinterMultiSendTx;

// github.com/MinterTeam/minter-php-sdk

include('config.php');
include('function.php');
include('nodes.php');

for ($i = 0; $i <= 4; $i++)
{
	$data = $array_node[$i];

$node_key = $data['node_key'];
$from = $data['from'];
$name = $data['name'];
$comm = $data['comm'];

$totalDelegatedBip = GetStatusPage()->totalDelegatedBip;
$numberOfBlocks = GetStatusPage()->numberOfBlocks;

$db = new Refund();

$data = $db->query('SELECT * FROM "'.$from.'"')->fetchArray(1);

$hash = $data['numberOfhash'];
$block = getBlockByHash($api2,$hash)->result->height;
$db->exec('UPDATE "'.$from.'" SET numberOfBlocks = "'. $block .'"');

$block_reward = $block + 17280; //Блок, после которого возможна выплата
if ($block_reward <= $numberOfBlocks)
	{
		$blockReward = GetBlocks()->reward;
		$commision = 1 - ($comm/100);//commision(0..1) - комиссия валидатора
		$delegators = file_get_contents("https://minterscan.pro/validators/$node_key/delegators?coin=$coin");
		$delegatorspayload = json_decode($delegators,true);
		$count = count($delegatorspayload)-1;
		
		if ($count != -1)
		{

		$estimateCoinBuy = $api->estimateCoinSell('BIP', '1000000000000000000', $coin, null);
		$will_get = $estimateCoinBuy->result->will_get/10 ** 18;
		
		$tx_array = array();

		for($i = 0; $i <= $count; $i++) 
			{
				$address = $delegatorspayload[$i]['address'];
				if (($address != 'Mx836a597ef7e869058ecbcc124fae29cd3e2b4444') and ($address != 'Mxaa9a68f11241eb18deff762eac316e2ccac22a03')) 
					{
						$bip_value = $delegatorspayload[$i]['bip_value'];
						$reward = $blockReward*17280*0.8*$bip_value*$commision/$totalDelegatedBip;
						$comm_node = ($blockReward*17280*0.8*$bip_value*1/$totalDelegatedBip)-$reward;
						$value = $will_get*$comm_node;
											
						$tx_rew = array(
										'coin' => $coin,
										'to' => $address,
										'value' => $value
									);
						array_push($tx_array, $tx_rew);
											
					}
			}
		$tx_rew = array(
								'coin' => $coin,
								'to' => 'Mxaa9a68f11241eb18deff762eac316e2ccac22a03',
								'value' => 0
							); //заглушка с нулевым value.
		array_push($tx_array, $tx_rew);

		$text = "🐈 MINTERCAT: $name $comm% commission refund. @MinterCat"; //текст payload

$api = 'https://api.minter.one';

$transaction = TransactionSend($api,$address,$privat_key,$chainId = 1,$gasCoin = 'BIP',$text = '',$tx_array);
$hash = $transaction->hash;
$code = $transaction->code;
if ($code == 0) {
	echo 'transaction send';
	$db->query('UPDATE "'.$from.'" SET numberOfhash = "'.$hash.'"');
	$bot_key = 'refund';
	include('bot/RefundMinterBot/RefundMinterBot.php'); //бот сообщает о выплате в группе.
	} else {echo 'transaction error';}
	}}
   else
   {
	   echo "Ревард уже был уплачен в блоке $block <br><br>
		Следующий Reward будет уплачен после блока $block_reward<br><br>";
   }
}