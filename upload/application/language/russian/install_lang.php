<?php

$lang['install_title'] 							= 'Установка АдминПанели';
$lang['install_title_end'] 						= 'Установка завершена!';

$lang['install_gameap_install'] 				= 'Установка Game AdminPanel';
$lang['install_welcome'] 						= 'Вас приветствует мастер установки Game AdminPanel';
$lang['install_welcome_description'] 			= '<p>Данный мастер поможет установить АдминПанель и подготовить ее к дальнейшей работе. Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, отсутствие файлов АдминПанели может сделать некоторые функции или панель в целом неработоспособной.</p>
												<p>АдминПанель - Web-панель управления игровыми серверами. Позволяет управлять серверами таких игр как Half-Life, Counter-Strike, Team Fortress. Написана с использование PHP фреймворка CodeIgniter 2.1.3, благодаря этому панель очень гибка в настройке, расширении модулями. АдминПанель поддерживает несколько операционных систем, работает с самыми популярными базами данных (MySQL, PostgreSQL и др.), поддерживает все игры Valve.</p>';

$lang['install_php_version'] 					= 'Версия PHP';
$lang['install_php_version_on_server'] 			= 'Версия на сервере';
$lang['install_php_recomended_version'] 		= 'Рекомендуемая версия';
$lang['install_php_recomended_version_info'] 	= '5.2.4 и выше';
$lang['install_php_ext'] 						= 'Проверка расширений PHP';
$lang['install_module'] 						= 'Модуль';
$lang['install_status'] 						= 'Статус';
$lang['install_module_ftp'] 					= 'Модуль FTP';
$lang['install_module_json'] 					= 'Модуль Json';
$lang['install_module_gd'] 						= 'Модуль GD';
$lang['install_module_ssh'] 					= 'Модуль SSH2';
$lang['install_not_module'] 					= 'Не установлен';
$lang['install_php_settings'] 					= 'Настройки PHP';
$lang['install_setting'] 						= 'Настройка';
$lang['install_upload_files'] 					= 'Загрузка файлов';
$lang['install_modules_information'] 			= 'Внимание! Из-за отсутствия какого либо расширения PHP либо настройки некоторые функции АдминПанели будут недоступны. Чтобы этого избежать, установите расширения выделенные красным';
$lang['install_dir_chmod'] 						= 'Права на директории';
$lang['install_dir_not_found'] 					= 'Директория отсутствует';
$lang['install_dir_not_writable'] 				= 'Запись запрещена';
$lang['install_dir_writable'] 					= 'Запись разрешена';
$lang['install_dirs_information'] 				= 'Внимание! Из-за отсутствия разрешений записи в некоторые директории некоторые функции АдминПанели будут недоступны. Чтобы этого избежать, установите права записи на выделенные красным директории.';
$lang['install_configuration'] 					= 'Конфигурация';
$lang['install_site_url'] 						= 'URL сайта';
$lang['install_secret_key'] 					= 'Секретный ключ';
$lang['install_system_email'] 					= 'Системный email';
$lang['install_local_os'] 						= 'Локальная ос';
$lang['install_script_path'] 					= 'Путь к исполняемым файлам на Локальной ОС';
$lang['install_steamcmd_path'] 					= 'Путь к SteamCMD на Локальной ОС';
$lang['install_create_admin'] 					= 'Создание администратора';
$lang['install_end_stage'] 						= 'Завершающий этап';
$lang['install_configuration_saved'] 			= 'Конфигурация данных сохранена';
$lang['install_manual_configuration'] 			= 'Создайте файл <strong>"application/config/gameap_config.php"</strong> со следующим содержимым';
$lang['install_end'] 							= '<h2>Благодарим Вас за установку АдминПанели</h2>
													<p>Теперь Вы можете приступить к управлению игровыми серверами. Если что-то непонятно, <a href="http://wiki.hldm.org/%D0%94%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8F_GameAP" target="blank">попробуйте воспользоваться документацией</a>. И не забудьте удалить директорию <font color="red">install_gameap</font> из корневой директории с АдминПанелью!</p>
													<p>Для повторной установки удалите файл "application/config/gameap_config.php"</p>';
													
$lang['install_goto_adminpanel'] 				= 'Поехали';

// 0.7.1
$lang['install_data_base']						= 'База данных';
$lang['install_db_hostname']					= 'Хост базы данных';
$lang['install_db_username']					= 'Пользователь';
$lang['install_db_password']					= 'Пароль базы данных';
$lang['install_db_database']					= 'Имя базы данных';
$lang['install_db_dbdriver']					= 'Драйвер базы данных';
$lang['install_db_dbprefix']					= 'Префикс';
$lang['install_db_error']						= 'Ошибка соединения с базой данных. Проверьте указанные данные';
$lang['install_database_saved']					= 'Конфигурационный файл базы данных создан';

$lang['install_db_pdo']							= 'Драйвер PDO';
