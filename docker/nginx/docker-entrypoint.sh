#!/bin/bash

# xdebug config
if [ "$XDEBUG" = "1" ]
then
    mv -f /usr/local/etc/php/conf.d/xdebug.ini.disabled /usr/local/etc/php/conf.d/xdebug.ini
else
    if [ -f /usr/local/etc/php/conf.d/xdebug.ini ]
    then
        mv -f /usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini.disabled
    fi
fi

if [ -f /usr/local/etc/php/conf.d/xdebug.ini ]
then
    # if XDEBUG_HOST is manually set
    HOST="$XDEBUG_HOST"

    # else if check if is Docker for Mac
    if [ -z "$HOST" ]; then
        HOST=`getent hosts host.docker.internal | awk '{ print $1 }'`
    fi

    # else get host ip
    if [ -z "$HOST" ]; then
        HOST=`/sbin/ip route|awk '/default/ { print $3 }'`
    fi

    sed -i "s/xdebug\.remote_host \=.*/xdebug\.remote_host\=$HOST/g" /usr/local/etc/php/conf.d/xdebug.ini
fi

# Fix cache/logs/session permission based on symfony version
# Symfony 3.x+
if [ -f bin/console ]; then
    chmod -R 777 var/cache var/logs var/sessions
else
#Symfony 2.x
    chmod -R 777 app/cache app/logs
fi

exec $@