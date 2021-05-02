FROM kimbtechnologies/php_nginx:8-latest 

# enable get_browser() in PHP
RUN mkdir /start/ \
	&& curl -L https://browscap.org/stream?q=Lite_PHP_BrowsCapINI -o /start/php_browscap.ini \
	&& echo "browscap = /start/php_browscap.ini" > /usr/local/etc/php/conf.d/enable_browscap.ini

# config files, changed seldom
COPY ./nginx.conf /etc/nginx/more-server-conf.conf 
COPY ./startup-before.sh  /
COPY ./start/* /start/

# php files, changed more often
COPY ./php/ /php-code/
COPY ./VERSION /php-code/VERSION