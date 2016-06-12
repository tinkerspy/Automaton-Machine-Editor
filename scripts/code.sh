#!/bin/sh

DIR=`dirname $0`

if [ $# -ne 0 ]
  then 
    ATML=`cat $1` php -n -d "include_path=${DIR}/../lib/" ${DIR}/../templates/code-current.php
  else 
   php -n -d "include_path=${DIR}/../lib/" ${DIR}/../templates/code-current.php
fi




