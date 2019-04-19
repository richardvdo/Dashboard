#!/bin/sh

# a mettre dans etc/init.d
# -rwxrw-rw-   1 root root   71 juil.  7  2018 initsensor.sh
# init port uart
sudo systemctl stop serial-getty@ttyAMA0.service
sudo systemctl disable serial-getty@ttyAMA0.service
sudo chmod o+r /dev/ttyAMA0
stty -F /dev/ttyAMA0 1200 sane evenp parenb cs7 -crtscts

