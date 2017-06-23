#!/bin/bash
if [ -f ../etc/settings.conf ]
then
	. ../etc/settings.conf
else
	echo "No settings found. Exit 1."
	exit 1
fi

if [ -f ../lib/libcore.sh ]
then
	. ../lib/libcore.sh
else
  echo "No core library found. Exit 1."
  exit 1
fi

for i in `find ../lib/modules/ -name '*.sh'` ; do source "$i"; done # Read modules

while true # Loop forever
do
	read LINE < ${S_CONSUMERSDIR}/${S_CONSUMERSSIGNAL} # Wait for a signal #DEBUG#echo "`date +%H:%M:%S`: ${0}(pid $$): Awaiting for signal from ${S_CONSUMERSDIR}/${S_CONSUMERSSIGNAL}"
	if [ "x${LINE}" != "x" ]; then # If signal is not empty ##echo "`date +%H:%M:%S`: ${0}(pid $$): Received ${LINE} on ${S_CONSUMERSDIR}/${S_CONSUMERSSIGNAL}, Connecting to channel ${S_CONSUMERSDIR}/${LINE}"
		RAWDATA=$(getDataFromChannel "${S_CONSUMERSDIR}/${LINE}") # Read data from channel #DEBUG#echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Received ${#RAWDATA} bytes from queue ${S_CONSUMERSDIR}/${LINE}"
		unLockChannel "${S_CONSUMERSDIR}/${LINE}" # Release data channel for future use #DEBUG#echo "`date +%H:%M:%S`: ${0}(pid $$): Attempting to unlock channel ${S_CONSUMERSDIR}/${LINE}"
		MODULEID=$(echo ${RAWDATA} | cut -d " " -f 2)
		DATA=$(echo ${RAWDATA} | cut -d " " -f 3-)
		#MODULEID=$(echo ${RAWDATA} | cut -d " " -f 1) # Read module id #DEBUG#
		echo "`date +%H:%M:%S`: ${0}(pid $$): Using ${MODULEID}_consumer to process data"
    if [ "x${MODULEID}" != "x" ]; then
  		${MODULEID}_consumer ${RAWDATA} # Call consumer function in module
    else
      echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Data corruption: ${RAWDATA}"
    fi
	fi
	. ../etc/settings.conf # Refresh settings
	for i in `find ${S_MODULE_PATH}/ -name '*.sh'` ; do source "$i"; done # Refresh modules
done
