FROM alpine:latest

RUN apk add --update \
    tzdata \
    curl \
    git \
    pwgen \
    nano \
    mysql mysql-client \
    apache2 \
    docker \
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

RUN apk add --update mysql mysql-client
RUN addgroup mysql mysql
RUN rm -f /var/cache/apk/*

ENV MYSQL_DATABASE=opendata-test MYSQL_USER=opendata MYSQL_PASSWORD=opendata GIT_REPO=https://github.com/asapbg/data-gov-bg API_URL=https://testdata.egov.bg/api/ GRAYLOG_HOST=testdata.egov.bg GRAYLOG_PORT=12202 DB_HOST=localhost

# configure apache
RUN mkdir -p /run/apache2 && chown -R apache:apache /run/apache2 \
    && sed -i 's#\#LoadModule rewrite_module modules\/mod_rewrite.so#LoadModule rewrite_module modules\/mod_rewrite.so#' /etc/apache2/httpd.conf \
    && sed -i 's#ServerName www.example.com:80#\nServerName localhost:80#' /etc/apache2/httpd.conf \
    && sed -i 's#DocumentRoot "/var/www/localhost/htdocs"#DocumentRoot "/var/www/localhost/htdocs/public"#' /etc/apache2/httpd.conf \
    && sed -i 's#<Directory "/var/www/localhost/htdocs">#<Directory "/var/www/localhost/htdocs/public">#' /etc/apache2/httpd.conf \
    && sed -i 's#AllowOverride None#AllowOverride All#' /etc/apache2/httpd.conf

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
    && sed -i "/DB_HOST=/c\\DB_HOST=$DB_HOST" ./.env \
    && sed -i "/DB_DATABASE=/c\\DB_DATABASE=$MYSQL_DATABASE" ./.env \
    && sed -i "/GRAYLOG_HOST=/c\\GRAYLOG_HOST=$GRAYLOG_HOST" ./.env \
    && sed -i "/GRAYLOG_PORT=/c\\GRAYLOG_PORT=$GRAYLOG_PORT" ./.env \
    && printf "IS_TOOL=true\n" >> .env \
    && printf "IS_TEST_TOOL=true\n" >> .env \
    && printf "IS_DOCKER=true\n" >> .env \
    && printf "TOOL_API_URL=$API_URL\n" >> .env \
    && date +%s > /var/lastrestart \
    && mkdir /var/files

COPY entrypoint.sh entrypoint.sh

ENTRYPOINT "./entrypoint.sh"
