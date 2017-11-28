#!/bin/bash

sudo umount "$(dirname "$(readlink -f "$0")")/rescueteam.be"

