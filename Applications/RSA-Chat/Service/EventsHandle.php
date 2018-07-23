<?php
/**
 * @author: chenjia404
 * @Date  : 2017-11-07
 * @Time  : 21:44
 */
namespace App\Service;

use GatewayWorker\Lib\Gateway;

class EventsHandle
{
	protected $client_name_list;

	protected $register_by_RSA;

	public function __construct()
	{
		$this->client_name_list =  new ClientName();
		$this->register_by_RSA  =  new RegisterByRSA();
	}

	private function sendToCurrentClient(array $message,int $code = 0,string $msg="成功")
	{
		Gateway::sendToCurrentClient(json_encode(["code"=>$code,"msg"=>$msg,"data"=>$message],JSON_UNESCAPED_UNICODE));
	}


	private function sendToGroup($room_id,array $message,int $code = 0,string $msg="成功")
	{
		Gateway::sendToGroup($room_id,json_encode(["code"=>$code,"msg"=>$msg,"data"=>$message],JSON_UNESCAPED_UNICODE));
	}

	/**
	 * 登录逻辑
	 * @author: chenjia404
	 * @Date  : 2017-07-15
	 * @param       $client_id
	 * @param array $message
	 * @return void
	 */
	public function login($client_id,array $message):void
	{
		// 判断是否有房间号
		if(!isset($message['room_id']))
		{
			$this->sendToCurrentClient(['type'=>'error'],102,"聊天室房间id不能为空");
			return;
		}

		// 把房间号昵称放到session中
		$room_id = $message['room_id'];
		$client_name = htmlspecialchars($message['client_name']);

		//同一个聊天室昵称不能重复
		if($this->client_name_list->exists($room_id,$client_name))
		{
			$this->sendToCurrentClient(['type'=>'error'],101,"同一个聊天室昵称不能重复");
			return;
		}

		$rsa_public_key = $message['rsa_public_key']??'';
		$rsa_public_key = str_replace("\r\n", "\n", $rsa_public_key);
		$rsa_public_key = str_replace("\r", "\n", $rsa_public_key);
		$pub_key = openssl_pkey_get_details(openssl_pkey_get_public($rsa_public_key));
		if(!$pub_key)
		{
			$this->sendToCurrentClient(['type'=>'error'],103,"rsa登录验证失败");
			return;
		}
		if($pub_key['bits'] < 2047)
		{
			$this->sendToCurrentClient(['type'=>'error'],103,"rsa位数不对,您的位数是" . $pub_key['bits']);
			return;
		}

		//昵称已经被注册，且不是当前用户
		if($this->register_by_RSA->exists($client_name) && $this->register_by_RSA->getKeyByName($client_name) != $rsa_public_key)
		{
			$this->sendToCurrentClient(['type'=>'error'],104,"该昵称已经被占用");
			return;
		}
		else
		{
			$this->register_by_RSA->registerByRSA($client_name,$rsa_public_key);
		}

		$this->client_name_list->add($room_id,$client_name);
		$_SESSION['room_id'] = $room_id;
		$_SESSION['client_name'] = $client_name;

		// 获取房间内所有用户列表
		$clients_list = Gateway::getClientSessionsByGroup($room_id);
		foreach($clients_list as $tmp_client_id=>$item)
		{
			$clients_list[$tmp_client_id] = $item['client_name']??"";
		}
		$clients_list[$client_id] = $client_name;

		// 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
		$new_message = ['type'=>'new_user_login', 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name), 'created_at'=>date('Y-m-d H:i:s')];
		$this->sendToGroup($room_id,$new_message);
		Gateway::joinGroup($client_id, $room_id);

		// 给当前用户发送用户列表
		$new_message['client_list'] = $clients_list;
		$new_message['type'] = 'reply_login';
		$this->sendToCurrentClient($new_message);
		return;
	}

	public function say($client_id,array $message):void
	{
		if(!isset($_SESSION['room_id']))
		{
			throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
		}
		$room_id = $_SESSION['room_id'];
		$client_name = $_SESSION['client_name'];

		//上次发言时间
		$previous_time = $_SESSION['previous_time']??0;
		if(time()-$previous_time < 2)
		{
			$this->sendToCurrentClient(['type'=>'error'],201,"发言速度过快");
			return;
		}

		// 私聊
		if($message['to_client_id'] != 'all')
		{
			$new_message = array(
				'type'=>'say',
				'from_client_id'=>$client_id,
				'from_client_name' =>$client_name,
				'to_client_id'=>$message['to_client_id'],
				'content'=>"<b>对你说: </b>".nl2br(htmlspecialchars($message['content'])),
				'time'=>date('Y-m-d H:i:s'),
			);
			Gateway::sendToClient($message['to_client_id'], json_encode(["code"=>0,"msg"=>"成功","data"=>$new_message]));
			$new_message['content'] = "<b>你对".htmlspecialchars($message['to_client_name'])."说: </b>".nl2br(htmlspecialchars($message['content']));
			$this->sendToCurrentClient($new_message);
			return;
		}

		//日志保存 如果没有设置CHAT_LOG_TYPE就不会保存
		$chat_log_type = getenv("CHAT_LOG_TYPE");
		$chatLog = new \App\Service\ChatLog($chat_log_type);
		$chatLog->add($_SERVER['REMOTE_ADDR'],$client_name,$message['content']);

		$new_message = array(
			'type'=>'say',
			'from_client_id'=>$client_id,
			'user'=>[
				'name' =>$client_name,
				'avatar'=>'https://tva3.sinaimg.cn/crop.19.12.155.155.180/659c6c35gw1f3swxjt6ooj2050050q30.jpg'
			],
			'to_client_id'=>'all',
			'content'=>nl2br(htmlspecialchars($message['content'])),
			'created_at'=>date('Y-m-d H:i:s'),
		);
		$_SESSION['previous_time'] = time();
		$this->sendToGroup($room_id ,$new_message);
		return;
	}


	public function onClose($client_id)
	{
		// 从房间的客户端列表中删除
		if(isset($_SESSION['room_id']))
		{
			$room_id = $_SESSION['room_id'];
			$client_name = $_SESSION['client_name'];
			$new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('Y-m-d H:i:s'));
			$this->client_name_list->remove($room_id,$client_name);
			$this->sendToGroup($room_id, $new_message);
		}
	}
}