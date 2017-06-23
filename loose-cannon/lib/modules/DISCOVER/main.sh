#!/bin/bash

S_MODULE_PATH=${S_MODULE_PATH:-`readlink -f ..`}
export DISCOVER_CWD="${S_MODULE_PATH}/DISCOVER"

if [ -f "${DISCOVER_CWD}/etc/DISCOVER.conf" ]; then
   source "${DISCOVER_CWD}/etc/DISCOVER.conf"
else
    echo "`date +%H:%M:%S`: $0(pid $$): File ${DISCOVER_CWD}/etc/DISCOVER.conf not found"
fi

function DISCOVER_agent() {
	
  if [[ "$#" > "0" ]]; then
	echo "DISCOVER_agent: Current Job: ${@}"
    #send the job string directly to php
    php ${DISCOVER_CWD}/apiRequest.php "${@}" "${S_FOLDER_PHP}" "${S_MODULE_PATH}"
  else
    echo "ERROR: invalid number of paramaters \"$#\": \"$@\""
  fi

}