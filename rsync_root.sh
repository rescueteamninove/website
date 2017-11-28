#!/bin/bash

RTN_DIR=$(dirname "$(readlink -f "$0")")

# The trailing slash on the source directory is important!!!
# (to declare the directory contents instead of the directory itself)

rsync --recursive --delete --verbose --exclude-from=rsync_exclude.list "$RTN_DIR/build/" "$RTN_DIR/rescueteam.be/httpd.www"
