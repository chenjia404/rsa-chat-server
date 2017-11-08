<?php
/**
 * @author: chenjia404
 * @Date  : 2017-11-08
 * @Time  : 20:52
 */

namespace App\Service;


class RegisterByRSA
{

	protected $register_by_rsa_file = 'register_by_rsa.json';

	private $register_by_rsa;

	public function __construct()
	{

		if(!file_exists($this->getRegisterByRSAFilename()))
		{
			file_put_contents($this->getRegisterByRSAFilename(),json_encode(array()));
		}

		$this->register_by_rsa = json_decode(file_get_contents($this->getRegisterByRSAFilename()),true);
	}

	/**
	 * 获取项目根目录
	 * @return string
	 */
	public function getRootDir()
	{
		return dirname(dirname(dirname(__DIR__)));
	}


	public function getRegisterByRSAFilename()
	{
		return $this->getRootDir() . DIRECTORY_SEPARATOR . $this->register_by_rsa_file;
	}

	/**
	 * 检查昵称是否重复
	 * @param int    $room_id 房间id
	 * @param string $name    客户端昵称
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($this->register_by_rsa[ $name ]);
	}


	public function getKeyByName($name)
	{
		return $this->register_by_rsa[$name]??"";
	}


	public function registerByRSA($name,$rsa_public_key)
	{
		if(!isset($this->register_by_rsa[$name]))
		{
			$this->register_by_rsa[$name] = $rsa_public_key;
			file_put_contents($this->getRegisterByRSAFilename(),json_encode($this->register_by_rsa,JSON_UNESCAPED_UNICODE));
			return true;
		}
		else
			return false;
	}
}