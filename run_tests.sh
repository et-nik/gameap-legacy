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

phpunit --version

mv ${ROOT_DIR}/upload/application/tests/_config/gameap_config.php ${ROOT_DIR}/upload/application/config/gameap_config.php
mv ${ROOT_DIR}/upload/application/tests/_config/database.php ${ROOT_DIR}/upload/application/config/database.php

phpunit --configuration ${ROOT_DIR}/upload/application/tests/phpunit.xml
exit $?
