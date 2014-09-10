<?php

// Load the config file
require_once('auto_files_backup_conf.php');
// Set the timezone
date_default_timezone_set($afb_timezone);
// Clear the string where I store the log output 
$afb_log = "";
// Count the number of problems 
$afb_problems = 0;

// ****************************************************************************    
// Internal functions
// ****************************************************************************    

// Check if the string ends with a slash "/" and if not add it
function add_last_slash(&$my_path) {
    if (substr($my_path,-1)!= "/") {
        $my_path .= "/";
    }
}

// Check if exists a directory and if not create it
function create_directory($my_path) {
    global $afb_log;
    if (!file_exists($my_path)) {
        if (!mkdir($my_path,0700)) {
            $afb_log .= "\nProblem creating the ". $my_path . " directory.";
            $afb_problems++;
        } else {
            $afb_log .= "\nThe data directory ". $my_path . " has been created.";
        }
    } else {
        $afb_log .= "\nThe data directory " . $my_path . " exists.";
    }
}

// Removes some parts in the outupt of the print_r() function
function clean_print_r($text_to_clean) {
    return substr($text_to_clean,8, strlen($text_to_clean)-11) . "\n";
}

// ****************************************************************************    
// Get the PHP version
// Todo. Check if PHP >= 5.3.0 http://us1.php.net/manual/es/function.phpversion.php
// Todo. Check the config variables 
// ****************************************************************************    
$afb_log .= "\nStart the \"Auto Files Backup\" execution. Version " . $afb_version;
$afb_log .= "\nCurrent PHP version: " . phpversion();
//echo PHP_VERSION_ID;

// ****************************************************************************    
// Create the daily, weekly and monthly destination paths 
// ****************************************************************************    

// Check if the destination path is relative or absolute and make it absolute
if (substr($afb_backup_destination_dir,0,1) != "/") {
    $afb_backup_destination_dir = getcwd(). "/". $afb_backup_destination_dir;
} 

add_last_slash($afb_backup_destination_dir);

// Daily, weekly and monthly routes
$afb_backup_destination_daily = $afb_backup_destination_dir . "daily/";
$afb_backup_destination_weekly = $afb_backup_destination_dir . "weekly/";
$afb_backup_destination_monthly = $afb_backup_destination_dir . "monthly/";

$afb_log .= "\nDestination path: " . $afb_backup_destination_dir; 
$afb_log .= "\nDaily destination path: " . $afb_backup_destination_daily; 
$afb_log .= "\nWeekly destination path: " . $afb_backup_destination_weekly; 
$afb_log .= "\nMonthly destination path: " . $afb_backup_destination_monthly; 

// Create the data directory
create_directory($afb_backup_destination_dir);

// Create the daily data directory
create_directory($afb_backup_destination_daily);

// Create the weekly directory
create_directory($afb_backup_destination_weekly);

// Create the monthly directory
create_directory($afb_backup_destination_monthly);


// ****************************************************************************    
// Make the complete source directory list
// ****************************************************************************    

// First check the non recursive paths
$afb_log .= "\nComplete non recursive path list:\n";
//$afb_log .= substr(print_r($afb_backup_origin_dir,true),8, strlen(print_r($afb_backup_origin_dir,true))-11);
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir,true));
// Check if each origin path finishes with a / and add it 
foreach ($afb_backup_origin_dir as $index=>$value) {
    add_last_slash($afb_backup_origin_dir[$index]);
}

// Copy the array of paths  
$afb_backup_origin_dir_all = $afb_backup_origin_dir;

// Now check the recursive paths
$afb_log .= "\nComplete recursive path list:\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir_recursive,true));
foreach ($afb_backup_origin_dir_recursive as $index=>$value) {
    // Check if the directory route finish with a / and add it
    add_last_slash($afb_backup_origin_dir_recursive[$index]);
    // Obtain the subdirectory list
    $directory_list = glob($afb_backup_origin_dir_recursive[$index] . "*", GLOB_ONLYDIR);
    foreach ($directory_list as $index2=>$value2) {
        // Check if each subdirectory path finish with a / and add it
        add_last_slash($directory_list[$index2]);
        // Insert the subdirectory in the array
        array_push($afb_backup_origin_dir_all, $directory_list[$index2] );
    }
}
$afb_log .=  "\nComplete directory list to backup:\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir_all, true));

// Remove the exclude directories
$afb_log .=  "\nComplete exclude directory list:\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_exclude_dir, true));
foreach ($afb_backup_origin_exclude_dir as $index=>$value) {
    add_last_slash($afb_backup_origin_exclude_dir[$index]);
    foreach (array_keys($afb_backup_origin_dir_all, $afb_backup_origin_exclude_dir[$index]) as $key) {
        unset($afb_backup_origin_dir_all[$key]);
    }
}

 $afb_log .= "\nComplete directory list to backup after proccess the exclude list\n";
 $afb_log .= clean_print_r(print_r($afb_backup_origin_dir_all, true));

// Find duplicates in the directories name
$afb_backup_origin_dir_all = array_unique($afb_backup_origin_dir_all);
$afb_log .= "\nComplete directory list to backup after remove duplicates.\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir_all, true));

// todo: find same dirs with different base route

// Remove paths that don't exist
foreach ($afb_backup_origin_dir_all as $index=>$value) {
    if (!file_exists($value)) {
        unset($afb_backup_origin_dir_all[$index]);
    }
}
$afb_log .= "\nComplete directory list to backup after remove paths that don't exist.\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir_all, true));

// todo: archivos sueltos

// Reindex the array
$afb_backup_origin_dir_all = array_values($afb_backup_origin_dir_all);
$afb_log .= "\nComplete directory list to backup after reindex the array.\n";
$afb_log .= clean_print_r(print_r($afb_backup_origin_dir_all, true));

// Change the "/" for a "_" in the paths
$afb_backup_destination_files_all = $afb_backup_origin_dir_all;
foreach ($afb_backup_destination_files_all as $index=>$value) {
    // Remove the first and the last / in the path
    $afb_backup_destination_files_all[$index] = substr( $value , 1 , strlen($value) - 2  );
    // Replace the "/· for a "_" in the paths
    $afb_backup_destination_files_all[$index] = str_replace("/","_",$afb_backup_destination_files_all[$index]); //. ".tar";
}

$afb_log .= "\nComplete destination file list.\n";
$afb_log .= clean_print_r(print_r($afb_backup_destination_files_all, true));


// Create the daily backup
//
$afb_now=date("Ymd_His");
$afb_week_day = date('w');
$afb_month_day = date('d');
$afb_do_weekly_backup = in_array($afb_week_day, $afb_do_weekly);
$afb_do_monthly_backup = in_array($afb_month_day, $afb_do_monthly);
$afb_log .= "\nWeek day: " . $afb_week_day;
$afb_log .= "\nMonth day: " . $afb_month_day;
$afb_log .= "\nDo the weekly backup today? " . ($afb_do_weekly_backup ? 'Yes' : 'No');
$afb_log .= "\nDo the monthly backup today? " . ($afb_do_monthly_backup ? 'Yes' : 'No');

$afb_log .= "\n\nStart the daily backup. \n";

foreach ($afb_backup_origin_dir_all as $index=>$value) {
    // Create the .tar file path
    $tar_file = $afb_backup_destination_daily . $afb_now . "_" . $afb_backup_destination_files_all[$index] . ".tar";
    if (file_exists($tar_file . ".gz")) {
        ///// todo finish
    }
    //$file_name = pathinfo($tar_file);
    //$file_name = $file_name['filename'];
    $file_name = $afb_backup_destination_files_all[$index];
    $file_extension = "";
    if (strpos($file_name,".") > -1) {
  
        $file_extension = substr($file_name, strpos($file_name,".")+1) . ".";
        $file_name = substr($file_name,0,strpos($file_name,"."));
    }
    $file_name = $afb_now . "_" . $file_name;
    $file_extension .= "tar.gz";
    $afb_log .= "\n*************************************************************************";   
    $afb_log .= "\nTar file: " . $tar_file . "\n";
    $afb_log .= "Dir to compress: " . $afb_backup_origin_dir_all[$index] . "\n";
    $afb_log .= "File extension: " . $file_extension . "\n";
    $afb_log .= "Output file: " . $file_name . "." . $file_extension . "\n";


    try {
        //https://bugs.php.net/bug.php?id=49190
        // https://www.google.es/search?q=new+phardata+multiples+dots
        /*
        $afb_log .= "\n - 0: Start the ". $afb_backup_origin_dir_all[$index] . " backup.\n";
        $archive = new PharData($tar_file); 
        $afb_log .= " - 1: Make the .tar file.\n";
        $archive->buildFromDirectory($afb_backup_origin_dir_all[$index]); 
        $afb_log .= " - 2: Make the .gz file.\n";
        $archive->compress(Phar::GZ,$file_extension); 
        $afb_log .= " - 3: Delete the .tar file: " . $tar_file  . "\n";
        unlink($tar_file); 
        $afb_log .= " - 4: End the ". $afb_backup_origin_dir_all[$index] . " backup.\n";
        $afb_log .= " - 5: Compress " . $afb_backup_origin_dir_all[$index] . " in " . $afb_backup_destination_daily . $file_name . "." . $file_extension . "\n";
        */
    
        exec("tar -czf " . $tar_file  . ".gz " . $afb_backup_origin_dir_all[$index]); 
    }
    catch (Exception $e) {
        $afb_log .= "Exception: " . $afb_backup_origin_dir_all[$index] . " in " . $afb_backup_destination_daily . $file_name . "." . $file_extension . ".Exception: " . $e;
        $afb_problems++;
    }


    //
    // Weekly backups
    //
    if ($afb_do_weekly_backup) {
        $origin_file = $afb_backup_destination_daily . $file_name . "." . $file_extension;
        $destination_file = $afb_backup_destination_weekly . $file_name . "." . $file_extension;
        if (!copy($origin_file, $destination_file)) {
            $afb_log .= "\nWeekly backup. Error copying " . $origin_file . " to " . $destination_file;
        } else {
        $afb_log .= "\nWeekly backup. Copied " . $origin_file . " to " . $destination_file; // . "\n"; 
        }
    }

     //
     // Monthly backups
     //
     if ($afb_do_monthly_backup) {
         $origin_file = $afb_backup_destination_daily . $file_name . "." . $file_extension;
         $destination_file = $afb_backup_destination_monthly . $file_name . "." . $file_extension;
         if (!copy($origin_file, $destination_file)) {
             $afb_log .= "\nMonthly backup. Error copying " . $origin_file . " to " . $destination_file;
         } else {
            $afb_log .= "\nMonthly backup. Copied " . $origin_file . " to " . $destination_file; // . "\n"; 
         }
     }


}
    //
    // Rotate backups
    //

    $afb_seconds_daily_rotation = $afb_rotation_daily*24*60*60;
    $afb_seconds_weekly_rotation = $afb_rotation_weekly*24*60*60;
    $afb_seconds_monthly_rotation = $afb_rotation_monthly*24*60*60;
    $afb_log .= "\nRemove daily files older than " . $afb_rotation_daily . " days";
    $afb_log .= "\nRemove weekly files older than " . $afb_rotation_weekly . " days";
    $afb_log .= "\nRemove monthly files older than " . $afb_rotation_monthly . " days";

    // Remove the old daily backups
    $files = glob($afb_backup_destination_daily ."*");
    foreach($files as $file) {
        if(is_file($file)
            && time() - filemtime($file) >= $afb_seconds_daily_rotation) { 
            $afb_log .= "\nRemove the daily file: " . $file;
            unlink($file);
        }
    }

     // Remove the old weekly backups
     $files = glob($afb_backup_destination_weekly ."*");
     foreach($files as $file) {
         if(is_file($file)
             && time() - filemtime($file) >= $afb_seconds_weekly_rotation) {
             $afb_log .= "\nRemove the weekly file: " . $file;
             unlink($file);
         }
     }


     // Remove the old monthly backups
     $files = glob($afb_backup_destination_monthly ."*");
     foreach($files as $file) {
         if(is_file($file)
             && time() - filemtime($file) >= $afb_seconds_monthly_rotation) {
             $afb_log = "\nRemove the monthly file: " . $file;
             unlink($file);
         }
     }

echo $afb_log . "\n";

// Todo: send a mail with the result
//
    
?>
