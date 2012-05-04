echo "**** Starting Minion Setup ****"

echo "**** Installing Salt ****"
aptitude update
aptitude -y safe-upgrade
aptitude -y install python-software-properties
add-apt-repository ppa:saltstack/salt
aptitude update
aptitude -y safe-upgrade
aptitude -y install salt-minion

/etc/init.d/salt-minion restart

echo "**** Copying Salt Minion Conf ****"

cp -f /tmp/minion /etc/salt/minion
chmod 755 /etc/salt/minion

/etc/init.d/salt-minion restart
sleep 5
/etc/init.d/salt-minion restart
sleep 5
echo "**** DONE ****"
