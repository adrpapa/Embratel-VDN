#!/bin/bash

service httpd stop
service pemui stop
service pem stop
service postgresql-9.1 restart
su  postgres -c "/usr/bin/dropdb plesk"
su  postgres -c 'cat ~/originalPostgres.pgdump|psql > ~/originalPostgres.log 2>&1; grep -q "^ERROR:" ~/originalPostgres.log && { echo "Deu erro"; } '

