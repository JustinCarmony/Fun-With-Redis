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
            - php5-xcache
            - php-apc
    require:
        - pkg: apache2

/etc/apache2/sites-available/default:
    file:
        - managed
        - source: salt://master/files/default
        - mode: 755