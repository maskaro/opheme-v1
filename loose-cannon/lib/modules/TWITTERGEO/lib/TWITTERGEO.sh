#!/bin/bash

function TWITTERGEO_JobMsgCount() {
  local JOBID=${1}
  x=0
	for i in `ls ${TWITTERGEO_CWD}/${TWITTERGEO_OUTPUT_DIR}/${JOBID}`; do
    (( x++ ))
	done
	echo ${x}
}

function TWITTERGEO_getLastId() {
# Find last id from file
#
# Input parameters:
#   last id = ${1}
  #echo "Looking for file ${1}"
  #if [ ! -d `dirname $1` ]; then # create directory if not exist
  #  mkdir -p `dirname $1`
  #fi  
  #if [ ! -f "${1}" ]; then # create file if not exist
  #  echo 0 > ${1}
  #fi
  cat "${1}"
}

function TWITTERGEO_apiRequest() {
	#curl --retry 3 --retry-delay 3  -sS "http://search.twitter.com/search.json?q=%20&rpp=100&geocode=${LAT},${LON},${RAD}&since_id=${LAST_ID}" #> data/twitter.arrays.json
	#curl --retry 3 --retry-delay 3  -sS "${1}"
	php ${TWITTERGEO_CWD}/apiRequest.php "${1}" "${2}"
}

function TWITTERGEO_apiRequestMock() {
  #echo "looking for ${TWITTERGEO_CWD}/search.json"
	cat ${TWITTERGEO_CWD}/search.json
}
