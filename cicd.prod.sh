#!/bin/bash

# Define a variable to store the container path
WORKING_PATH="/home/hero/core"
TARGET_BRANCH="origin/production"

# goto folder
cd -P "$WORKING_PATH"

# before
before=$(git rev-parse $TARGET_BRANCH)

# git pull from latest repo
git pull ${TARGET_BRANCH//\// }

# after
after=$(git rev-parse $TARGET_BRANCH)

if [ "$before" != "$after" ]; then
    # restart webman
    docker-compose restart webman-core
fi