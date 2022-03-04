#!/bin/bash
#
# This file was obtained from the answer provided by "Frederik Struck-Schøning" - 
#   https://stackoverflow.com/questions/5828324/update-git-submodule-to-latest-commit-on-origin
#
# To use this script - 
#
#   ./git-update-submodules.sh /full/path/to/repo/with/submodules/inside master
#
# NOTE: If on Windows you can run this script from within a gitbash command window.
#
APP_PATH=$1
shift

if [ -z $APP_PATH ]; then
  echo "Missing 1st argument: should be full path to folder of a git repo";
  exit 1;
fi

BRANCH=$1
shift

if [ -z $BRANCH ]; then
  echo "Missing 2nd argument (branch name)";
  exit 1;
fi

echo "Working in: $APP_PATH"
cd $APP_PATH

git checkout $BRANCH && git pull --ff origin $BRANCH

git submodule sync
git submodule init
git submodule update
git submodule foreach "(git checkout $BRANCH && git pull --ff origin $BRANCH && git push origin $BRANCH) || true"

for i in $(git submodule foreach --quiet 'echo $path')
do
  echo "Adding $i to root repo"
  git add "$i"
done

git commit -m "Updated $BRANCH branch of deployment repo to point to latest head of submodules"
git push origin $BRANCH

