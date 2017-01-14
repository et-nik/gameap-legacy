<?php

$lang['adm_servers_title_index']					= 'GameAP';
$lang['adm_servers_heading_index']					= 'GameAP';
$lang['adm_servers_title_ds']						= 'GameAP :: Dedicated servers';
$lang['adm_servers_heading_ds']						= 'Dedicated servers';
$lang['adm_servers_title_gs']						= 'GameAP :: Game servers';
$lang['adm_servers_heading_gs']						= 'Game servers';
$lang['adm_servers_title_games']					= 'GameAP :: Games';
$lang['adm_servers_heading_games']					= 'Games';
$lang['adm_servers_title_gt']						= 'GameAP :: Game mods';
$lang['adm_servers_heading_gt']						= 'Game mods';
$lang['adm_servers_title_add_ds']					= 'GameAP :: New dedicated server';
$lang['adm_servers_heading_add_ds']					= 'New dedicated server';
$lang['adm_servers_title_add_gs']					= 'GameAP :: New server';
$lang['adm_servers_heading_add_gs']					= 'New server';
$lang['adm_servers_title_add_game']					= 'GameAP :: New game';
$lang['adm_servers_heading_add_game']				= 'New game';
$lang['adm_servers_title_add_game_type']			= 'GameAP :: New game';
$lang['adm_servers_heading_add_game_type']			= 'New game';
$lang['adm_servers_title_install_game_server']		= 'GameAP :: Sever installation';
$lang['adm_servers_heading_install_game_server']	= 'Server installation';

$lang['adm_servers_add_server_successful']			= 'Server added successfully';
$lang['adm_servers_delete_server_successful']		= 'Server deleted successfully';
$lang['adm_servers_back_to_servers']				= 'Back to servers list';
$lang['adm_servers_add_game_successful']			= 'New game successfully added';
$lang['adm_servers_delete_game_successful']			= 'Game deleted successfully';
$lang['adm_servers_back_to_games']					= 'Back to games list';
$lang['adm_servers_add_game_type_successful']		= 'New game mod added successfully';
$lang['adm_servers_delete_game_type_successful']	= 'Game mod deleted successfully';
$lang['adm_servers_back_to_game_types']				= 'Back to mods list';
$lang['adm_servers_delete_ds_confirm']				= 'Do you want delete dedicated server?';
$lang['adm_servers_delete_gs_confirm']				= 'Do you want delete this server?';
$lang['adm_servers_delete_game_confirm']			= 'Do you want delete this game?';
$lang['adm_servers_delete_game_type_confirm']		= 'Do you want delete this game mod?';
$lang['adm_servers_local_server']					= 'Localhost server';
$lang['adm_servers_server_data_changed']			= 'Server settings changed successfully';
$lang['adm_servers_game_data_changed']				= 'Game settings changed successfully';
$lang['adm_servers_game_type_data_changed']			= 'Game mod changed successfully';
$lang['adm_servers_server_to_be_installed']			= 'Server will be installed soon';
$lang['adm_servers_go_to_settings']					= 'Proceed to settings';
$lang['adm_servers_serv_not_installed']				= 'Server not installed';
$lang['adm_servers_serv_installed']					= 'Server installed';
$lang['adm_servers_serv_installed_proccess']		= 'Server in installation procces';

// Errors
$lang['adm_servers_ds_unavailable']					= 'Dedicated servers not found';
$lang['adm_servers_gs_unavailable']					= 'Servers not found';
$lang['adm_servers_games_unavailable']				= 'No games avaliable';
$lang['adm_servers_game_not_found']					= 'Game';
$lang['adm_servers_gt_unavailable']					= 'Game mods not found';
$lang['adm_servers_selected_ds_unavailable']		= 'Dedicated server not found';
$lang['adm_servers_add_server_failed']				= 'Unable to add server';
$lang['adm_servers_delete_server_failed']			= 'Unable to delete server';
$lang['adm_servers_server_not_found']				= 'Server not found';
$lang['adm_servers_game_type_select_wrong']			= 'Incorrect ModID';
$lang['adm_servers_game_type_not_found']			= 'Mod not found';
$lang['adm_servers_no_game_types_for_selected_game']= 'No mods for selected game. <a href="{base_url}adm_servers/add/game_types" target="blank">Add new</a>.';
$lang['adm_servers_add_game_failed']				= 'Failed to add new game';
$lang['adm_servers_delete_game_failed']				= 'Failed to delete this game';
$lang['adm_servers_add_game_type_failed']			= 'Failed to add new game mod';
$lang['adm_servers_delete_game_type_failed']		= 'Failed to delete game mod';
$lang['adm_servers_ds_contains_game_servers']		= 'You cannot delete dedicated servers with associated game servers with it. Delete game servers first.';
$lang['adm_servers_game_contains_game_servers']		= 'You cannot delete game which used servers servers. Delete servers first.';
$lang['adm_servers_game_type_contains_game_servers'] = 'You cannot delete game mods which used for servers. Delete servers first.';
$lang['adm_servers_unknown_page']					= 'Undefined page';
$lang['adm_servers_telnet_data_unavailable']		= 'Telnet data is incorrect. Check  this data first.';
$lang['adm_servers_ssh_data_unavailable']			= 'SSH data is incorrect. Check  this data first.';
$lang['adm_servers_ftp_data_unavailable']			= 'FTP  data is incorrect. Check this data first.';
$lang['adm_servers_error_server_edit']				= 'Failed to edit server settings';
$lang['adm_servers_error_game_edit']				= 'Failed to edit game settings';
$lang['adm_servers_error_game_type_edit']			= 'Failed to edit mod settings';
$lang['adm_servers_no_steamcmd_data']				= 'No SteamCMD settings for selected game';
$lang['adm_servers_base_not_contains_game']			= 'Selected game is not found';
$lang['adm_servers_gs_empty_settings']				= 'Following parametrs are not specified in the settings';
$lang['adm_servers_empty_games_list']				= 'You must add new game first. <a href="%s">Add</a>';

// Templates
$lang['adm_servers_add_ds']							= 'Add new dedicated server';
$lang['adm_servers_location']						= 'Location';
$lang['adm_servers_provider']						= 'Provider';
$lang['adm_servers_ram']							= 'RAM (Mb)';
$lang['adm_servers_cpu']							= 'CPU (MHz)';
$lang['adm_servers_ds_access']						= 'Dedicated server access data';
$lang['adm_servers_ds_access_desc']					= 'Verify that the specified data. The deceptiveness of the above data may make the server or some features unavailable.';
$lang['adm_servers_steamcmd_path']					= 'SteamCMD path';
$lang['adm_servers_steamcmd_path_desc']				= 'Select path where is SteamCMD allocated, and you can install some game servers from GameAP.';
$lang['adm_servers_control_protocol']				= 'Server access protocol';
$lang['adm_servers_default']						= 'Default';
$lang['adm_servers_ds_documentation']				= '';
$lang['adm_servers_game_servers_documentation']		= '';
$lang['adm_servers_games_documentation']			= '';
$lang['adm_servers_game_types_documentation']		= '';
$lang['adm_servers_ftp_data']						= 'FTP details';
$lang['adm_servers_ftp_host']						= 'FTP host(IP:port)';
$lang['adm_servers_path_to_executable_file']		= 'Path for GameAP executable scripts';
$lang['adm_servers_ssh_data']						= 'SSH details';
$lang['adm_servers_ssh_host']						= 'SSH host(IP:port)';
$lang['adm_servers_not_ssh_php']					= 'php_ssh2 module is not installed. You cannot use ssh for access dedicated server.';
$lang['adm_servers_telnet_data']					= 'Telnet details';
$lang['adm_servers_telnet_data_desc']				= 'Telnet usually using with Windows OS. If your server using Linux OS SSH access is prefer.';
$lang['adm_servers_telnet_host']					= 'Telnet host(IP:port)';
$lang['adm_servers_ds_control']						= 'Base settings';
$lang['adm_servers_dedicated_servers']				= 'Dedicated servers';
$lang['adm_servers_game_servers_on_this_ds']		= 'Servers on this DS';
$lang['adm_servers_game_server_name']				= 'Server name';
$lang['adm_servers_game']							= 'Game';
$lang['adm_servers_games']							= 'Games';
$lang['adm_servers_game_server_ip']					= 'Server IP';
$lang['adm_servers_game_servers']					= 'Servers';
$lang['adm_servers_game_server_settings']			= 'Server settings';
$lang['adm_servers_game_server_control']			= 'Server settings';
$lang['adm_servers_count_game_servers']				= 'Servers';
$lang['adm_servers_add_game']						= 'Add new game';
$lang['adm_servers_game_code']						= 'Id';
$lang['adm_servers_game_code_desc']					= 'Unique game id. Maybe like as start code. Exp. valve, cstrike, gearbox.';
$lang['adm_servers_game_code_desc_for_gt_control'] 	= 'Unique game id. Determines which  game belongs to modification (game mod).';
$lang['adm_servers_game_start_code']				= 'Startcode';
$lang['adm_servers_game_start_code_desc']			= 'Startcode uses for server startup as gamedir option. Example, "./hlds_run -game valve", <strong>valve</strong> - startcode';
$lang['adm_servers_engine']							= 'Engine';
$lang['adm_servers_engine_version']					= 'Build';
$lang['adm_servers_steamcmd_parameters']			= 'SteamCMD options';
$lang['adm_servers_game_control']					= 'Game settings';
$lang['adm_servers_new_game']						= 'Add new game';
$lang['adm_servers_new_game_server']				= 'New server';
$lang['adm_servers_add_game_server']				= 'Add new server';
$lang['adm_servers_install_game_server']			= 'Install new server';
$lang['adm_servers_connect_data']					= 'Server IP (IP:port)';
$lang['adm_servers_rcon_password']					= 'RCON password';
$lang['adm_servers_game_type']						= 'Mod';
$lang['adm_servers_local_server']					= 'Localhost';
$lang['adm_servers_server_dir']						= 'Server directory';
$lang['adm_servers_server_dir_desc']				= 'Path is relative with GameAP executable files directory (server.sh or server.exe).';
$lang['adm_servers_absolute_path_to_server']		= 'Server path';
$lang['adm_servers_server_enabled']					= 'Server enabled';
$lang['adm_servers_start_parameters']				= 'Startup parameters';
$lang['adm_servers_dublicate_game_type']			= 'Copy settings';
$lang['adm_servers_edit_start_parameters']			= 'Edit server startup parameters';
$lang['adm_servers_start_parameters_desc']			= 'You can specify following aliases in startup parameters:<br /> {id} - server ID, <br /> {dir} - GameAP executable files directory,<br /> {name} - screen name,<br /> {ip} - IP,<br /> {port} - port,<br /> {game} - game startcode,<br />{user} - run server from this user<br /><br />Example: "./server.sh start {dir} {name} {ip} {port} "hlds_run -game {game} +ip {ip} +port {port} +map crossfire" {user}<br /><br />You can also use your own aliases from game mod settings.<br /><br />';
$lang['adm_servers_screen_name']					= 'Screen name (Linux)';
$lang['adm_servers_user_start']						= 'OS User who will runs this server (Linux)';
$lang['adm_servers_command_start']					= 'Server start command';
$lang['adm_servers_command_restart']				= 'Server restart command';
$lang['adm_servers_command_stop']					= 'Server stop command';
$lang['adm_servers_command_status']					= 'Check server status command';
$lang['adm_servers_command_update']					= 'Server update command';
$lang['adm_servers_command_get_console']			= 'Get server console command';
$lang['adm_servers_add_game_type']					= 'Add new game mod';
$lang['adm_servers_new_game_type']					= 'New game mod';
$lang['adm_servers_disk_size']						= 'Mod size (Mb)';
$lang['adm_servers_game_type_control']				= 'Edit mod settings';
$lang['adm_servers_linux_execute']					= 'Linux executable file';
$lang['adm_servers_linux_execute_desc']				= 'GameAP executable file, that uses for start/restart/stop servers (<b>./server.sh</b>)';
$lang['adm_servers_windows_execute']				= 'Windows executable file';
$lang['adm_servers_windows_execute_desc']			= 'GameAP executable file, that uses for start/restart/stop servers (<b>server.exe</b>)';
$lang['adm_servers_config_files']					= 'Configuration files';
$lang['adm_servers_game_directories']				= 'Server directories';
$lang['adm_servers_log_directories']				= 'Server log directories';
$lang['adm_servers_alias']							= 'Alias';
$lang['adm_servers_aliases']						= 'Aliases';
$lang['adm_servers_only_for_admins']				= 'For admins only';
$lang['adm_servers_server_will_be_reinstalled']		= 'Server will be reinstalled soon';
$lang['adm_servers_reinstall']						= 'Reinstall server';
$lang['adm_servers_reinstall_gs_confirm']			= 'Do you want reinstall server? (All files will be deleted)';

// 0.7.2
$lang['adm_servers_players_control']				= 'Actions on players commands';
$lang['adm_servers_rcon_commands']					= 'RCON commands';
$lang['adm_servers_kick_cmd']						= 'Kick player command';
$lang['adm_servers_kick_cmd_desc']					= 'You can use following shortcodes: {id} - player id';
$lang['adm_servers_ban_cmd']						= 'Ban player command';
$lang['adm_servers_ban_cmd_desc']					= 'You can use following shortcodes: {id} - player id, {time} - ban time, {reason} - ban reason';
$lang['adm_servers_chname_cmd']						= 'Change nick command';
$lang['adm_servers_chname_cmd_desc']				= 'You can use following shortcodes: {id} - player id, {name} - new player nick';
$lang['adm_servers_chmap_cmd']						= 'Change map command';
$lang['adm_servers_chmap_cmd_desc']					= 'You can use following shotcodes: {map} - new map';
$lang['adm_servers_passwd_cmd']						= 'Set password command';
$lang['adm_servers_passwd_cmd_desc']				= 'You can use following shortcodes: {password} - new password';
$lang['adm_servers_sendmsg_cmd']					= 'Chat say command';
$lang['adm_servers_sendmsg_cmd_desc']				= "You can use following shortcodes: {msg} - message to say";
$lang['adm_servers_srestart_cmd']					= 'Soft restart command';

$lang['adm_servers_aliases_for_command']			= 'Startup command aliases';
$lang['adm_servers_aliases_edit']					= 'Edit aliases';

// 0.8
$lang['adm_servers_query_port']						= 'Query port';
$lang['adm_servers_query_port_desc']				= 'Port for querying server. Leave it blank for use default server port.';
$lang['adm_servers_rcon_port']						= 'RCON port';
$lang['adm_servers_rcon_port_desc']					= 'Port for sending RCON commands. Leave it blank for use default server port.';
$lang['adm_servers_port_exists']					= 'Following port are already in use';
$lang['adm_servers_ds_stats']						= 'Server load stats';
$lang['adm_servers_install_server_parameters']		= 'Server installation options';
$lang['adm_servers_repository_parameters']			= 'Repositories';
$lang['adm_servers_local_repository']				= 'Local repositry';
$lang['adm_servers_local_repository_gdesc']			= 'Path to game server archive on localhost. Its used for server installation.';
$lang['adm_servers_local_repository_gtdesc']		= 'Path to game server mod archive on localhost. This archive will be extracted to server directory folder after basic files of this server are installed.';
$lang['adm_servers_remote_repository']				= 'Remote repositry';
$lang['adm_servers_remote_repository_gdesc']		= 'Path to game server archive on remote host. Its will be downloaded and extracted into server installation directory. Always specify transfer protocol (http://, https://, ftp:// and etc.).';
$lang['adm_servers_remote_repository_gtdesc']		= 'Path to game server mod archive on remote host. Its will be downloaded and extracted into server directory after basic files installation completes. Always specify transfer protocol (http://, https://, ftp:// and etc.).';

// 0.8.6
$lang['adm_servers_ip_description']					= 'You can set several IP addresses. Separate each address with comma.';

// 0.8.8
$lang['adm_servers_go_to_game']						= 'Proceed';

// 0.9
$lang['adm_servers_sftp_path_not_found']			= 'Cannot locate server.sh or server.exe via SSH path.';
$lang['adm_servers_ftp_path_not_found']				= 'Cannot locate server.sh or server.exe via FTP path.';
$lang['adm_servers_modifications']					= 'Mods';
$lang['adm_servers_must_be_one']					= 'You can have only one localhost server.';
$lang['adm_servers_empty_ds_list']					= 'You need to <a href="%s">add</a> a dedicated server first.';

// 0.9.3
$lang['adm_servers_delete_files']					= 'Delete files.';
$lang['adm_servers_send_command']					= 'Send command on console';

// 0.9.9
$lang['adm_servers_generate_rcon_password']			= 'Generate new';

// 1.0
$lang['adm_servers_cpu_limit']						= 'CPU limit';
$lang['adm_servers_ram_limit']						= 'RAM limit';
$lang['adm_servers_net_limit']						= 'Network limit';

$lang['adm_servers_disable_ds']						= 'Disable this dedicated server';
$lang['adm_servers_unknown_engine']					= 'Unsupported game engine';

$lang['adm_servers_gdaemon_access']					= 'GameAP Daemon';
$lang['adm_servers_gdaemon_data']					= 'GameAP Daemon details';
$lang['adm_servers_gdaemon_host']					= 'Host(IP:port)';
$lang['adm_servers_gdaemon_key']					= 'GDaemon access key';
