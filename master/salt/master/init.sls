apache2:
    pkg:
        - installed
    service:
        - running
    watch:
        - file: /etc/apache2/sites-available/default
php5:
    pkg:
        - installed
        - names:
            - libapache2-mod-php5 
            - php5
            - php5-dev 
            - php-pear 
            - php-apc
    require:
        - pkg: apache2

php5-xcache:
    pkg:
        - purged

/etc/apache2/sites-available/default:
    file:
        - managed
        - source: salt://master/files/default
        - mode: 755

/var/fun/master/www:
    file:
        - directory
        - mode: 755

/usr/local/bin/solo -port=5001 php /var/fun/master/bin/worker.master.php >> /tmp/worker.master.log:
    cron:
        - present
        - user: root
    require:
        - file: /usr/local/bin/solo