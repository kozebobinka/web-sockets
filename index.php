<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta charset="utf-8">
		<title>Workman</title>
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
		
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">	
	</head>
	
	<body>
		<div class="container mt-5">
			<h4 class="text-danger"></h4>
		</div>
		<div class="container mt-5">
			<p>Чтобы получать уведомления о новых задачах нажмите на кнопку ниже и во всплывающем окне нажмите "Разрешить"</p>
			<button type="button" class="btn btn-danger" id="subscribe">Подписаться</button>
		</div>
		
		<div class="container mt-5">
			<textarea class="chat form-control" rows="10"></textarea>
		</div>
		<div class="container mt-5">
			<a href="https://workman.info-gkh.com.ua/send.php" target="_blank">Для теста отправлять сообщения отсюда</a>
		</div>
		
		<div class="container mt-5">
			<h5 class="text-success">Чтобы пользоваться модулем надо:</h5>
			<p>1. Подключить в файле, в который надо отправлять сообщения:</p>
			<code  class="text-muted card">
				<?=htmlspecialchars('<script type="text/javascript" src="//www.gstatic.com/firebasejs/3.6.8/firebase.js"></script>')?>
				<br>
				<?=htmlspecialchars('<script type="text/javascript" src="https://workman.info-gkh.com.ua/webmessaging/webmessaging.js"></script>')?>
			</code>
			<br>
			<p>2. Подключить в файле, из которого отправляем сообщения:</p>
			<code  class="text-muted card">
				<?=htmlspecialchars('<script type="text/javascript" src="https://workman.info-gkh.com.ua/webmessaging/webmessaging.js"></script>')?>
			</code>
			<br>
			<p>3. Скопировать следующие файлы В КОРЕНЬ сайта, на который отправляем сообщения:</p>
			<code  class="text-muted card">
				firebase-messaging-sw.js<br>
				firebase-onmessage-sw.js
			</code>
			<br>
			<p>4. Взять скелет javascript кода из файлов index.php для приемника и send.php для отправителя, написать в них нужные вам действия в функциях и назначить нужные переменные.</p>	
		</div>
		<div class="py-5 my-5">
		</div>
		
		
		<script type="text/javascript" src="//www.gstatic.com/firebasejs/3.6.8/firebase.js"></script>
		<script type="text/javascript" src="https://workman.info-gkh.com.ua/webmessaging/webmessaging.js"></script>
		
		<script>		
			"use strict";
			
			// Идентификатор кнопки "Подписаться"
			var subscribe_ident = '#subscribe';
			
			// если уведомления разрешены, прячем кнопку "подписаться"
			if (Notification.permission === 'granted') {
				$(subscribe_ident).parent().html('<span class="text-success"><strong>Вы подписаны на наши уведомления, спасибо!</strong></span>');
			}
			// если пользователь уже заблочил сообщения, надо ему этойто рассказать
			if (Notification.permission === 'denied') {
				$(subscribe_ident).parent().html(
				'<span class="text-danger"><strong>У вас заблокированы уведомления с сайта. Чтобы разблокировать их, следуйте инструкциям из этой статьи:<br>' +
				'<a target="_blank" href="https://push.world/help/unblock-push">КАК РАЗБЛОКИРОВАТЬ ПОДПИСКУ НА PUSH</a></strong></span>');
			}
			
			// Идентификатор пользователя. Лучше постараться делать что-то уникальное и с намеком из какого он модуля, типа sip_vasya и ads_petya
			var user = 'tester444';
			
			// Действия при получении сообщения		
			function on_message_come(message)
			{
				$('.chat').text($('.chat').text() + message.notification.body + "\n-------\n");		
			}
			
			// Действие если не получается соединиться		
			function on_connection_error()
			{
				$('h4').html('Сервер не запущен. Запуск:<br><code>/home/infogkhc/public_html/workman-websocket/server.php start -d</code>');
			}
			
			// запускаем сообщения
			webmessaging.start_listen(user, on_message_come, on_connection_error, subscribe_ident);
		</script>
		
	</body>
</html>

