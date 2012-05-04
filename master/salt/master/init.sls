apache2:
    pkg:
        - installed

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