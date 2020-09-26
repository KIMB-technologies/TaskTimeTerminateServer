#!/bin/sh 
# file to setup docker container and update data to newer version

# account
php /start/account.php
# data storage migrations
php /start/migrations.php

# file rights
chown -R www-data:www-data /php-code/data/