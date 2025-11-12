#!/bin/bash

# Script para executar getDcpMessages no Linux
# Substitui o getDcpMessages.bat do Windows

LRGS_HOME="/var/www/html/lrgs"

# Constrói o CLASSPATH
CLASSPATH="$LRGS_HOME/bin/opendcs.jar:$LRGS_HOME/bin/hibernate.cfg.xml"

# Adiciona todos os JARs da pasta dep
for jar in $LRGS_HOME/dep/*.jar; do
    CLASSPATH="$CLASSPATH:$jar"
done

# Executa o comando Java
java -Xmx240m \
    -cp "$CLASSPATH" \
    -DDCSTOOL_HOME="$LRGS_HOME" \
    -DDECODES_INSTALL_DIR="$LRGS_HOME" \
    -DDCSTOOL_USERDIR="$LRGS_HOME" \
    lrgs.lddc.GetDcpMessages "$@"
