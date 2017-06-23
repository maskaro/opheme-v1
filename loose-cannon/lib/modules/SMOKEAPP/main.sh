#!/bin/bash

MODULEID=SMOKEAPP

function SMOKEAPP_agent() {
 #echo "Attempting to connect to $1"
 RESPONSE=`curl -sIL $1 | grep HTTP`
 echo "${MODULEID}!${RESPONSE}" 
}

function SMOKEAPP_consumer() {
  declare -a MYARR=( $( echo $@ | tr '!' ' ' ) ) # Read data into array
  #echo "Number of elements ${#MYARR[*]}, data ${MYARR[*]}"
  unset MYARR[0]
  #echo "Number of elements left ${#MYARR[*]}, data ${MYARR[*]}"
  if [ "${MYARR[2]}" != "200" ]; then
    echo "Failure response ${MYARR[2]}"
  else
    echo "Response ${MYARR[2]}"
  fi
  
}

function SMOKEAPP_test() {
  SMOKEAPP_consumer `SMOKEAPP_agent 192.168.2.11`
  
}

if [ "${1}" == "test" ]; then
  SMOKEAPP_test
fi 
