#!/bin/sh

# Generates a .h template file from an xml defintion in the ATML env variable
# Or from an ATML file given as an argument

DIR=`dirname $0`

if [ $# -ne 0 ]
  then 
    ATML=`cat $1` php -n -d "include_path=${DIR}" ${DIR}/../templates/header-current.php
  else 
   php -n -d "include_path=${DIR}" ${DIR}/../templates/header-current.php
fi




