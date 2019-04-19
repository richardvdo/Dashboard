#!/bin/bash

dst_port=12345
dst_dir=/home/jarvis/ifstat
dst_file=eth0.log

nc -u -l 12345 >> $dst_dir/$dst_file&
