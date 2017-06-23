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
	read LINE < ${S_AGENTSDIR}/${S_AGENTSSIGNAL} # Wait to receive a signal #DEBUG#echo "`date +%H:%M:%S`: ${0}(pid $$): Awaiting for signal on ${S_AGENTSDIR}/${S_AGENTSSIGNAL}"
	if [ "x${LINE}" != "x" ]; then # If signal is not empty #DEBUG#echo "`date +%H:%M:%S`: ${0}(pid $$): Received ${LINE} on ${S_AGENTSDIR}/${S_AGENTSSIGNAL}, Connecting to channel ${S_AGENTSDIR}/${LINE}"
		RAWDATA=$(getDataFromChannel "${S_AGENTSDIR}/${LINE}") # Read job from data channel.  
		unLockChannel "${S_AGENTSDIR}/${LINE}" # Release agent channel for reuse #DEBUG#echo "`date +%H:%M:%S`: ${0}(pid $$): Attempting to unlock channel ${S_AGENTSDIR}/${LINE}"
		declare -a MODULEPARM=( $(echo ${RAWDATA} | tr '!' ' ' ) ) # Create array for module parameters: 1-> return data channel, 2-> Job id, 3-> Data from Jobs file
		MODULEID=${MODULEPARM[1]} # Get module id
		${MODULEID}_agent ${RAWDATA}
		unset MODULEPARM
	fi
	. ../etc/settings.conf # Refresh settings
	for i in `find ${S_MODULE_PATH}/ -name '*.sh'` ; do source "$i"; done # Refresh modules
done
			
