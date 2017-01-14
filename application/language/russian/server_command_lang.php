<?php

$lang['server_command_title_index'] 						= 'АдминПанель :: Команда';
$lang['server_command_header_index'] 						= 'Команда';
$lang['server_command_title_console_view'] 					= 'АдминПанель :: Просмотр консоли';
$lang['server_command_header_console_view'] 				= 'Просмотр консоли';

$lang['server_command_ban_command_sended'] 					= 'Команда бана отправлена на сервер';
$lang['server_command_kick_command_sended'] 				= 'Игрок кикнут';
$lang['server_command_nickchange_command_sended'] 			= 'Команда смены ника отправлена на сервер';
$lang['server_command_msg_command_sended'] 					= 'Сообщение отправлено';
$lang['server_command_mapchange_command_sended'] 			= 'Команда смены карты на &quot;%s&quot; отправлена на сервер';
$lang['server_command_restart_cmd_sended'] 					= 'Команда перезагрузки отправлена на сервер';
$lang['server_command_password_set'] 						= 'Пароль задан';
$lang['server_command_cmd_sended'] 							= 'Команда отправлена на сервер';
$lang['server_command_sent_cmd'] 							= 'Отправленная команда';
$lang['server_command_answer'] 								= 'Ответ сервера';
$lang['server_command_cmd'] 								= 'Команда';
$lang['server_command_file'] 								= 'Файл';
$lang['server_command_console'] 							= 'Консоль сервера';
$lang['server_command_started'] 							= 'Сервер успешно запущен';
$lang['server_command_stopped'] 							= 'Сервер успешно остановлен';
$lang['server_command_restarted'] 							= 'Сервер успешно перезапущен';
$lang['server_command_start_confirm'] 						= 'Вы уверены, что хотите запустить сервер?';
$lang['server_command_stop_confirm'] 						= 'Вы уверены, что хотите остановить сервер?';
$lang['server_command_restart_confirm'] 					= 'Вы уверены, что хотите перезагрузить сервер?';
$lang['server_command_update_confirm'] 						= 'Вы уверены, что хотите обновить сервер?';

$lang['server_command_start_adm_msg']						= '<p>Проверьте правильность указания следующих директорий: директория к исполняемым файлам(server.exe или server.sh), директория игрового сервера (относительно директории к исполняемым файлам). Проверьте правильность указания команды. <a href="http://wiki.hldm.org/index.php/АдминПанель:Документация_для_пользователей" target="blank">Справка по игровым серверам</a>, <a href="http://wiki.hldm.org/index.php/%D0%90%D0%B4%D0%BC%D0%B8%D0%BD%D0%9F%D0%B0%D0%BD%D0%B5%D0%BB%D1%8C:%D0%A0%D0%B5%D1%88%D0%B5%D0%BD%D0%B8%D0%B5_%D0%BF%D1%80%D0%BE%D0%B1%D0%BB%D0%B5%D0%BC_%D1%81_%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA%D0%BE%D0%BC_%D1%81%D0%B5%D1%80%D0%B2%D0%B5%D1%80%D0%BE%D0%B2" target="blank">Решение проблем с запуском серверов</a></p>';

// Errors
$lang['server_command_form_unavailable'] 					= 'Неправильно заполнена форма';
$lang['server_command_rcon_command_not_found'] 				= 'RCON команда не найдена';
$lang['server_command_rcon_command_access_denied'] 			= 'Вам запрещено использовать данную команду';
$lang['server_command_server_down'] 						= 'Сервер выключен';
$lang['server_command_no_players_privileges'] 				= 'У Вас нет прав управления игроками на этом сервере';
$lang['server_command_no_console_privileges'] 				= 'Отсутствуют права на просмотр консоли сервера';
$lang['server_command_no_start_privileges'] 				= 'Вы не можете запускать этот сервер (отсутствуют права)';
$lang['server_command_no_stop_privileges'] 					= 'Отсутствуют права на остановку сервера';
$lang['server_command_no_restart_privileges'] 				= 'Отсутствуют права на перезагрузку сервера';
$lang['server_command_no_update_privileges'] 				= 'Отсутствуют права на обновление сервера';
$lang['server_command_not_available_for_windows'] 			= 'Просмотр консоли недоступен для игровых серверов под управлением ОС Windows';
$lang['server_command_ssh_not_set'] 						= 'Не настроен SSH';
$lang['server_command_ssh_not_module'] 						= 'Отсутствует SSH2 модуль php';
$lang['server_command_ftp_not_set'] 						= 'Не настроен FTP';
$lang['server_command_telnet_not_set'] 						= 'Не настроен Telnet';
$lang['server_command_console_not_param'] 					= 'Не заданы параметры получения данных консоли';
$lang['server_command_start_not_param'] 					= 'Не заданы параметры запуска сервера';
$lang['server_command_stop_not_param'] 						= 'Не заданы параметры остановки сервера';
$lang['server_command_restart_not_param'] 					= 'Не заданы параметры перезагрузки сервера';
$lang['server_command_update_not_param'] 					= 'Не заданы параметры обновления сервера';
$lang['server_command_no_data'] 							= 'АдминПанель не получила данные от сервера';
$lang['server_command_server_not_found'] 					= 'Ошибка. Возможно указанного Вам сервера не существует';
$lang['server_command_server_is_already_running'] 			= 'Сервер уже запущен. Если подключиться к нему невозможно, попробуйте его <a href="%s">перезапустить</a>, либо <a href="%s">остановить</a>';
$lang['server_command_running_server_not_found'] 			= 'Запущенный сервер не найден';
$lang['server_command_restart_running_server_not_found'] 	= 'Запущенный сервер не найден. Сервер будет запущен.';
$lang['server_command_start_failed'] 						= 'Не удалось запустить сервер';
$lang['server_command_stop_failed'] 						= 'Не удалось остановить сервер';
$lang['server_command_restart_failed'] 						= 'Не удалось перезапустить сервер';
$lang['server_command_update_failed'] 						= 'Ошибка обновления';
$lang['server_command_start_file_not_found'] 				= 'Исполняемый файл не найден';
$lang['server_command_start_file_not_executable'] 			= 'У исполняемого файла отсутствуют права на запуск';

// 0.8.10
$lang['server_command_update_task_exists']					= 'Задание обновления уже имеется, установите для него новую дату.';
$lang['server_command_max_tasks']							= 'Данных заданий не может быть более трех, удалите существующие задания или установите им новую дату.';

// 0.9
$lang['server_command_connection_failed']					= 'Соединение не удалось';
$lang['server_command_empty_connect_data']					= 'Не заданы данные для соединения';
$lang['server_command_empty_auth_data']						= 'Не заданы данные для авторизации';
$lang['server_command_empty_command']						= 'Пустая команда';
$lang['server_command_not_connected']						= 'Отсутствует соединение';
$lang['server_command_login_failed']						= 'Авторизация не удалась';
$lang['server_command_exec_disabled']						= 'Функция exec отключена в настройках PHP';

$lang['server_command_file_not_found']						= 'Файл %s не найден';
$lang['server_command_file_not_readable']					= 'Нет прав на чтение файла %s';
$lang['server_command_file_not_writable']					= 'Нет прав на запись файла %s';
$lang['server_command_file_not_executable']					= 'Нет прав на выполнение файла %s';

// 0.9.3
$lang['server_command_player_not_found']					= 'Игрок не найден.';

// 0.9.4
$lang['server_command_wait_one_minute_to_restart']			= 'С момента последнего перезапуска должна пройти одна минута.';
$lang['server_command_wait_one_minute_to_start']			= 'С момента последнего запуска должна пройти одна минута.';

// 1.0
$lang['server_command_gs_disabled']							= 'Игровой сервер деактивирован. Обратитесь к администратору.';
$lang['server_command_ds_disabled']							= 'Выделенный сервер отключен. Обратитесь к администратору.';

$lang['server_command_get_response_failed']					= 'Ответ на запрос не получен.';
