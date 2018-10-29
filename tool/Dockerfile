FROM alpine:latest

RUN apk add --update \
    tzdata \
    curl \
    git \
    pwgen \
    mysql mysql-client \
    apache2 \
    php7-apache2 \
    php7-cli \
    php7-zlib \
    php7-zip \
    php7-bz2 \
    php7-ctype \
    php7-curl \
    php7-pdo_mysql \
    php7-mysqli \
    php7-json \
    php7-mcrypt \
    php7-xml \
    php7-dom \
    php7-iconv \
    php7-session \
    php7-intl \
    php7-gd \
    php7-mbstring \
    php7-opcache \
    php7-phar \
    php7-fileinfo \
    php7-tokenizer \
    php7-tokenizer \
    php7-simplexml \
    php7-xmlreader \
    php7-xmlwriter \
    php7-pdo_sqlite \
    php7-sqlite3

RUN apk add --update mysql mysql-client pwgen
RUN addgroup mysql mysql
RUN rm -f /var/cache/apk/*

ENV MYSQL_DATABASE=opendata MYSQL_USER=opendata MYSQL_PASSWORD=opendata GIT_REPO=https://github.com/governmentbg/data-gov-bg API_URL=http://data.egov.bg/api/ GRAYLOG_HOST=https://opendata.gov.bg GRAYLOG_PORT=24230

# configure apache
RUN mkdir -p /run/apache2 && chown -R apache:apache /run/apache2 \
    && sed -i 's#\#LoadModule rewrite_module modules\/mod_rewrite.so#LoadModule rewrite_module modules\/mod_rewrite.so#' /etc/apache2/httpd.conf \
    && sed -i 's#ServerName www.example.com:80#\nServerName localhost:80#' /etc/apache2/httpd.conf \
    && sed -i 's#DocumentRoot "/var/www/localhost/htdocs"#DocumentRoot "/var/www/localhost/htdocs/public"#' /etc/apache2/httpd.conf \
    && sed -i 's#<Directory "/var/www/localhost/htdocs">#<Directory "/var/www/localhost/htdocs/public">#' /etc/apache2/httpd.conf \
    && sed -i 's#AllowOverride None#AllowOverride All#' /etc/apache2/httpd.conf

# install composer
COPY composer /usr/local/bin/composer

# configure project
RUN cd /var/www/localhost \
    && rm -r htdocs \
    && git clone $GIT_REPO \
    && mv data-gov-bg htdocs \
    && cd htdocs \
    && echo '' > storage/logs/laravel.log \
    && chmod -R 777 storage \
    && composer install \
    && cp .env.example .env \
    && sed -i '/GRAYLOG_HOST/d' ./.env \
    && sed -i '/GRAYLOG_PORT/d' ./.env \
    && printf "IS_TOOL=true\nTOOL_API_URL=$APIURL\n\nGRAYLOG_HOST=$GRAYLOG_HOST\nGRAYLOG_PORT=$GRAYLOG_PORT\n" >> .env

VOLUME [ "/var/lib/mysql", "/mnt", "/var/www/localhost/htdocs" ]

COPY entrypoint.sh entrypoint.sh

ENTRYPOINT "./entrypoint.sh"
