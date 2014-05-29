#!/usr/bin/env sh

# abort script on failure
set -e -x

# change to working dir
cd `dirname $0`/../..

brew update
brew install mysql
mysql.server start
