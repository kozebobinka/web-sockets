#!/usr/local/bin/php
<?//
	require_once  __DIR__.'/vendor/autoload.php';
	use Workerman\Worker;
	
	//////// {{{ ищем актуальные сертификат и ключ
	$ssl_patch = '/home/infogkhc/ssl';
	$domain = 'ds_info_gkh_com_ua';
	
	$max_time = 0;
	$current_cert = '';
	foreach (glob("$ssl_patch/certs/{$domain}_*.crt") as $cert) {
		$file_time = filemtime($cert);
		if ($file_time > $max_time) {
			$max_time = $file_time;
			$current_cert = $cert;
		}
	}
	
	if ($current_cert == '') {
		echo "Certificate not found.";
		return;
	}
	
	$last_cert_id = substr($current_cert, strlen("$ssl_patch/certs/$domain")+1, 11);
	
	$current_key = current(glob("$ssl_patch/keys/$last_cert_id*.key"));
	//////// }}}
	
	
	// SSL context.
	$context = array(
		'ssl' => array(
			'local_cert'  => $current_cert,
			'local_pk'    => $current_key,
			'verify_peer' => false,
		)
	);
	
	// создаём ws-сервер, к которому будут подключаться все наши пользователи
	$ws_worker = new Worker("websocket://0.0.0.0:2346", $context);
	
	// Enable SSL. WebSocket+SSL means that Secure WebSocket (wss://). 
	// The similar approaches for Https etc.
	$ws_worker->transport = 'ssl';
	
	
	
	// массив для связи соединения пользователя и необходимого нам параметра
	$users = [];
	
	// создаём обработчик, который будет выполняться при запуске ws-сервера
	$ws_worker->onWorkerStart = function() use (&$users)
	{
		// создаём локальный tcp-сервер, чтобы отправлять на него сообщения из кода нашего сайта
		$inner_tcp_worker = new Worker("tcp://127.0.0.1:1234");
		// создаём обработчик сообщений, который будет срабатывать,
		// когда на локальный tcp-сокет приходит сообщение
		$inner_tcp_worker->onMessage = function($connection, $data) use (&$users) {
			$data = json_decode($data);
			// отправляем сообщение пользователю по userId
			if (isset($users[$data->user])) {
				$webconnection = $users[$data->user];
				$webconnection->send(($data->message) ? $data->message : '');
			}
		};
		$inner_tcp_worker->listen();
	};

$ws_worker->onConnect = function($connection) use (&$users)
{
	$connection->onWebSocketConnect = function($connection) use (&$users)
	{
		// при подключении нового пользователя сохраняем get-параметр, который же сами и передали со страницы сайта
		$users[$_GET['user']] = $connection;
	};
};

$ws_worker->onClose = function($connection) use(&$users)
{
	// удаляем параметр при отключении пользователя
	$user = array_search($connection, $users);
	unset($users[$user]);
};

// Run worker
Worker::runAll();