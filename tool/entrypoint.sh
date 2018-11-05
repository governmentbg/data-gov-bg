#!/bin/sh

# start apache
httpd

# start cron
crond

if [ ! -d /run/mysqld ]; then
    mkdir -p /run/mysqld
    chown -R mysql:mysql /run/mysqld
fi

if [ ! -d /var/files/backup ]; then
    mkdir -p /var/files/backup
fi

if [ -d /var/lib/mysql/mysql ]; then
    echo '[i] MySQL directory already present, skipping creation'
else
    echo '[i] MySQL data directory not found, creating initial DBs'

    chown -R mysql:mysql /var/lib/mysql

    # init database
    echo '[i] MySQL initializing database...'
    mysql_install_db --user=mysql > /dev/null
    echo '[i] MySQL database initialized'

    echo "[i] MySql root password: $MYSQL_PASSWORD"

    # create temp file
    tfile=`mktemp`
    if [ ! -f "$tfile" ]; then
        return 1
    fi

    # save sql
    echo "[i] Create temp file: $tfile"
    cat << EOF > $tfile
USE mysql;
FLUSH PRIVILEGES;
DELETE FROM mysql.user;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD' WITH GRANT OPTION;
EOF

    # Create new database
    echo "[i] Creating database: $MYSQL_DATABASE"
    echo "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` CHARACTER SET utf8 COLLATE utf8_general_ci;" >> $tfile

    # Set new user and password
    dbpass=`pwgen -s -1 -v`

    sed -i "s#DB_PASSWORD=123123#DB_PASSWORD=$dbpass#" /var/www/localhost/htdocs/.env

    if [ "$MYSQL_USER" != "" ]; then
        echo "[i] Creating user: $MYSQL_USER with password $dbpass"
        echo "GRANT ALL ON \`$MYSQL_DATABASE\`.* to '$MYSQL_USER'@'%' IDENTIFIED BY '$dbpass';" >> $tfile
    fi

    echo 'FLUSH PRIVILEGES;' >> $tfile

    # run sql in tempfile
    echo "[i] Run tempfile: $tfile"
    /usr/bin/mysqld --user=mysql --bootstrap --verbose=0 < $tfile

    rm -f $tfile

    echo '[i] CRON initializing jobs...'

    echo "*/30 * * * * cd /var/www/localhost/htdocs/ && source deploy.sh master" > /mnt/cronjobs
    echo "* * * * * cd /var/www/localhost/htdocs/ && php artisan schedule:run >> /dev/null 2>&1" >> /mnt/cronjobs
    echo "0 */3 * * * mysqldump -u opendata -p$dbpass opendata | gzip > /var/files/backup/\`date +%Y-%m-%d\`" >> /mnt/cronjobs
    /usr/bin/crontab /mnt/cronjobs
    rm -f /mnt/cronjobs

    echo '[i] CRON jobs initialized...'
fi

echo '[i] Starting all process'
/usr/bin/mysqld --user=mysql --console &

until mysqladmin ping &>/dev/null; do
   echo -n '.'; sleep 0.2
done

echo '[i] Updating project'
cd /var/www/localhost/htdocs/ && source deploy.sh master

exec /bin/sh "$@"

