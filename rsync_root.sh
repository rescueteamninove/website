#!/bin/bash

rsync --recursive --delete --verbose --exclude-from=rsync_exclude.list build/* rescueteam.be/httpd.www
