#!/bin/sh

usage() 
{
	echo "Application endpoint configurator."
	echo "Usage: $0 [--upgrade] <endpoint url path> <application archive path>"
	echo "Application scripts are placed on apache web server, in <endpoint url path> directory. .htaccess with necessary rewrite rules is placed in this directory."
	echo "Example: $0 app2 /root/myapp-1.0-1.app.zip - application scripts are placed in /var/www/html/app2"
	echo "If --upgrade flag is specified, existing scipts are replaced with new ones. .htaccess is re-generated. typeCache directory is cleared."
	echo
	echo "To configure DUMMY endpoint, which replyes OK on all requests, call script as follows: $0 dummy"
	echo

	exit
};

HTTP_RESTART_REQUIRED=0

dummy_endpoint=0
if [ "$#" -lt 2 ]; then
    if [ "$#" -eq 1 -a "$1" = "dummy" ]
    then
        dummy_endpoint=1
    else
        usage
    fi
fi

if [ "$1" = "--upgrade" ]
then
	upgrade_mode=1
	shift
else
	upgrade_mode=0
fi

ENDPOINT_URLPATH=$1
shift
ENDPOINT_FILEPATH=$*

if ! echo $ENDPOINT_URLPATH | perl -e 'my $upath = <STDIN>; if ($upath !~ /^[a-zA-Z0-9_-]+$/) { exit(1);} else {exit(0);}'
then
	echo "Error: endpoint url path must contain only digits, latin letters, underscore"
	echo
	usage
fi 

if [ $dummy_endpoint -eq 0  -a ! -f "$ENDPOINT_FILEPATH" ]; then
	echo "$ENDPOINT_FILEPATH - file not found\n"
	usage
fi

if [ ! -f "/etc/httpd/conf/httpd.conf" ]
then
	echo "/etc/httpd/conf/httpd.conf is not found. Perhaps, httpd is not installed  on endpoint node?"
	exit 1;
fi

export ENDPOINT_LOCATION=/var/www/html/${ENDPOINT_URLPATH}
if [ $upgrade_mode -eq 1 -a ! -s $ENDPOINT_LOCATION ]
then
	echo "Can't find $ENDPOINT_LOCATION - nothing to upgrade!"
	exit 1
fi

if [ $dummy_endpoint -eq 0 -a $upgrade_mode -eq 0 -a -s $ENDPOINT_LOCATION ]
then
	echo $ENDPOINT_LOCATION already exists. Please specify unique url path
	exit 1
fi

# install mod_ssl if not installed
rpm -q mod_ssl > /dev/null || yum -y install mod_ssl

suf=$RANDOM

if ! perl -e '
    my $h = 0; 
    my $html_dir_found = 0;
    my $options_set = 0;
    my $allowovveride_set = 0;
    while ($ln = <STDIN>) { 
       if ($ln =~ m#Directory "/var/www/html"#) { 
           $flag = 1; 
           print $ln; 
           $html_dir_found = 1;
           next; 
       } 
       if ($flag && $ln =~ /^[\s\t]*Options /) { 
           print "    Options FollowSymLinks ExecCGI\n"; 
           next; 
       } 
       if ($flag && $ln =~ /^[\s\t]*AllowOverride /) { 
           print "    AllowOverride All\n"; 
           next; 
       } 
       if ($ln =~ m#</Directory>#) {
           $flag = 0;
       }
       print $ln;
    }

    if (!$html_dir_found) {
       print SRDERR "Did not find /var/www/html directory in /etc/httpd/conf/httpd.conf\n";
       exit(1);
    } 
' < /etc/httpd/conf/httpd.conf > /tmp/httpd.conf_$suf;
then
   echo "Error on httpd.conf modification"
   echo "Check that /var/www/html directory is declared in /etc/httpd/conf/httpd.conf"
   exit 1
fi


if ! grep clientcert.crt /etc/httpd/conf.d/ssl.conf > /dev/null 
then
	# setup SSL
	# generate self-signed certificate:
	openssl genrsa -out clientcert.key
	openssl req -x509 -new -days 3600 -nodes -key clientcert.key -subj "/DC=APS/DC=Application Endpoint/O=$HOSTNAME/OU=APS/CN=$HOSTNAME" -out clientcert.crt

	if [ -f clientcert.crt ]
	then
		cp clientcert.crt /etc/pki/tls/certs/
		cp clientcert.key /etc/pki/tls/private/
		sed -e 's/localhost.crt/clientcert.crt/' -e 's/localhost.key/clientcert.key/' -e 's/#SSLVerifyClient require/SSLVerifyClient optional_no_ca/' -e 's/SSLOptions +StdEnvVars/SSLOptions +StdEnvVars +ExportCertData/' --in-place /etc/httpd/conf.d/ssl.conf
	else
		echo "SSL certificate generation failed. Please setup https manually"
	fi
fi

if ! diff -q -b -B /tmp/httpd.conf_$suf /etc/httpd/conf/httpd.conf 
then
if ! mv -f /tmp/httpd.conf_$suf /etc/httpd/conf/httpd.conf
then
	echo "Error: root permissions are needed!"
	exit 1
fi
HTTP_RESTART_REQUIRED=1
fi

TMP_LOCATION=/tmp/app_$RANDOM

mkdir $TMP_LOCATION

if [ $dummy_endpoint -eq 0 ]
then
  if echo "$ENDPOINT_FILEPATH" | grep '://'
  then
        wget -k $ENDPOINT_FILEPATH -O app_$suf.zip
        ENDPOINT_FILEPATH=app_$suf.zip
  elif [ ! -f "$ENDPOINT_FILEPATH" ]
  then
        echo "Local file $ENDPOINT_FILEPATH is not found!"
        exit 1
  fi

  if ! unzip "$ENDPOINT_FILEPATH" -d $TMP_LOCATION > /dev/null
  then
        echo "Application archive is not in zip format or application download url was incorrect"
        exit 1
  fi
  cd $TMP_LOCATION
  if [ $upgrade_mode -eq 1 ]
  then
	cp -r scripts/* $ENDPOINT_LOCATION 
	cp -r scripts/.*[a-zA-Z0-9_]* $ENDPOINT_LOCATION 2> /dev/null
	rm -rf $ENDPOINT_LOCATION/typeCache
  else
	rm -rf $ENDPOINT_LOCATION
	mv -f scripts $ENDPOINT_LOCATION;
  fi
else
  mkdir -p /var/www/html/dummy
fi

 echo "<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteBase /$ENDPOINT_URLPATH
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_URI} !=/favicon.ico" > $ENDPOINT_LOCATION/.htaccess

if [ $dummy_endpoint -eq 0 ]
then
 perl -e '

  while (my $ln = <STDIN>) { 
	if ($ln =~ /< *service +id="([^"]+)"/) {
		print "   RewriteRule ^$1(/\.\*)\?\$ ";
		$rewrite_rule_start = 1;
		next;
	}
	if ($ln =~ m#< *code +.* +path="scripts/([^"]*)"#) {
		print "$1\?q=\$1 [L,QSA]\n";
	}
  }' < APP-META.xml >> $ENDPOINT_LOCATION/.htaccess
else
 echo "<?php" > $ENDPOINT_LOCATION/index.php
 echo >> $ENDPOINT_LOCATION/index.php
 echo "   RewriteRule .* index.php" >>  $ENDPOINT_LOCATION/.htaccess
fi
if [ -f /etc/httpd/conf.d/ssl.conf ]
then
	echo "RewriteCond %{HTTPS} !=on" >> $ENDPOINT_LOCATION/.htaccess
	echo "RewriteRule .* - [F]" >> $ENDPOINT_LOCATION/.htaccess
fi

echo "</IfModule>" >> $ENDPOINT_LOCATION/.htaccess

chmod -R 755 $ENDPOINT_LOCATION
chown -R apache:apache $ENDPOINT_LOCATION
if [ $HTTP_RESTART_REQUIRED -eq 1 ]
then
if ! service httpd restart
then
	echo "FAILED to start httpd!!!"
	exit 1
fi
fi
# starting pre-configuration script if such script found in package
# after executing - removing script from endpoint
if [ -f $ENDPOINT_LOCATION/pre-configure.sh ]
then
    echo "Staring $ENDPOINT_LOCATION/pre-configure.sh script"
    sh $ENDPOINT_LOCATION/pre-configure.sh
    rm $ENDPOINT_LOCATION/pre-configure.sh
fi
echo

echo 
echo "CONGRATULATIONS! Endpoint configured SUCCESSFULLY"
echo "******************************************************************************************************"
echo "*****                                                                                            *****"
echo "***** Remember to check if php and aps-php are installed                                         *****"
echo "*****                                                                                            *****"
echo "***** yum install php                                                                            *****"
echo "***** yum install http://download.apsstandard.org/php.runtime/aps-php-runtime-2.0-393.noarch.rpm *****"
echo "*****                                                                                            *****"
echo "******************************************************************************************************"
if [ -f /etc/httpd/conf.d/ssl.conf ]
then
	echo
	echo "Endpoint url: https://$HOSTNAME/$ENDPOINT_URLPATH"
else
	echo
	echo "http endpoint url: http://$HOSTNAME/$ENDPOINT_URLPATH"
	echo "https was not configured. Check error messages above for more information"
fi
echo
rm -rf $TMP_LOCATION
