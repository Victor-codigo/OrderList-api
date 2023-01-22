CREATE DATABASE IF NOT EXISTS OrderList_db;
CREATE DATABASE IF NOT EXISTS OrderList_db_test;
CREATE USER IF NOT EXISTS db_user IDENTIFIED BY "50pq3a";
GRANT ALL ON OrderList_db.* TO "db_user"@"%";
GRANT ALL ON OrderList_db_test.* TO "db_user"@"%";