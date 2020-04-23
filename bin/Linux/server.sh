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
VERSION=101

# Переменные
UUSER=$(whoami);
NAME="";

# ----------------------------------------------------------------------
# Запуск сервера
server_start()
{
    if [[ $SNAME == "" ]]; then
        echo -e "Screen name empty";
        echo -e "Server not started";
        return 1;
    fi

    if [[ $COMMAND == "" ]]; then
        echo -e "Command empty";
        echo -e "Server not started";
        return 1;
    fi

    if server_status; then
        echo -e "Server is already running";
        return 1
    else
        if [[ $(id -u) -eq "0" ]]; then
            su $UUSER -c "cd $DIR; ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND";
        else
            cd $DIR;
            ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND
            cd -
        fi;

        PID=$!;
        sleep 3;

        if [[ $(id -u) -eq "0" ]]; then
            check_result=$(su $UUSER -c "screen -U -ls | grep -i ${SNAME}")
        else
            check_result=$(screen -U -ls | grep -i ${SNAME})
        fi;

        if [[ -n ${check_result} ]]; then
            echo -e "Server started";
            return 0
        else
           echo -e "Server not started";
           echo -e "Start command:\ncd $DIR; ${COMMAND_PARTS[0]} screen -U -m -d -S $SNAME ${COMMAND_PARTS[1]} $COMMAND";
           return 1
        fi
    fi
}

# ----------------------------------------------------------------------
# Остановка сервера
server_stop()
{
    if [[ $SNAME == "" ]]; then
        echo -e "Screen name empty";
        echo -e "Server not started";
        return 1
    fi

    if server_status; then
       kill -TERM `ps aux | grep -v grep | grep -i screen | grep -i $SNAME | awk '{print $2}'`

       if [[ $(id -u) -eq "0" ]]; then
           su $UUSER -c "screen -U -X -S $SNAME kill"
       else
           screen -U -X -S $SNAME kill
       fi;

       echo "Server stopped"
       return 0
    else
       echo "Coulnd't find a running server"
       return 1
    fi
}

# ----------------------------------------------------------------------
# Получение статуса сервера
server_status()
{
    if [[ $SNAME == '' ]]
    then
        return 1
    fi

    if [[ $(id -u) -eq "0" ]]; then
        check_result=$(su $UUSER -c "screen -U -ls | grep -i ${SNAME}")
    else
        check_result=$(screen -U -ls | grep -i ${SNAME})
    fi;

    if [[ -n ${check_result} ]]; then
        return 0
    else
        return 1
    fi
}

# ----------------------------------------------------------------------
# Получение частей команд
get_parts()
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
cpu_limit()
{
    if [[ $CPU_LIMIT && $allow_cpu_limit && $PID ]]; then
        CPU_LIMIT=$(($CPU_LIMIT*$core_count))

        # PID
        cpulimit --pid=$PID --limit=$CPU_LIMIT

        # EXE
        #cpulimit --exe="$DIR/$PROGRAM" --limit=$CPU_LIMIT
    fi
}

parse_options()
{
    POSITIONAL=()
    while [[ $# -gt 0 ]]
    do
        key="$1"

        case $key in
            -t|--type)
                TYPE="$2"
                shift
                shift
            ;;
            -d|--dir)
                DIR="$2"
                shift
                shift
            ;;
            -n|--name|--sname)
                SNAME="$2"
                shift
                shift
            ;;
            -i|--ip)
                IP="$2"
                shift
                shift
            ;;
            -p|--port)
                PORT="$2"
                shift
                shift
            ;;
            -c|--command)
                COMMAND="$2"
                shift
                shift
            ;;
            -u|--user)
                UUSER="$2"
                shift
                shift
            ;;
            -m|--memory)
                RAM_LIMIT="$2"
                shift
                shift
            ;;
            -f|--cpu)
                CPU_LIMIT="$2"
                shift
                shift
            ;;
            -s|--net)
                NET_LIMIT="$2"
                shift
                shift
            ;;
            *)
                POSITIONAL+=("$1")
                shift
        esac
    done
}

main()
{
    # Разбиение на программу и агрументы
    IFS=' ' read -a explode <<< "$COMMAND";
    PROGRAM=${explode[0]};

    ARGUMENTS="";
    for element in "${explode[@]}"
    do
        ARGUMENTS=$ARGUMENTS" $element";
    done

    unset explode;

    case "$TYPE" in
        start)
            get_parts;

            if server_start; then
                exit 0
            else
               exit 1
            fi

            ;;

        stop)
            if server_stop; then
                exit 0
            else
               exit 1
            fi
            ;;

        restart)
            get_parts >> /dev/null
            server_stop >> /dev/null

            if server_start; then
                echo -e "Server restarted"
                exit 0
            else
                echo -e "Server not restarted"
                exit 1
            fi

            ;;

        status)
            if server_status; then
               echo "Server is UP"
               exit 0
            else
               echo "Server is Down"
               exit 1
            fi
            ;;

        get_console)
            screen_id=$(su gameap -c "screen -ls | grep $SNAME" | head | cut -d. -f1 | tr -d '\t')

            su $UUSER -c "screen -U -S ${screen_id} -X -p 0 hardcopy -h ${DIR}/gap_console.txt && chmod 666 ${DIR}/gap_console.txt"
            sed -i '/^$/d' "${DIR}/gap_console.txt"
            iconv -c -f utf-8 -t utf-8 "${DIR}/gap_console.txt"
            ;;

        send_command)
            screen_id=$(su gameap -c "screen -ls | grep $SNAME" | head | cut -d. -f1 | tr -d '\t')
            # Screen version 4.00.03jw4 (FAU) 2-May-06
            #~ su $USER "-c screen -p 0 -S $SNAME -X stuff '$COMMAND'$'\n'"

            # Screen version 4.01.00devel (GNU) 2-May-06
            #~ su $USER "-c screen -p 0 -S $SNAME -X stuff '$COMMAND\n'"

            su $UUSER "-c screen -U -p 0 -S ${screen_id} -X stuff '${COMMAND}
            '"

            ;;
        *)
            echo "Unknown type"
            ;;
    esac
}

parse_options "$@"
main