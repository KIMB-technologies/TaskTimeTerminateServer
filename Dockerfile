FROM kimbtechnologies/php_nginx:latest 

# copy php files, nginx conf and startup scripts
COPY --chown=www-data:www-data ./php/ /php-code/
COPY --chown=www-data:www-data ./start/ /start/
COPY ./nginx.conf /etc/nginx/more-server-conf.conf 
COPY /startup-before.sh  /
