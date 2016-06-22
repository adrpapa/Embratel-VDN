#!/usr/bin/env python
import os;
import md5;
import time;

el_server='201.31.12.7'
el_live='201.31.12.4'
el_delta='201.31.12.36'


def call_curl(host, url, parms, options ):
	expires=int(time.time())+60;
	login=key='elemental';
	md5_hex = lambda x: md5.new(x).hexdigest();
	hashed_key=md5_hex(key + md5_hex(url + login + key + str(expires)))
	print 'login: '+login+' key: '+key+' expires: '+str(expires)+' url: '+url+' X-Auth-Key: '+hashed_key
	cmd = 'curl -H "X-Auth-User: %s" -H "X-Auth-Expires: %s" -H "X-Auth-Key: %s" -H "Content-type: application/xml" -H "Accept: application/xml" %s http://%s/api/%s '%( login, expires, hashed_key, options, host, url+parms ) 
	#print cmd
	os.system(cmd)

url='/live_events';
parms='/1/reset';
options='-d "<reset></reset>"'

call_curl(el_live, url, parms, options)
