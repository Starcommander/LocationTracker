#!/bin/bash
export JAVA_HOME=/usr/lib/jvm/java-21-openjdk-amd64
exec /opt/gradle-8.5/bin/gradle "$@"