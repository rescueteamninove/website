#!/bin/bash

echo "input: $1"
echo "radius: $2"
echo "output: $3"

convert "$1" -flatten \
\( +clone  -alpha extract \
-draw "fill black polygon 0,0 0,$2 $2,0 fill white circle $2,$2 $2,0" \
\( +clone -flip \) -compose Multiply -composite \
\( +clone -flop \) -compose Multiply -composite \
\) -alpha off -compose CopyOpacity -composite  "$3"

