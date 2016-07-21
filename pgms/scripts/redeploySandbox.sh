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







[root@mn ~]# 
service pemui stop
service pem stop
service postgresql-9.1 restart

su  postgres -c 'dropdb plesk'
su  postgres -c 'cat ~postgres/pleskd.pgdump|psql'

depois:
service pem start
service pemui start
service httpd start


[root@pba ~]# 
service pba stop
/etc/init.d/postgresql restart

su  postgres -c 'dropdb pba'
su  postgres -c 'cat ~postgres/pba.pgdump|psql'

depois:
service pba start

