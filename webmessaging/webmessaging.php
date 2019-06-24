<?
header('Access-Control-Allow-Origin: *');  
 
$host		= 'localhost';
$database	= 'infogkhc__general';
$user		= 'infogkhc_workman'; 
$password	= 'ertYmanTAlys';

// запуск сервера, если скрипт не смог соединиться
if (isset($_POST['start_server'])) {
	shell_exec('/home/infogkhc/public_html/workman-websocket/server.php start -d');
}


if (isset($_POST['send_message'])) {
	
	$users = is_array($_POST['user']) ? $_POST['user'] : array($_POST['user']);
	
	/////////////////////////////////////////////////////////////////
	//// {{{  получаем все токены пользователя и добавляем их в json
	$db = mysqli_connect($host, $user, $password, $database) 
		or die("Ошибка " . mysqli_error($db));

	$query = sprintf("SELECT * FROM `webmessaging` WHERE `user` IN (%s)",
		"'" . implode("','", $users). "'"
	);	
	$result = mysqli_query($db, $query) 
		or die("Ошибка " . mysqli_error($db)); 
	
	$tokens = array();
	while ($usr = $result->fetch_assoc()) {
		for ($i = 1; $i <= 5; $i++) {	
			if( $usr["token$i"] ) $tokens[] = $usr["token$i"];
		}
	}
	
	$message['registration_ids'] = $tokens;
	$message['android'] = array('ttl' => '2419200s');
	$message = json_encode(array_merge($message, $_POST['message']));
	$db->close();
	//// }}}
	
	/////////////////////////////////////////////////////////////////
	//// {{{ наш сервер, тут просто отправляем действие по web socket
	$localsocket = 'tcp://127.0.0.1:1234';
	// соединяемся с локальным tcp-сервером
	$instance = stream_socket_client($localsocket);
	// отправляем сообщение
	foreach ($users as $usr) {
		fwrite($instance, json_encode(['user' => $usr, 'message' => $message])  . "\n");
	
	}
	//// }}}

	/////////////////////////////////////////////////
	//// {{{ шлем web push уведомление через firebase
	$url = 'https://fcm.googleapis.com/fcm/send';
	$YOUR_API_KEY = 'AAAA2ZsSVA4:APA91bH3adqx0k4n3WtrnKhWco9ML2nab7X-T9G78ksDwEaRWOlU9EWXyK1ceCexC4pGlnK4_Zd1wv9964E5pHW5DOi8TdepZgaHbbgkObZfLiXEAtV8LVrOau8WbCaeXE8QOna3Ibq4'; // Server key

	$request_headers = [		
		'Content-Type: application/json',		
		'Authorization: key=' . $YOUR_API_KEY,	
	];	
		
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, $url);	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $message);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	
	$response = curl_exec($ch);	
	curl_close($ch);
	//// }}}
	
	echo $message;
}

if (isset($_POST['send_token'])) {
	
	$db = mysqli_connect($host, $user, $password, $database) 
		or die("Ошибка " . mysqli_error($db));
	
	// был ли у нас такой пользователь раньше?
	$query = sprintf("SELECT * FROM `webmessaging` WHERE `user`='%s'",
		$_POST['user']
	);
	$result = mysqli_query($db, $query) 
		or die("Ошибка " . mysqli_error($db)); 
	
	if ($result->num_rows) {
		// пользователь уже приходил
		$usr = $result->fetch_assoc();
		
		// ассоциируется ли текущий токен с юзером?
		if ( !in_array($_POST['token'], $usr) ) {
			// ищем пустую ячейку для токена
			$key = array_search('', $usr);
			// если не нашли - берем случайную
			$key = ($key) ? $key : 'token' . mt_rand(1, 5);
			// записываем новый токен
			$query = sprintf("UPDATE `webmessaging` SET `%s`='%s' WHERE `user`='%s'",
				$key,
				$_POST['token'],
				$_POST['user']
			);
			$result = mysqli_query($db, $query) 
				or die("Ошибка " . mysqli_error($db)); 
		}
	} else {
		// новый пользователь
		$query = sprintf("INSERT INTO `webmessaging` (`user`, `token1`) VALUES ('%s', '%s')",
			$_POST['user'],
			$_POST['token']
		);
		$result = mysqli_query($db, $query) 
			or die("Ошибка " . mysqli_error($db)); 
	}
	
	$db->close();
	
	echo TRUE;
}