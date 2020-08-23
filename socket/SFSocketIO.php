<?php


use Workerman\Worker;

class SFSocketIO extends \PHPSocketIO\SocketIO
{
	public function __construct($host, $port = null, $opts = array())
	{
		$nsp = isset($opts['nsp']) ? $opts['nsp'] : '\PHPSocketIO\Nsp';
		$this->nsp($nsp);

		$socket = isset($opts['socket']) ? $opts['socket'] : '\PHPSocketIO\Socket';
		$this->socket($socket);

		$adapter = isset($opts['adapter']) ? $opts['adapter'] : '\PHPSocketIO\DefaultAdapter';
		$this->adapter($adapter);
		if(isset($opts['origins']))
		{
			$this->origins($opts['origins']);
		}

		unset($opts['nsp'], $opts['socket'], $opts['adapter'], $opts['origins']);

		$this->sockets = $this->of('/');

		if(!class_exists('Protocols\SocketIO'))
		{
			class_alias('PHPSocketIO\Engine\Protocols\SocketIO', 'Protocols\SocketIO');
		}
		if($port)
		{
			$worker = new Worker('SocketIO://'.$host.':'.$port, $opts);
			$worker->name = 'PHPSocketIO';

			if(isset($opts['ssl'])) {
				$worker->transport = 'ssl';
			}

			$this->attach($worker);
		}
	}
}
