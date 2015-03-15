cd tests/GDaemon && ./daemon
cd ../../
sudo netstat -lntup
phpunit --configuration tests/phpunit.xml 
