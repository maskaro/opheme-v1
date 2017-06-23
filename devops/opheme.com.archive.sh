#!/bin/bash


#find /opt/live.opheme.com/loose-cannon/var/jobs/ -mtime 2 -exec rm -f {} \; -print

#touch -r /var/log/syslog.3.gz /opt/live.opheme.com/loose-cannon/var/jobs/file25th

BASEDIR=$1
ARCHIVE=${BASEDIR}/opheme.com.archive.tar
ARCHIVE_TGZ=${ARCHIVE}.tar.gz
LOG=echo

function createArchive() {
  declare -a ARGS=( "$@" )
  tar zcvf ${ARCHIVE_TGZ} `echo "${ARGS}"`
}

function updateArchive() {
  declare -a ARGS=( "$@" )
  gunzip ${ARCHIVE_TGZ}
  tar rvf ${ARCHIVE} `echo "${ARGS}"`
  gzip ${ARCHIVE} 
}

if [ "$#" -lt 1 ]; then
  ${LOG} "$0: Usage: ./opheme.com.archive.sh <BASEDIR>"
  exit 1
fi  

unset TARARRAY
declare -a TARARRAY
for file in `find ${BASEDIR}/loose-cannon/var/jobs/ -mtime +1 -type f ! -name jobs.txt`; do
TARARRAY+=(${file})
${LOG} "Adding file ${file} to array"
done
#echo "Array content: ${TARARRAY[*]}"

if [ ${#TARARRAY[*]} -gt 0 ]; then

  if [ ! -f ${ARCHIVE_TGZ} ]; then
    ${LOG} "$0: No previous archive found. Creating new ${ARCHIVE_TGZ}"
    createArchive `echo "${TARARRAY[*]}"`
  else
    ${LOG} "$0: Previous archive found. Appending to ${ARCHIVE_TGZ}"
    updateArchive `echo "${TARARRAY[*]}"`
  fi
fi


