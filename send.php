<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta charset="utf-8">
		<title>Workman: тест websocket</title>
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">		
	</head>
	
	<body>
		<div class="container mt-5">
			<div class="form-group w-50">
				USER
				<input class="form-control" id="user" value="tester444">
			</div>
			<div class="form-group w-50">
				MESSAGE
				<input class="form-control" id="message">
			</div>
			<div class="form-group w-50 text-right">
				<button class="btn btn-success" id="send_message">Отправить</button>
			</div>
		</div>

		
		<script type="text/javascript" src="https://workman.info-gkh.com.ua/webmessaging/webmessaging.js"></script>
		<script>
			"use strict";
			
			var icon = 'https://fru-gkh.com.ua/application/tpl/images/logo.png';
			var click_action = 'https://workman.info-gkh.com.ua/';
			
			$('#send_message').on('click', function () {
				var user = $('#user').val();
				var message = {	
					'notification': {
						'title': 'TEST: ' + new Date(), 
						'body': $('#message').val(),
						'icon': icon,			
						'click_action': click_action
					}
				};
				
				
				// user может так же быть массивом пользователей
				// var user = ['tester01', 'tester22']; 
				webmessaging.send_message(user, message);

			});
		</script>
		
	</body>
</html>
