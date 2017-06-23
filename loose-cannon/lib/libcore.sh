#!/bin/bash
#
# core.sh
# Library providing core framework functionality

function cleanUp() {
# Trap to do tidy up of child processes and files
	killChildrenOfType consumer
	killChildrenOfType agent
	removeDirectory ${S_CONSUMERSDIR}
	removeDirectory ${S_AGENTSDIR}
	echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Parent PID $$ terminating. Exit 0."
	exit 0
}

function childProcessDied() {
# Trap that prints out a message about death of a child process
	echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): A child process suffered a quick painless death!"
}

function ensureDirExists() {
# Create directory $1 if not exist
	if [ ! -d ${1} ]
	then
		mkdir ${1}
		if [ $? -ne 0 ]
		then
			echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Failed to create ${1}. Exit 1."
			exit 1
		fi
	fi
}

function ensurePipeExists() {
	echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Checking if ${1} is found"
	if [ ! -p ${1} ]
	then
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Creating pipe ${1}"
		mkfifo ${1}
	else 
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Pipe ${1} exists"
	fi
}

function findFreeChannel() {
	i=0
	NUMBER_OF_CHANNELS=${1}
	DIR_FOR_CHANNELS=${2}

	for chnl in `ls -I *.lock ${DIR_FOR_CHANNELS}` 	
	do
		if [ ! -p "${DIR_FOR_CHANNELS}/${chnl}.lock" ]
		then
			(( i++ ))
			echo ${chnl}
			break
		fi				
	done

	if [ ${i} -eq 0 ] #DEBUG#
	then
	  echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): No free channel in ${DIR_FOR_CHANNELS}. Wait for ${S_CHANNELRETRY} seconds and retry."
		sleep ${S_CHANNELRETRY} 
		findFreeChannel ${NUMBER_OF_CHANNELS} ${DIR_FOR_CHANNELS}
	fi	
}

function freeChannelId() {
	CHANNELTYPE=$1

	case ${CHANNELTYPE} in
		agent|AGENT)
			echo `findFreeChannel ${#S_CHANNELSNR[*]} ${S_AGENTSDIR}`
		;;	
		consumer|CONSUMER)
			echo `findFreeChannel ${#S_CHANNELSNR[*]} ${S_CONSUMERSDIR}`
		;;
		*)
			echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Unrecognise channel type ${CHANNELTYPE}. Exit 1."
			#exit 1
		;;
	esac
}

function getDataFromChannel() {
	read PAYLOAD < ${1}
	echo "${PAYLOAD}"
}

function killChildrenOfType() {
# Select an array of type $1 children to kill
  i=0
	case $1 in
		agent|AGENT)
	    echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Terminating ${#AGENTPIDS[*]} agent children"
	    while [ $i -lt ${#AGENTPIDS[*]} ]
	    do
	      pkill -9 -P ${AGENTPIDS[$i]}	
		    kill -9 ${AGENTPIDS[$i]}
		    echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): $2 PID ${AGENTPIDS[$i]} terminated"
		    (( i++ ))
	    done
	    unset ${AGENTPIDS[$i]}				
		;;
		consumer|CONSUMER)
	    echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Terminating ${#CONSUMERPIDS[*]} consumer children"
	    while [ $i -lt ${#CONSUMERPIDS[*]} ]
	    do	
	      pkill -9 -P ${CONSUMERPIDS[$i]}
		    kill -9 ${CONSUMERPIDS[$i]}
		    echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): $2 PID ${CONSUMERPIDS[$i]} terminated"
		    (( i++ ))
	    done
	    unset ${CONSUMERPIDS[$i]}				
		;;
		*)
			echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Invalid type $1"
		;;	
	esac
	

}

function lockChannel () {
	LOCKON=$1
	if [ ! -p ${LOCKON}.lock ]
	then
		mkfifo ${LOCKON}.lock #DEBUG#echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): pid $$: Creating lock ${LOCKON}.lock."
	else
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): pid $$: Lock already in ${LOCKON}.lock."
	fi
}

function makeDirectory() {
# Create directory $1
	if [ ! -d $1 ]
	then
		mkdir -p $1
	fi
}

function makeFifo() {
# Create named pipes for signal/data channels
	local loop=0
	#ensureDirExists ${S_CONSUMERSDIR}
	ensureDirExists ${S_AGENTSDIR}
	#ensurePipeExists ${S_CONSUMERSDIR}/${S_CONSUMERSSIGNAL}
	ensurePipeExists ${S_AGENTSDIR}/${S_AGENTSSIGNAL}
	while [ $loop -lt ${#S_DATACHANNELS[*]} ]
	do
		#ensurePipeExists ${S_CONSUMERSDIR}/${S_DATACHANNELS[$loop]}
		ensurePipeExists ${S_AGENTSDIR}/${S_DATACHANNELS[$loop]}
		(( loop++ ))
	done
}

function registeredChildren() {
# List registered child processes
	local i=0
	echo -n "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Registered $1 processes: "
	case $1 in
		agent|AGENT)
	    while [ $i -lt ${#AGENTPIDS[*]} ]
	    do
		    echo -n "${AGENTPIDS[$i]}, "
		    (( i++ ))
	    done
		;;
		consumer|CONSUMER)
	    while [ $i -lt ${#CONSUMERPIDS[*]} ]
	    do
		    echo -n "${CONSUMERPIDS[$i]}, "
		    (( i++ ))
	    done
		;;
	esac
	echo 
}

function removeDirectory() {
# Remove directory $1
	if [ -d ${1} ]
	then
		rm -rf ${1}
	fi
}

function removeLocks () {
# Remove lock files in directory $1
	echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Removing lock files in ${1}"
	if [ -d ${1} ]
	then
		rm -rf ${1}/*.lock
	fi	
}

function signalToChannel() {
	SIGNALQ=${1}
	MESSAGE=${2}
	if [[ "x${MESSAGE}" != "x" && "x${SIGNALQ}" != "x" ]]
	then
		#echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Sending signal to channel ${SIGNALQ}, connect to ${MESSAGE}"
		echo ${MESSAGE} | tr -d [:cntrl:] > ${SIGNALQ} 
	else
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Received empty signal or queue ${SIGNALQ}, payload ${MESSAGE}. Not signalling."
	fi
}

function spawnChildren() {
# Start $2 amount of $1 type of processes
	local i=0
	echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Starting ${2} ${1} processes"
	while [ $i -lt ${2} ]
	do
		./${1}.sh &
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): ${1} PID $! started"
		case $1 in
			agent|AGENT)
				AGENTPIDS[$i]=$!
			;;
			consumer|CONSUMER)
				CONSUMERPIDS[$i]=$!
			;;
		esac
		(( i++ ))
	done
	registeredChildren $1
}

function spawnChildrenOfType() {
# Select $1 type of child processes to start
	case $1 in
		agent|AGENT)
			spawnChildren agent ${S_AGENTS}
		;;
		consumer|CONSUMER)
			spawnChildren consumer ${S_CONSUMERS}
		;;
		*)
			echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Invalid type $1"
		;;	
	esac
}

function statusReport() {
  A_LOCKS=`ls -1 ${S_AGENTSSDIR}/*.lock 2>/dev/null | wc -l `
  #C_LOCKS=`ls -1 ${S_CONSUMERSDIR}/*.lock 2>/dev/null | wc -l` 
  A_PROCS=`pgrep agent.sh | wc -l`
  #C_PROCS=`pgrep consumer.sh | wc -l`
  #echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Agent processes ${A_PROCS}, agent channels in use ${A_LOCKS}/${#S_DATACHANNELS[*]}. Consumer processes ${C_PROCS}, consumer channels in use ${C_LOCKS}/${#S_DATACHANNELS[*]}"
  echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): Agent processes ${A_PROCS}, agent channels in use ${A_LOCKS}/${#S_DATACHANNELS[*]}. "
}

function unLockChannel() {
	LOCKON=$1
	if [ -p ${LOCKON}.lock ]
	then
		rm -f ${LOCKON}.lock #DEBUG#echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): pid $$: Removing lock ${LOCKON}.lock."
	else
		echo "`date +%H:%M:%S`: ${0}:${FUNCNAME}(pid $$): pid $$: Lock already removed ${LOCKON}.lock."
	fi
}

