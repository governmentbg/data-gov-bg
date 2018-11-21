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

    dbpass=`cat /var/lib/mysql/dbpass`
else
    echo '[i] MySQL data directory not found, creating initial DBs'

    # Set new user and password
    dbpass=`pwgen -s -1 -v`
    echo $dbpass > /var/lib/mysql/dbpass

    chown -R mysql:mysql /var/lib/mysql

    # init database
    echo '[i] MySQL initializing database...'
    mysql_install_db --user=mysql > /dev/null
    echo '[i] MySQL database initialized'

    echo "[i] MySql root password: $MYSQL_PASSWORD"

    # create temp file
    tmpfile=`mktemp`
    echo "[i] Create temp file: $tmpfile"
    if [ ! -f "$tmpfile" ]; then
        return 1
    fi

    # save sql
    printf "USE mysql;\n" >> $tmpfile
    printf "FLUSH PRIVILEGES;\n" >> $tmpfile
    printf "DELETE FROM mysql.user;\n" >> $tmpfile
    printf "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD' WITH GRANT OPTION;\n" >> $tmpfile

    # Create new database
    echo "[i] Creating database: $MYSQL_DATABASE"
    echo "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` CHARACTER SET utf8 COLLATE utf8_general_ci;" >> $tmpfile

    if [ "$MYSQL_USER" != "" ]; then
        echo "[i] Creating user: $MYSQL_USER with password $dbpass"
        echo "GRANT ALL ON \`$MYSQL_DATABASE\`.* to '$MYSQL_USER'@'%' IDENTIFIED BY '$dbpass';" >> $tmpfile
    fi

    echo 'FLUSH PRIVILEGES;' >> $tmpfile

    # run sql in tempfile
    echo "[i] Run tempfile: $tmpfile"
    /usr/bin/mysqld --user=mysql --bootstrap --verbose=0 < $tmpfile

    rm -f $tmpfile
fi

sed -i "/DB_PASSWORD=/c\\DB_PASSWORD=$dbpass" /var/www/localhost/htdocs/.env

echo '[i] CRON initializing jobs...'

# create temp file
tmpfile=`mktemp`
echo "[i] Create temp file: $tmpfile"
if [ ! -f "$tmpfile" ]; then
    return 1
fi

echo "*/30 * * * * cd /var/www/localhost/htdocs/ && source deploy.sh master" > $tmpfile
echo "* * * * * cd /var/www/localhost/htdocs/ && php artisan schedule:run >> /dev/null 2>&1" >> $tmpfile
echo "0 */3 * * * mysqldump -u $MYSQL_USER -p$dbpass $MYSQL_DATABASE | gzip > /var/files/backup/\`date +%Y-%m-%d\`" >> $tmpfile

# run cronjobs in tempfile
echo "[i] Run tempfile: $tmpfile"
/usr/bin/crontab $tmpfile
rm -f $tmpfile

echo '[i] CRON jobs initialized...'

echo '[i] Starting all processes...'
/usr/bin/mysqld --user=mysql --console &

until mysqladmin ping &>/dev/null; do
   echo -n '.'; sleep 0.2
done

echo '[i] Updating project...'
cd /var/www/localhost/htdocs/ && source deploy.sh master

exec /bin/sh "$@"
