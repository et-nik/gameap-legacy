sudo apt-get update -qq
sudo apt-get install -y -qq libssh2-1-dev libssh2-php
whoami
echo `whoami`":1234" | sudo chpasswd

sudo netstat -lntup

# Make files
sudo mkdir /home/travis/build/ET-NiK/GameAP/Files
sudo chmod 777 /home/travis/build/ET-NiK/GameAP/Files

echo FILE_CONTENTS >> /home/travis/build/ET-NiK/GameAP/Files/File01.txt
echo FILE_CONTENTS >> /home/travis/build/ET-NiK/GameAP/Files/File02.txt
sudo chmod 666 /home/travis/build/ET-NiK/GameAP/Files/File01.txt
sudo chmod 666 /home/travis/build/ET-NiK/GameAP/Files/File02.txt

phpunit --version

mv /home/travis/build/ET-NiK/GameAP/tests/Test.php /home/travis/build/ET-NiK/GameAP/upload/application/controllers/Test.php
phpunit --configuration /home/travis/build/ET-NiK/GameAP/tests/phpunit-dbinst.xml

phpunit --configuration /home/travis/build/ET-NiK/GameAP/tests/phpunit.xml
echo "Result $?"
