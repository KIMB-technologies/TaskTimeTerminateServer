FROM kimbtechnologies/php_nginx:latest 

# copy php files, nginx conf and startup scripts
COPY --chown=www-data:www-data ./php/ /php-code/
COPY --chown=www-data:www-data ./start/ /start/
COPY ./nginx.conf /etc/nginx/more-server-conf.conf 
COPY ./startup-before.sh  /
COPY ./VERSION /php-code/VERSION

RUN curl -L https://browscap.org/stream?q=Lite_PHP_BrowsCapINI -o /start/lite_php_browscap.ini \
	&& echo "browscap = /start/lite_php_browscap.ini" > /usr/local/etc/php/conf.d/enable_browscap.ini