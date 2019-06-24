(function (G, U) {
    "use strict";
	
	var UI = G.webmessaging || {};
	
	var server_address = "wss://workman.info-gkh.com.ua:2346/";
	var firebase_id = '934609572878' // наш id в https://console.firebase.google.com/, проект: fru-gkh, владелец на текущий момент: kozebobinka@gmail.com
	
	var user;
	
	var on_message_come;
	var on_connection_error;
	
	var send_token_to_server;
	
	var ws;
	var messaging;
	
	var url_php = 'https://workman.info-gkh.com.ua/webmessaging/webmessaging.php';
	
	// метод для запуска
    function start_listen(user_to, func_succes, func_error, subscribe_ident) 
	{
		
		user = user_to;
		on_message_come = func_succes;
		on_connection_error = func_error;
		
		start_websocket();
		
		// поддерживает ли браузер уведомления
		if ('Notification' in window) {
		
			firebase.initializeApp({
				messagingSenderId: firebase_id
			});
		
			messaging = firebase.messaging();
			// по клику, запрашиваем у пользователя разрешение на уведомления и подписываем его
			$(subscribe_ident ? subscribe_ident : '#subscribe').on('click', function () {
				subscribe();
			});
			// пользователь уже разрешил получение уведомлений подписываем на уведомления если ещё не подписали || Notification.permission === 'default' || Notification.permission === 'denied'
			if ( Notification.permission === 'granted' ) {
				subscribe();
			}
			
			messaging.onMessage(function(payload) {
				// регистрируем ServiceWorker каждый раз
				navigator.serviceWorker.register('firebase-onmessage-sw.js');	
				// запрашиваем права на показ уведомлений если еще не получили их
				Notification.requestPermission(function(result) {
					if (result === 'granted' ) {
						navigator.serviceWorker.ready.then(function(registration) {
							payload.notification.data = payload.notification; // параметры уведомления
							registration.showNotification(payload.notification.title, payload.notification);
						});
					}
				});
			});
		}

	}
	
	// работа с сокетами на нашем сервере
	function start_websocket() 
	{
		// Открываем сокет, передаем имя пользователя, которому будут приходить сюда сообщения
		ws = new WebSocket(server_address + "?user=" + user);
		ws.onerror = function(connection, code, msg){
			// Действие если не получается соединиться		
			on_connection_error()
			$.post(
				url_php, 
				{ 	
					start_server: 1,
				},
			);
			// Попробуем подключиться через минуту
			setTimeout(start_websocket, 60000);
		}
		ws.onmessage = function(evt) {
			// Действия при получении сообщения	
			on_message_come(JSON.parse(evt.data));
		}
		ws.onopen = function() {
			// Костыль для браузеров, которые зачем-то глушат соединение с сервером у бездействующих сайтов.
			keep_alive();
		};
	}
	
	// поддержка соединения в браузере
	function keep_alive() 
	{
		var timeout = 20000;  
		if (ws.readyState === ws.OPEN) {  
// 			console.log("Connected.");
			setTimeout(keep_alive, timeout);  
		} else {
			// на случай потери соединения (перезапуск сервера, например)
			start_websocket();
		}         
	}
	
	// подписка на всплывающие уведомления
	function subscribe() 
	{
	    // запрашиваем разрешение на получение уведомлений
		messaging.requestPermission()
		.then(function () {
			// получаем ID устройства
			messaging.getToken()
			.then(function (current_token) {
				if (current_token) {
					// Отправка токена на сервер
					send_token_to_server(current_token);
				} else {
					console.warn('Не удалось получить токен.');
					send_token_to_server(false);//set_token_send_to_server(false);
				}
			})
			.catch(function (err) {
				console.warn('При получении токена произошла ошибка.', err);
				send_token_to_server(false);//set_token_send_to_server(false);
			});
			
		})
		.catch(function (err) {
			console.warn('Не удалось получить разрешение на показ уведомлений.', err);
			console.log(Notification.permission,'ff');
		});
	}
	
	// Отправка токена на сервер
	function send_token_to_server(current_token)
	{	
		$.post (
			url_php, 
			{
				send_token: 1,
				user: user,
				token: current_token,
			}
		);	
	}
	
	// метод для отправки сообщения
	function send_message(user, message)
	{
	    $.post(
			url_php, 
			{ 	
				send_message: 1,
				user: user,
				message: message,
			},
		);
	}
	
	UI.start_listen = start_listen;
	UI.send_message = send_message;
	
	G.webmessaging = UI;
	
}(this, undefined));