<?php
/**
 * @author: chenjia404
 * @Date  : 2017-11-04
 * @Time  : 16:48
 */

namespace App\Service;


class ClientName
{
	public function __construct()
	{

	}

	/**
	 * 检查昵称是否重复
	 * @param int    $room_id 房间id
	 * @param string $name    客户端昵称
	 * @return bool
	 */
	public function exists($room_id,$name)
	{
		global $db;
		$exists = $db->select("id")
			->from("client_name_list")
			->where('name=:name AND room_id= :room_id')
			->bindValues([
				'name'    => $name,
				'room_id' => $room_id
			])->query();

		return isset($exists[ 0 ]);
	}

	/**
	 * 记录房间昵称
	 */
	public function add($room_id,$name)
	{
		global $db;
		$db->insert('client_name_list')->cols([
			'room_id' => $room_id,
			'name'    => $name
		])->query();
	}


	/**
	 * 移除昵称记录
	 * @param int $room_id
	 * @param     $name
	 */
	public function remove($room_id,$name)
	{
		global $db;
		$db->delete('client_name_list')
			->where('name=:name AND room_id= :room_id')
			->bindValues([
			'name'    => $name,
			'room_id' => $room_id
		])->query();
	}

	/**
	 * 清空昵称记录，最好的开启聊天室的时候操作
	 */
	public function removeAll()
	{
		global $db;
		$db->query("truncate client_name_list");
	}
}