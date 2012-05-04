include:
    - core

/var/deploy/minion:
    file:
        - recurse
        - source: salt://minion/files/deploy
        - makedirs: true
    require:
        - file: /var/deploy
        - pkg: core-tools

git-fun-clone:
    cmd:
        - run
        - unless: ls /var/fun/.git/
        - name: git clone git://github.com/JustinCarmony/Fun-With-Redis.git /var/fun
    require:
        - pkg: core-tools

cd /var/fun && git pull:
    cmd:
        - run
    require:
        - cmd: git-fun-clone

/var/fun/config.php:
    file:
        - managed
        - source: salt://files/config.php
    require:
        - cmd: git-fun-clone

