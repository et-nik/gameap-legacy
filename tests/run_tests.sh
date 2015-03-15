cd tests/GDaemon && ./daemon
cd ../../

sudo apt-get update -qq
sudo apt-get install -y -qq libssh2-1-dev libssh2-php
pecl install -f ssh2-beta < .noninteractive
sudo apt-get install -y -qq telnetd
whoami
echo `whoami`":1234" | sudo chpasswd

sudo netstat -lntup


phpunit --configuration tests/phpunit.xml 
