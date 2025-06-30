#!/bin/bash
PHP_PATH=$(which php)
CRON_JOB="*/5 * * * * $PHP_PATH $(pwd)/cron.php"
( crontab -l 2>/dev/null | grep -v -F "$PHP_PATH $(pwd)/cron.php" ; echo "$CRON_JOB" ) | crontab -
echo "CRON job set to run every 5 minutes."
