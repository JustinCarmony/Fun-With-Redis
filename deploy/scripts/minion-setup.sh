echo "**** Starting Minion Setup ****"

echo "**** Installing Salt ****"
aptitude -y install python-software-properties
add-apt-repository ppa:saltstack/salt
aptitude update
aptitude safe-upgrade
aptitude install salt-minion

/etc/init.d/salt-minion restart
/etc/init.d/salt-minion restart

echo "**** Copying Salt Minion Conf ****"

cp -f /tmp/minion /etc/salt/minion
chmod 755 /etc/salt/minion

echo "**** DONE ****"
