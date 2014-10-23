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
# -p <percentage>	лимит на использование процессора
# -s <max speed>	лимит на использование пропускной способности
# 
# Examples:
# ./server.sh -t start -d /home/hl_server -n screen_hldm -i 127.0.0.1 -p 27015 -c "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire"
# ./server.sh -t get_console -n hldm -u usver
#

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
	
	if [[ "$(status)" == 1 ]]
		then
		echo -e "Server is already running"
    else
		su $USER -c "cd $DIR; screen -U -m -d -S $SNAME $COMMAND"
		sleep 4
		
		if [[ `su $USER -c "screen -U -ls | grep -i $SNAME"` ]]
			then
			echo -e "Server started"
		else
		   echo -e "Server not started \nStart command:"
		   echo -e su $USER -c "cd $DIR; screen -U -m -d -S $SNAME $COMMAND"
		fi
    fi
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
	
	if [[ "$(status)" == 1 ]]
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
	if [ -e ${PIDFILE} ] && [ $(ps -p $(cat ${PIDFILE})|wc -l) = "2" ] ;
		then
		echo 1;
    else
		echo 0;
    fi
}

# Получение опций
while getopts "t:n:i:p:c:u:m:p:n" opt ;
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
			MEM_LIMIT=$OPTARG;
			;;
		p)
			CPU_LIMIT=$OPTARG;
			;;
		s)
			NET_LIMIT=$OPTARG;
			;;
		esac
done

# -------------------
echo -e "Type: $TYPE";
echo -e "Dir: $DIR";
echo -e "Screen name: $SNAME";
echo -e "Ip: $IP";
echo -e "Command: $OPTARG";
echo -e "User: $USER";
echo -e "Memory limit: $MEM_LIMIT";
echo -e "Cpu limit: $CPU_LIMIT";
echo -e "Net limit: $NET_LIMIT";
# -------------------


case "$TYPE" in
	start)
		start;
		;;

	stop)
		stop;
		;;
		
	restart)
		stop;
		start;
		;;
		
	status)
		if [ "$(status)" == 1 ] ;
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
		su $USER "-c screen -U -p 0 -S $NAME -X stuff '$COMMAND
		'"
		;;
	*)
		echo "Unknown type"
		;;
esac
