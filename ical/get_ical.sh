#!/bin/sh

pat=/var/www/html/ical/
cal_rva=$pat"basic_rva.ics"
cal_vdo=$pat"basic_vdo.ics"
cal_mvd=$pat"basic_mvd.ics"
cal_mdu=$pat"basic_mdu.ics"

rm $cal_rva
rm $cal_vdo
rm $cal_mvd
rm $cal_mdu

wget -O $cal_rva https://calendar.google.com/calendar/ical/richardvdo%40gmail.com/private-44f68a1a42f4b2eb1670c18c014d3e0a/basic.ics
wget -O $cal_vdo https://calendar.google.com/calendar/ical/famillevdod%40gmail.com/private-9f66c43bb45305785be4ab47984082df/basic.ics
wget -O $cal_mvd https://calendar.google.com/calendar/ical/marjoriedurand85%40gmail.com/private-b83fdea4b5035f35307e12bd87e639c7/basic.ics
wget -O $cal_mdu https://calendar.google.com/calendar/ical/mathis.durand79%40gmail.com/private-439302b0dcb8e63e6dd748fb359e87fd/basic.ics
