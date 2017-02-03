#!/usr/bin/env bash
sudo apt-get update -qq
sudo apt-get install -y -qq libssh2-1-dev libssh2-php
whoami
echo `whoami`":1234" | sudo chpasswd

sudo netstat -lntup

ROOT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Make files
sudo mkdir ${ROOT_DIR}/Files
sudo chmod 777 ${ROOT_DIR}/Files

echo FILE_CONTENTS >> ${ROOT_DIR}/Files/File01.txt
echo FILE_CONTENTS >> ${ROOT_DIR}/Files/File02.txt
sudo chmod 666 ${ROOT_DIR}/Files/File01.txt
sudo chmod 666 ${ROOT_DIR}/Files/File02.txt

mv ${ROOT_DIR}/tests/_config/gameap_config.php ${ROOT_DIR}/application/config/gameap_config.php
mv ${ROOT_DIR}/tests/_config/database.php ${ROOT_DIR}/application/config/database.php

exit $?
