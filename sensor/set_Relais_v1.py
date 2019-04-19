import RPi.GPIO as GPIO
import time

GPIO.setmode(GPIO.BCM)
Relais1 = 12
Relais2 = 16
Relais3 = 20
Relais4 = 21


GPIO.setup(Relais1, GPIO.OUT)
GPIO.setup(Relais2, GPIO.OUT)
GPIO.setup(Relais3, GPIO.OUT)
GPIO.setup(Relais4, GPIO.OUT)

GPIO.output(Relais1, False)
GPIO.output(Relais2, False)
GPIO.output(Relais3, False)
GPIO.output(Relais4, False)

while True:
    GPIO.output(Relais1, True)
    time.sleep(1)
    GPIO.output(Relais2, True)
    time.sleep(1)
    GPIO.output(Relais3, True)
    time.sleep(1)
    GPIO.output(Relais4, True)
    time.sleep(3)
    GPIO.output(Relais1, False)
    time.sleep(1)
    GPIO.output(Relais2, False)
    time.sleep(1)
    GPIO.output(Relais3, False)
    time.sleep(1)
    GPIO.output(Relais4, False)
    time.sleep(3)





