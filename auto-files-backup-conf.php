<?php

DEFINE('DEBUG',TRUE);
DEFINE('DEBUG_VERBOSE', TRUE);
DEFINE('LOG_TRACES',TRUE);
DEFINE('EXECUTE_COMMANDS',TRUE);

$afb_timezone = "Europe/Madrid";

$afb_version = "0.1.0.0";

// Where to create the backups; It should already exist
$afb_backup_origin_dir_recursive = array(
    "/var/www",
);
  
$afb_backup_origin_dir = array(
    "/etc/",
    "/var/spool/cron/crontabs/",
);

$afb_backup_origin_exclude_dir = array(
    "/boot",
);

$afb_backup_destination_dir = "data";


// Rotation Settings

// Which day do you want monthly backups? (01 to 31)
// If the chosen day is greater than the last day of the month, it will be done
// on the last day of the month.
//  Set to 0 to disable monthly backups.
$afb_do_monthly = array(
    01,
);

// Which day do you want weekly backups? (0 to 6 where 0 is Sunday)
// Set empty to disable weekly backups: $afb_do_weekly = array();
$afb_do_weekly = array(
    6,
);

// Set rotation of daily backups. VALUE*24hours
// If you want to keep only today's backups, you could choose 1, i.e. everything older than 24hours will be removed.
$afb_rotation_daily=6;

// Set rotation for weekly backups. VALUE*24hours
$afb_rotation_weekly=35;

// Set rotation for monthly backups. VALUE*24hours
$afb_rotation_monthly=150;

// todo
// Should we email results? Also should we email critical errors?  false, true
$afb_send_email=true;

// todo
// EMAIL address to send results
$afb_email_address="foo@bar.com";

// todo
// Email Subject
$afb_email_subject ="Backup done"; //todo put hostname 

?>
