#!/bin/bash

fusermount -u "$(dirname "$(readlink -f "$0")")/rescueteam.be"

