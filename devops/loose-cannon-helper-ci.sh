#!/bin/bash

# Script to look up processes that has been started by parent process and kill the child process

### VARIABLES BEGIN

PPROCNAME=core.sh
CPROCNAME=core.sh
KILLAFTER=60

### VARIABLES END

# Loop through parent pids
pgrep -P 1 ${PPROCNAME} | while read ppid; do
  # Find child processes of ppid
  CPID=`pgrep -P ${ppid} ${CPROCNAME}`
  sleep ${KILLAFTER}
  if [ `ps -fp ${CPID} > /dev/null ; echo $?` -eq 0 ]; then
    logger "$0: Killing hanging process ${CPID}"
    kill ${CPID}
  fi
done




