#!/bin/bash

S_MODULE_PATH=${S_MODULE_PATH:-`readlink -f ..`}
export CAMPAIGN_CWD="${S_MODULE_PATH}/CAMPAIGN"

if [ -f "${CAMPAIGN_CWD}/etc/CAMPAIGN.conf" ]; then
   source "${CAMPAIGN_CWD}/etc/CAMPAIGN.conf"
else
    echo "`date +%H:%M:%S`: $0(pid $$): File ${CAMPAIGN_CWD}/etc/CAMPAIGN.conf not found"
fi

function CAMPAIGN_agent() {
	
  if [[ "$#" > "0" ]]; then
	echo "CAMPAIGN_agent: Current Job: ${@}"
    #send the job string directly to php
    php ${CAMPAIGN_CWD}/apiRequest.php "${@}" "${S_FOLDER_PHP}" "${S_MODULE_PATH}"
  else
    echo "ERROR: invalid number of paramaters \"$#\": \"$@\""
  fi

}