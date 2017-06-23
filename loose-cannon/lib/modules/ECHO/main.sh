#!/bin/bash
#
# INPUT: <jobid>!<MODULENAME>
# OUTPUT: Epoch time and human-readable date-timestamp

function ECHO_agent() {
  EPOCH=$(date +%s)
  echo "epoch time ${EPOCH} = \"$(date -d @${EPOCH} +%c)\""
}
function ECHO_test() {
  if [ $# -eq 0 ]; then
    ECHO_agent "1!ECHO"
  else
    ECHO_agent ${1}
  fi
}
if [ "${1}" == "test" ]; then
  shift
  ECHO_test ${1}
fi 
