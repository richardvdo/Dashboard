#! /bin/bash

### BEGIN INIT INFO
# Provides:          nc_ifstat_clt
# Required-Start:
# Required-Stop:
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: ifstat for Jarvis
# Description:       Enable service provided by daemon.
### END INIT INFO

src_port=8888
dst_port=12345
dst_host=10.0.0.1
eth=eth0

nc_ifstat()
{
	while [ "true" ]
	do
  		ifstat -n -i $eth | nc -p $src_port -u $dst_host $dst_port
  		sleep 5
	done
}

case "$1" in
        stop)
		echo " Stoping ifstat for Jarvis..."
                killall ifstat
		killall nc
		;;
        start)
		echo " Starting ifstat for Jarvis..."
		nc_ifstat&
		exit 0	
                ;;
        *)
                exit 1
                ;;
esac

exit 0
