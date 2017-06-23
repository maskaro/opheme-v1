#!/bin/bash

S_MODULE_PATH=${S_MODULE_PATH:-`readlink -f ..`}
export TWITTERGEO_CWD="${S_MODULE_PATH}/TWITTERGEO"

if [ -f "${TWITTERGEO_CWD}/etc/TWITTERGEO.conf" ]; then
   source "${TWITTERGEO_CWD}/etc/TWITTERGEO.conf"
else
    echo "`date +%H:%M:%S`: $0(pid $$): File ${TWITTERGEO_CWD}/etc/TWITTERGEO.conf not found"
fi

function TWITTERGEO_agent() {
# Make an oAuth authenticated request to Twittter API using coordinates provided as parameters
#m n
# Requests per rate limit window (15 min window)	
# 180/user
# 450/app
#
# Input parameters:
#   Mod data  = ${@} -> data from job file
#   Job id    = ${1} -> ID number of the job in the job file used as a unique identifier to store LAST_ID for the job
#   Latitude  = ${2} -> defined in job file, example 52.20536
#   Longitude = ${3} -> defined in job file, example 0.11906
#   Radius    = ${4} -> defined in job file, example 1mi
#   Extras     = ${@} -> defined in job file */
  
  if [[ "$#" > "0" ]]; then
    local TWITTERGEO_LIB="${TWITTERGEO_CWD}/lib/TWITTERGEO.sh"
    if [ -f ${TWITTERGEO_LIB} ]; then
      source ${TWITTERGEO_LIB}
    else "ERROR: ${FUNCNAME} Could not find ${TWITTERGEO_LIB}"
    fi

    unset INPUT_ARRAY
    declare -a INPUT_ARRAY=( `echo ${@} | tr '!' ' '` )

    unset TWITTERGEO_ARRAY
    declare -A TWITTERGEO_ARRAY
    TWITTERGEO_ARRAY[jid]=${INPUT_ARRAY[0]} #jobid
    TWITTERGEO_ARRAY[lat]=${INPUT_ARRAY[2]} #latitude
    TWITTERGEO_ARRAY[lon]=${INPUT_ARRAY[3]} #longitude
    TWITTERGEO_ARRAY[rad]=${INPUT_ARRAY[4]} #radius
    TWITTERGEO_ARRAY[lid]=$(TWITTERGEO_getLastId ${TWITTERGEO_CWD}/${TWITTERGEO_OUTPUT_DIR}/${TWITTERGEO_ARRAY[jid]}/lastid)

	#twitter request URL
	local TWITTERGEO_URL="http://api.twitter.com/1.1/search/tweets.json?q=%20&count=100&geocode=${TWITTERGEO_ARRAY[lat]},${TWITTERGEO_ARRAY[lon]},${TWITTERGEO_ARRAY[rad]}&since_id=${TWITTERGEO_ARRAY[lid]}"
    
    # 20130207 jheinonen: Count number of messages before checking in
    MSGCOUNT1=`TWITTERGEO_JobMsgCount ${TWITTERGEO_ARRAY[jid]}` 
    
	#get JSON data from twitter
    JSON_DATA=`TWITTERGEO_apiRequest "${TWITTERGEO_URL}" "${TWITTERGEO_ARRAY[jid]}"`

    #if [ "x`echo ${JSON_DATA:0:6}`" == "xERROR:" ]; then
      #echo "`date +%H:%M:%S`: ${FUNCNAME}: ${JSON_DATA}"
    #else
      #ruby ${TWITTERGEO_CWD}/json_parse.rb ${TWITTERGEO_ARRAY[jid]}!${TWITTERGEO_ARRAY[lat]}!${TWITTERGEO_ARRAY[lon]}!${TWITTERGEO_ARRAY[rad]} "${JSON_DATA}"
    #fi

    # 20130207 jheinonen: Count number of messages after checking in
    MSGCOUNT2=`TWITTERGEO_JobMsgCount ${TWITTERGEO_ARRAY[jid]}`
    MSGCOUNT3=`expr ${MSGCOUNT2} - ${MSGCOUNT1}`
    #MSGCOUNT3=`expr 10 - 2`
    echo "`date +%H:%M:%S`: (pid $$): ${FUNCNAME}: TWITTERGEO_URL:geocode=${TWITTERGEO_ARRAY[lat]},${TWITTERGEO_ARRAY[lon]},${TWITTERGEO_ARRAY[rad]}&since_id=${TWITTERGEO_ARRAY[lid]}, Jobid ${TWITTERGEO_ARRAY[jid]}, Messages in total:${MSGCOUNT2}, New:${MSGCOUNT3}"
  else
    echo "ERROR: invalid number of paramaters \"$#\": \"$@\""
    #echo "EXAMPLE: ${FUNCNAME} <jobid> <lat> <lon> <rad>"
  fi
}

function TWITTERGEO_test() {
  #S_MODULE_PATH=`pwd`
  TWITTERGEO_agent ${@}
}


if [ "${1}" == "test" ]; then
   shift
   TWITTERGEO_test "05fb619c0eb7b3b7962e25f721dfa8b2156f3540!TWITTERGEO!52.205411!0.119047!1mi"
fi 
