#!/bin/bash

#
# Скрипт запуска HLDS сервера
#
# Autor: ET-NiK
# Site 1: http://www.gameap.ru
# Site 2: http://hldm.org
# 
# Данный скрипт используется для управления игровыми серверами через АдминПанель
#
# 
# Параметры
#
# -t <type> 		(start|stop|restart|status|get_console|send_command)
# -d <dir>			директория относительно скрипта
# -n <screen_name>	имя screen
# -i <ip>
# -p <port>
# -c <command> 		команда для сервера (напр. 'hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire')
# -u <user>			имя пользователя
# -m <memory>		лимит оперативной памяти (Kb)
# -f <percentage>	лимит на использование процессора
# -n <max speed>	лимит на использование пропускной способности
# 
# Examples:
# ./server.sh -t start -d /home/hl_server -n screen_hldm -i 127.0.0.1 -p 27015 -c "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire"
# ./server.sh -t get_console -n hldm -u usver
#

# Версия
VERSION=100

# Загрузка конфигурации
if [[ -s ./server.conf ]]; then
	# echo -e "Configuration loaded"
	source ./server.conf
fi

# Переменные
USER=$(whoami);
NAME="";

# ----------------------------------------------------------------------
# Запуск сервера
function server_start()
{
	if [[ $SNAME == "" ]]; then
		echo -e "Screen name empty";
		echo -e "Server not started";
		return;
	fi
	
	if [[ $COMMAND == "" ]]; then
		echo -e "Command empty";
		echo -e "Server not started";
		return;
	fi
	
	if [[ "$(server_status)" == 1 ]]
       then
		echo -e "Server is already running";
    else
		su $USER -c "cd $DIR; ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND";
		PID=$!;
		sleep 3;
		
		if [[ `su $USER -c "screen -U -ls | grep -i $SNAME"` ]]
			then
			echo -e "Server started";
			# echo -e "Start command:\n cd $DIR; ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND";
		else
		   echo -e "Server not started";
		   echo -e "Start command:\ncd $DIR; ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND";
		fi
    fi
    
    cpu_limit;
}

# ----------------------------------------------------------------------
# Остановка сервера
function server_stop()
{
	if [[ $SNAME == "" ]]; then
		echo -e "Screen name empty";
		echo -e "Server not started";
		exit;
	fi
	
	if [[ "$(server_status)" == 1 ]]
       then
       kill -TERM `ps aux | grep -v grep | grep -i screen | grep -i $SNAME | awk '{print $2}'`
       su $USER -c  "screen -U -X -S $SNAME kill"
       echo "Server stopped"
    else
       echo "Coulnd't find a running server"
    fi
}

# ----------------------------------------------------------------------
# Получение статуса сервера
function server_status()
{
	if [[ $SNAME == '' ]]
	then
		echo 0;
		return;
	fi

	if [[ `sudo su $USER -c "screen -ls |grep $SNAME"` ]]
		then
		echo 1;
    else
		echo 0;
    fi
}

# ----------------------------------------------------------------------
# Получение частей команд
function get_parts()
{
	if [[ $RAM_LIMIT > 0 && $allow_ram_limit ]]; then
		COMMAND_PARTS[0]="ulimit -Hv $RAM_LIMIT ;";
	fi
	
	if [[ $NET_LIMIT > 0 && $allow_net_limit ]]; then
		COMMAND_PARTS[1]="trickle -d $NET_LIMIT -u $NET_LIMIT";
	fi
}

# ----------------------------------------------------------------------
# Применить ограничение CPU %
function cpu_limit()
{
	if [[ $CPU_LIMIT && $allow_cpu_limit && $PID ]]; then
		CPU_LIMIT=$(($CPU_LIMIT*$core_count))
		
		# PID
		cpulimit --pid=$PID --limit=$CPU_LIMIT
		
		# EXE
		#cpulimit --exe="$DIR/$PROGRAM" --limit=$CPU_LIMIT
	fi
}

# Получение опций
while getopts 't:d:n:i:p:c:u:m:f:s:' opt ;
do
	case $opt in
		t)
			TYPE=$OPTARG;
			;;
		d) 
			DIR=$OPTARG;
			;;
		n) 
			SNAME=$OPTARG;
			;;
		i) 
			IP=$OPTARG;
			;;
		p) 
			PORT=$OPTARG;
			;;
		c) 
			COMMAND=$OPTARG;
			;;
		u)
			USER=$OPTARG;
			;;
		m)
			RAM_LIMIT=$OPTARG;
			;;
		f)
			CPU_LIMIT=$OPTARG;
			;;
		s)
			NET_LIMIT=$OPTARG;
			;;
		esac
done

# DEBUG
# -------------------
#~ echo -e "Type: $TYPE";
#~ echo -e "Dir: $DIR";
#~ echo -e "Screen name: $SNAME";
#~ echo -e "Ip: $IP";
#~ echo -e "Port: $PORT";
#~ echo -e "Command: $OPTARG";
#~ echo -e "User: $USER";
#~ echo -e "Memory limit: $RAM_LIMIT Kb";
#~ echo -e "Cpu limit: $CPU_LIMIT %";
#~ echo -e "Net limit: $NET_LIMIT Kb/s";
# -------------------

# Разбиение на программу и агрументы
IFS=' ' read -a explode <<< "$COMMAND";
PROGRAM=${explode[0]};
unset explode[0];

ARGUMENTS="";
for element in "${explode[@]}"
do
    ARGUMENTS=$ARGUMENTS" $element";
done

unset explode;

case "$TYPE" in
	start)
		get_parts;
		server_start;
		;;

	stop)
		server_stop;
		;;
		
	restart)
		get_parts >> /dev/null
		server_stop >> /dev/null
		
		if [ "$(server_start)" == "Server started" ] ;
			then
			echo -e "Server restarted"
		else
			echo -e "Server not restarted"
		fi
		
		;;
		
	status)
		if [ "$(server_status)" == 1 ] ;
			then
		   echo "Server is UP"
		else
		   echo "Server is Down"
		fi
		;;
		
	get_console)
		su $USER -c "screen -U -S $SNAME -X -p 0 hardcopy -h $DIR/gap_console.txt && chmod 666 $DIR/gap_console.txt"
		RESULT=`cat $DIR/gap_console.txt`
		echo -e "$RESULT"
		;;
		
	send_command)
		# Screen version 4.00.03jw4 (FAU) 2-May-06
		#~ su $USER "-c screen -p 0 -S $SNAME -X stuff '$COMMAND'$'\n'"
 	
 		# Screen version 4.01.00devel (GNU) 2-May-06
		#~ su $USER "-c screen -p 0 -S $SNAME -X stuff '$COMMAND\n'"

		su $USER "-c screen -U -p 0 -S $SNAME -X stuff '$COMMAND
		'"
		
		;;
	*)
		echo "Unknown type"
		;;
esac
