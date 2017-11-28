#!/bin/bash

SCRIPT_DIR=$(dirname $(readlink -f "$0"))

"$SCRIPT_DIR/create_rounded_image.sh" "$1" 40 "$2"

convert "$2" -resize 128x128 -colors 256 -depth 8 "$2"

