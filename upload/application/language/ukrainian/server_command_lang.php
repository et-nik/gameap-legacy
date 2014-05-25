<?php

$lang['server_command_title_index'] = 'GameAP :: Команда';
$lang['server_command_header_index'] = 'Команда';
$lang['server_command_title_console_view'] = 'GameAP :: Перегляд консолі';
$lang['server_command_header_console_view'] = 'Перегляд консолі';

$lang['server_command_ban_command_sended'] = 'Команда бана відправлена ​​на сервер';
$lang['server_command_kick_command_sended'] = 'Гравець кікнутий';
$lang['server_command_nickchange_command_sended'] = 'Команда зміни ника відправлена ​​на сервер';
$lang['server_command_msg_command_sended'] = 'Команда зміни ника відправлена ​​на сервер';
$lang['server_command_mapchange_command_sended'] = 'Команда зміни карти на "%s" відправлена ​​на сервер';
$lang['server_command_restart_cmd_sended'] = 'Команда перезавантаження відправлена ​​на сервер';
$lang['server_command_password_set'] = 'Пароль заданий';
$lang['server_command_cmd_sended'] = 'Команда відправлена ​​на сервер';
$lang['server_command_sent_cmd'] = 'Відправлена ​​команда';
$lang['server_command_answer'] = 'Відповідь сервера';
$lang['server_command_cmd'] = 'Команда';
$lang['server_command_file'] = 'Файл';
$lang['server_command_console'] = 'Консоль сервера';
$lang['server_command_started'] = 'Сервер успішно запущений';
$lang['server_command_stopped'] = 'Сервер успішно зупинений';
$lang['server_command_restarted'] = 'Сервер успішно перезапущений';
$lang['server_command_start_confirm'] = 'Ви впевнені, що хочете запустити сервер?';
$lang['server_command_stop_confirm'] = 'Ви впевнені, що хочете зупинити сервер?';
$lang['server_command_restart_confirm'] = 'Ви впевнені, що хочете перезавантажити сервер?';
$lang['server_command_update_confirm'] = 'Ви впевнені, що хочете оновити сервер?';

$lang['server_command_start_adm_msg'] = '<p> Перевірте правильність вказівки наступних директорій: директорія до виконуваних файлів (server.exe або server.sh), директорія ігрового сервера (щодо директорії до виконуваних файлів). Перевірте правильність вказівки команди. <a target="blank">Довідка з ігрових серверів</a>, <a target="blank">Вирішення проблем з запуском серверів</a></p> ';

// Errors
$lang['server_command_form_unavailable'] = 'Неправильно заповнена форма';
$lang['server_command_rcon_command_not_found'] = 'RCON команда не знайдена';
$lang['server_command_rcon_command_access_denied'] = 'Вам заборонено використовувати дану команду';
$lang['server_command_server_down'] = 'Сервер вимкнений';
$lang['server_command_no_players_privileges'] = 'У Вас немає прав керування гравцями на цьому сервері';
$lang['server_command_no_console_privileges'] = 'Відсутні права на перегляд консолі сервера';
$lang['server_command_no_start_privileges'] = 'Ви не можете запускати цей сервер (відсутні права)';
$lang['server_command_no_stop_privileges'] = 'Відсутні права на зупинку сервера';
$lang['server_command_no_restart_privileges'] = 'Відсутні права на перезавантаження сервера';
$lang['server_command_no_update_privileges'] = 'Відсутні права на оновлення сервера';
$lang['server_command_not_available_for_windows'] = 'Перегляд консолі недоступний для ігрових серверів під управлінням ОС Windows';
$lang['server_command_ssh_not_set'] = 'Чи не налаштований SSH';
$lang['server_command_ssh_not_module'] = 'Відсутня SSH2 модуль php';
$lang['server_command_ftp_not_set'] = 'Чи не налаштований FTP';
$lang['server_command_telnet_not_set'] = 'Чи не налаштований Telnet';
$lang['server_command_console_not_param'] = 'Чи не задані параметри отримання даних консолі';
$lang['server_command_start_not_param'] = 'Чи не задані параметри запуску сервера';
$lang['server_command_stop_not_param'] = 'Чи не задані параметри зупинки сервера';
$lang['server_command_restart_not_param'] = 'Чи не задані параметри перезавантаження сервера';
$lang['server_command_update_not_param'] = 'Чи не задані параметри оновлення сервера';
$lang['server_command_no_data'] = 'GameAP не отримала дані від сервера';
$lang['server_command_server_not_found'] = 'Помилка. Можливо зазначеного Вам сервера не існує ';
$lang['server_command_server_is_already_running'] = 'Сервер вже запущений. Якщо підключитися до нього неможливо, спробуйте його <a href="%s"> перезапустити </ a>, або <a href="%s"> зупинити </ a> ';
$lang['server_command_running_server_not_found'] = 'Запущений сервер не знайдений. Можливо він був зупинений раніше. ';
$lang['server_command_restart_running_server_not_found'] = 'Запущений сервер не знайдений. Сервер буде запущений. ';
$lang['server_command_start_failed'] = 'Не вдалося запустити сервер';
$lang['server_command_stop_failed'] = 'Не вдалося зупинити сервер';
$lang['server_command_restart_failed'] = 'Не вдалося перезапустити сервер';
$lang['server_command_update_failed'] = 'Помилка поновлення';
$lang['server_command_start_file_not_found'] = 'Виконуваний файл не знайдено';
$lang['server_command_start_file_not_executable'] = 'У виконуваного файлу відсутні права на запуск';

// 0.8.10
$lang['server_command_update_task_exists'] = 'Завдання поновлення вже є , встановіть для нього нову дату. ';
$lang['server_command_max_tasks'] = 'Даних завдань не може бути більше трьох , видаліть існуючі завдання або встановіть їм нову дату. ';

// 0.9
$lang['server_command_connection_failed'] = 'З\'єднання не вдалося ';
$lang['server_command_empty_connect_data '] = 'Не задано дані для з\'єднання ';
$lang['server_command_empty_auth_data'] = 'Не задано дані для авторизації';
$lang['server_command_empty_command'] = 'Порожня команда ';
$lang['server_command_not_connected'] = 'Відсутня з\'єднання ';
$lang['server_command_login_failed'] = 'Авторизація не вдалася ';
$lang['server_command_exec_disabled'] = 'Функція exec відключена в налаштуваннях PHP';

$lang['server_command_file_not_found'] = 'Файл% s не найден';
$lang['server_command_file_not_readable'] = 'Немає прав на читання файлу %s';
$lang['server_command_file_not_writable'] = 'Немає прав на запис файлу %s';
$lang['server_command_file_not_executable'] = 'Немає прав на виконання файлу %s';

// 0.9.3
$lang['server_command_player_not_found'] = 'Равець не є знайдений.';
