 #!/bin/sh

PROCESS_NUM=$(ps -ef | grep teleinfo_puissance_v2 | grep -v "grep" | wc -l)

if [ $PROCESS_NUM -eq 1 ]
then 
	exit 1
else
	php /home/pi/DashScreen/PiHomeDashScreen/teleinfo/teleinfo_puissance_v2.php
fi
	exit 0