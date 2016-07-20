#!/bin/bash

service httpd stop
service pemui stop
service pem stop
service postgresql-9.1 restart
su  postgres -c "/usr/bin/dropdb plesk"
su  postgres -c 'cat ~/originalPostgres.pgdump|psql'
#' > ~/originalPostgres.log 2>&1; grep -q "^ERROR:" ~/originalPostgres.log && { echo "Deu erro"; } '

service pem start
service pemui start
service httpd start




[root@pba ~]# 
service pba stop
service postgresql-9.1 restart



[root@mn ~]# 
service pemui stop
service pem stop
service postgresql-9.1 restart


[root@mn ~]# 
su  postgres -c 'cat ~/originalPostgres.pgdump|psql'
service pem start
service pemui start
service httpd start



[root@pba ~]# 
su  postgres -c 'cat ~/originalPostgres.pgdump|psql'
