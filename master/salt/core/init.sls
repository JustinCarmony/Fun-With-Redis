core-tools:
    pkg:
        - installed
        - names:
            - git-core
            - sysv-rc-conf

/var/deploy:
    file:
        - directory
        - makedirs: true

/usr/local/bin/solo:
    file:
        - managed
        - source: salt://files/solo
        - mode: 755
        