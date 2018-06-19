#!/bin/bash

##
CHECK=$(php foreground-check.php)
case ${CHECK} in
    IMPORT_DB_BY_ROOT)
        mysql -uroot -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    IMPORT_DB_BY_USER)
        mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    READY)
        echo '>>>>>> Vtiger CRM is ready.' ;;
    *)
        echo '>>>>>> Vtiger CRM: '${CHECK} ;;
esac

## run cron
cron

## run apache
apache2-foreground
