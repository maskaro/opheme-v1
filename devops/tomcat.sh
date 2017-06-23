#!/bin/sh
# Tomcat Startup Script

CATALINA_HOME=/opt/tomcat; export CATALINA_HOME
JAVA_HOME=/usr/lib/jvm/java-6-openjdk; export JAVA_HOME
TOMCAT_OWNER=root; export TOMCAT_OWNER

start() {
        echo -n "Starting Tomcat:  "
        su $TOMCAT_OWNER -c $CATALINA_HOME/bin/startup.sh
        sleep 2
}
stop() {
        echo -n "Stopping Tomcat: "
        su $TOMCAT_OWNER -c $CATALINA_HOME/bin/shutdown.sh
}

# See how we were called.
case "$1" in
  start)
        start
        ;;
  stop)
        stop
        ;;
  restart)
        stop
        start
        ;;
  *)
        echo $"Usage: tomcat {start|stop|restart}"
        exit
esac
