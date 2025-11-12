@echo off

setLocal EnableDelayedExpansion

if defined CP_SHARED_JAR_DIR (
 for /R %CP_SHARED_JAR_DIR% %%a in (*.jar) do (
   set CLASSPATH=!CLASSPATH!;%%a
 )
)

set CLASSPATH="C:\Users\rodri\Desktop\lrgs/bin/opendcs.jar;C:\Users\rodri\Desktop\lrgs/bin/hibernate.cfg.xml;
for /R C:\Users\rodri\Desktop\lrgs/dep %%a in (*.jar) do (
  set CLASSPATH=!CLASSPATH!;%%a
)

set CLASSPATH=!CLASSPATH!"

java -Xmx240m -cp !CLASSPATH! -DDCSTOOL_HOME=C:\Users\rodri\Desktop\lrgs -DDECODES_INSTALL_DIR=C:\Users\rodri\Desktop\lrgs -DDCSTOOL_USERDIR=C:\Users\rodri\Desktop\lrgs %*%
