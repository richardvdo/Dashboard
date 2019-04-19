#!/bin/sh

pat=/home/jarvis/xplanet/img/
tmp=$pat"tmp_clouds_2048.jpg"
img=$pat"clouds_2048.jpg"

rm $tmp

wget -O $tmp http://xplanet.sourceforge.net/clouds/clouds_2048.jpg

if [ -f $tmp ] ; then
  mogrify -resize 2000x1000 $tmp
  if [ -f $img ] ; then
    rm $img
  fi
  mv $tmp $img
  chown -R jarvis:www-data $pat && chmod -R 775 $pat
fi
