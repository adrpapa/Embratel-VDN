#!/bin/bash

if [ $(dig +noall +answer cdn.flts.apsdemo.org | wc -l) -gt 0 ]; then
	dig +noall +answer cdn.flts.apsdemo.org
	echo 'VPN já está Configurada'
	exit 0
fi

cd /home/fastlane/Dropbox/Clientes/Fastlane/Embratel/pgms/scripts/apsstandardVpn
if [ $(id -u) != 0 ]; then
	echo "must be root to run this script."
	sudo /home/fastlane/Dropbox/Clientes/Fastlane/Embratel/pgms/scripts/apsstandardVpn/startvpn.sh 
	exit 1
fi

DNS=10.112.0.11;

pkill -f aps.ovpn

echo "Iniciando openvpn"
openvpn --verb 9 --config aps.ovpn > /var/log/openvpn.log &

DATE_LIMIT=$(( $(date +%s) + 30 ))

while [ $(date +%s) -lt ${DATE_LIMIT} ]; do
	grep 'Initialization Sequence Completed' /var/log/openvpn.log && break
	sleep 1
done

grep -q $DNS /etc/resolv.conf || {
	echo nameserver $DNS > /tmp/resolv.conf;
	cat /etc/resolv.conf >> /tmp/resolv.conf;
	mv /tmp/resolv.conf /etc/resolv.conf;
}
dig +noall +answer cdn.flts.apsdemo.org

