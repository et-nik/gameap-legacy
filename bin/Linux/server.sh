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
# Параметры запуска
#
# @command - команда (start|stop|restart|status)
# @dir - директория относительно скрипта
# @name - имя для screen
# @serverip - ip сервера
# @port - порт сервера
# @start_command - команда для сервера (напр. 'hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire')
# @user - пользователь, под которым будет запущен игровой сервер (если пусто, то будет использован root)
#
# Example:
# ./server.sh start /home/hl_server screen_hldm 127.0.0.1 27015 "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire" user
#
#
#

PATH=/bin:/usr/bin:/sbin:/usr/sbin

# Раскомментируйте строчку ниже, если не запускается игровой сервер
# во многих случаях, особенно для виртуальных серверов, это помогает
#export CPU_MHZ=2000

# Directory
DIR=$2

PIDFILE_NAME="server.pid"
PIDFILE="$DIR/$PIDFILE_NAME"

# Screen Name
NAME=$3

# IP
SERVER_IP=$4

# Port
SERVER_PORT=$5

# Command
COMMAND=$6

if [[ $7 == '' ]]
then
	USER=$(whoami)
else
	USER=$7
fi

case "$1" in
 start)
    if [[ `su $USER -c "screen -ls |grep $NAME"` ]]
		then
		echo "Server is already running"
    else
		su $USER -c "cd $DIR; screen -m -d -S $NAME $COMMAND"
		sleep 4
		
		if [[ `su $USER -c "screen -ls |grep $NAME"` ]]
			then
			echo "Server started"
		else
		   echo -e "Server not started \nStart command:"
		   echo su $USER -c "cd $DIR; screen -m -d -S $NAME $COMMAND"
		fi
    fi
    ;;

 stop)
    if [[ `su $USER -c "screen -ls |grep $NAME"` ]]
       then
       kill `ps aux | grep -v grep | grep -i $USER | grep -i screen | grep -i $NAME | awk '{print $2}'`
       echo "Server stopped"
    else
       echo "Coulnd't find a running server"
    fi
    ;;

 restart)
    if [[ `su $USER -c "screen -ls |grep $NAME"` ]]
       then
       kill `ps aux | grep -v grep | grep -i $USER | grep -i screen | grep -i $NAME | awk '{print $2}'`
       
       sleep 2

		su $USER -c "cd $DIR; screen -m -d -S $NAME $COMMAND"
		echo "Server restarted"
    else
       echo "Coulnd't find a running server"
    fi
    ;;
 status)
    if [ -e ${PIDFILE} ] && [ $(ps -p $(cat ${PIDFILE})|wc -l) = "2" ] ;
    	then
       echo "Server is UP"
    else
       echo "Server is Down"
    fi
    ;;
 get_console)
	su $4 -c "screen -S $NAME -X -p 0 hardcopy $DIR/console.txt && chmod 666 $DIR/console.txt"
	echo "File $DIR/console.txt created"
	;;
 *)
    echo "Usage all parameters"
    exit 1
    ;;
esac

exit 0
