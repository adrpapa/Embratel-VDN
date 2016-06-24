#!/bin/bash
pushd /home/fastlane/Embratel/workspace
apsbuild VDN_Embratel || exit 1
rsync /home/fastlane/Embratel/workspace/VDN_Embratel*zip root@cdn.flts.apsdemo.org:/var/www/packages/
rsync -rav /home/fastlane/Embratel/workspace/VDN_Embratel/scripts/ apache@cdn.flts.apsdemo.org:/var/www/html/ebtvdn/
rsync -rav /home/fastlane/Embratel/workspace/VDN_Embratel/ui/ pemuser@cdn.flts.apsdemo.org:ebtvdn/ui/

