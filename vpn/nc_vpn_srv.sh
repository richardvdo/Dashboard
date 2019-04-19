#!/bin/bash

### BEGIN INIT INFO
# Provides:          nc_vpn_srv
# Required-Start:
# Required-Stop:
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: vpn for Jarvis
# Description:       Enable service provided by daemon.
### END INIT INFO

dst_port=12346
dst_dir=/home/jarvis/vpn
dst_file=vpn.log

nc_vpn()
{
  while [ "true" ]
  do
    nc -q 1 -u -l 12346 > $dst_dir/$dst_file < /dev/null
    sleep 5
  done
}

case "$1" in
        stop)
                echo " Stoping vpn for Jarvis..."
                killall vpn
                killall nc
                ;;
        start)
                echo " Starting vpn for Jarvis..."
                nc_vpn&
                exit 0
                ;;
        *)
                exit 1
                ;;
esac

exit 0
