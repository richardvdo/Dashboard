 #!/bin/sh

PROCESS_NUM=$(ps -ef | grep get_temp | grep -v "grep" | wc -l)

if [ $PROCESS_NUM -eq 1 ]
then 
	exit 1
else
	python /home/pi/DashScreen/PiHomeDashScreen/sensor/get_temp_v2.py
fi
	exit 0