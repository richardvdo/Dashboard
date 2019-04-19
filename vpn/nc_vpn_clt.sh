#! /bin/bash

### BEGIN INIT INFO
# Provides:          nc_vpn_clt
# Required-Start:
# Required-Stop:
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: vpn for Jarvis
# Description:       Enable service provided by daemon.
### END INIT INFO

src_port=8889
dst_port=12346
dst_host=10.0.0.1


nc_vpn()
{
	while [ "true" ]
	do
  		last | grep ppp | grep "logged in" | nc -q 1 -p $src_port -u $dst_host $dst_port
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
