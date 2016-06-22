#!/bin/bash
# Set variables – $mozydir should be updated with the correct directory name use when deploying the package with the endpoint.sh script

mozydir=mozy
logdirpath=/var/www/html/$mozydir/logs/
rotatelog=$logdirpath"mozylogrotate.log"
dateforlog=$(date +"%Y-%m-%d")
time=$(date +"%H:%M:%S")

if [ -z "$1" ];
        then
        date=$(date +"%Y-%m-%d")

else
        date="$1"

fi

countLogs=$(ls -l $logdirpath | grep $date- | grep .log | wc -l)


logarchive=$logdirpath"logfile_$date.tar"

#Check for log files to rotate

if [ "$countLogs" -gt "0" ];
        then
        echo $dateforlog "/" $time "------ Archiving all log files from $logdirpath for the day…" >> $rotatelog;

#Gathering all log files for the day into a single archive
        tar -czPvf $logarchive $logdirpath$date-*.log >> $rotatelog
#Check if the archive file has been created

                if [ -f $logarchive ];
                        then
                    echo $dateforlog "/" $time "------ Success : All logs for the day have successfully been archived. Deleting log files…" >> $rotatelog


#Remove all log files which have been previously archived

                    rm  -rf $logdirpath"/"$date-*.log

                else
                        echo $dateforlog "/" $time "------ Error : There was an issue archiving the logs." >> $rotatelog;
                fi
else
        echo $dateforlog "/" $time "------ Info : No log found to archive." >> $rotatelog;
fi
