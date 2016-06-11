#!/bin/bash 

WORK=machines/$1/work

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
    ../../../code.sh ../original.atml > Machine.cpp
    ../../../header.sh ../original.atml > Machine.h
    git add .
    git commit -m "Initial commit" --quiet

    # Create the updated template
    git branch templates
    git checkout templates
    rm Machine.cpp Machine.h
    ../../../code.sh ../new.atml > Machine.cpp
    ../../../header.sh ../new.atml > Machine.h
    cp Machine.cpp ../Template.cpp
    cp Machine.h ../Template.h
    git add .
    git commit -m "Updated template" --quiet

    # Commit the customized code to master (Careful, CR/LF confuses git!)
    git checkout master
    rm Machine.cpp Machine.h
    cat ../Machine.cpp | tr -d '\r' > Machine.cpp
    cat ../Machine.h | tr -d '\r' > Machine.h
    git add .
    git commit -m "Custom code" --quiet

    # Merge master with the updated template
    git checkout templates
    git merge -X ours master
    cp Machine.cpp ../New-machine.cpp
    cp Machine.h ../New-machine.h
  else
    echo "Initializing template..."
    ../../../code.sh ../new.atml > Machine.cpp
    ../../../header.sh ../new.atml > Machine.h
    cp Machine.cpp ../Template.cpp
    cp Machine.h ../Template.h
fi

