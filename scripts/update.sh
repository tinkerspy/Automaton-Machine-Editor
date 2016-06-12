#!/bin/bash 

# Initializes a set of templates for the current PHP session
# Optionally merges customized code with the new template (when MODIFY file exists)

DIR=`dirname $0`

WORK=${DIR}/../machines/$2/work

if [ $# -eq 0 ]
  then 
    echo "Usage: $0 [session]"
    exit 1
fi

# Set up working directory

rm -rf $WORK
mkdir $WORK
cd $WORK


if [ -f ../MODIFY ];
  then
    echo "Merging code with new template.."

    # Set up temporary git repository
    git init --quiet

    # Recreate the original template from ATML
    ../../../scripts/code.sh ../original.atml > Machine.cpp
    ../../../scripts/header.sh ../original.atml > Machine.h
    ../../../scripts/sketch.sh ../original.atml > sketch.ino
    git add .
    git commit -m "Initial commit" --quiet

    # Create the updated template
    git branch templates
    git checkout templates
    rm Machine.cpp Machine.h
    ../../../scripts/code.sh ../new.atml > Machine.cpp
    ../../../scripts/header.sh ../new.atml > Machine.h
    ../../../scripts/sketch.sh ../new.atml > sketch.ino
    cp Machine.cpp ../Template.cpp
    cp Machine.h ../Template.h
    cp sketch.ino ../Template.ino
    git add .
    git commit -m "Updated template" --quiet

    # Commit the customized code to master (Careful, CR/LF confuses git!)
    git checkout master
    rm Machine.cpp Machine.h
    cat ../Machine.cpp | tr -d '\r' > Machine.cpp
    cat ../Machine.h | tr -d '\r' > Machine.h
    cat ../sketch.ino | tr -d '\r' > sketch.ino
    git add .
    git commit -m "Custom code" --quiet

    # Merge master with the updated template
    git checkout templates
    git merge -X ours master
    cp Machine.cpp ../New-machine.cpp
    cp Machine.h ../New-machine.h
    cp sketch.ino ../New-sketch.ino
  else
    echo "Initializing template..."
    ../../../scripts/code.sh ../new.atml > Machine.cpp
    ../../../scripts/header.sh ../new.atml > Machine.h
    ../../../scripts/sketch.sh ../new.atml > sketch.ino
    cp Machine.cpp ../Template.cpp
    cp Machine.h ../Template.h
    cp sketch.ino ../Template.ino
fi


