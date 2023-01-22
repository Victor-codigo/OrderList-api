FROM mysql:8.0

# Update dependencies
# RUN apt-get update

CMD [ "mysqld", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci", "--default-authentication-plugin=mysql_native_password"]

# SQL commands to configure database
COPY setup.sql /docker-entrypoint-initdb.d