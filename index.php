<?php
require 'config.php';
#############   Backup    ###############
if (isset($_REQUEST['backup'])) {
    /* Create a sql file and then  writing sql query to  .sql file    */
    $newImport = new backup_restore($db_host, $db_name, $db_user, $db_pass);
    $myfile = fopen("backup/$db_name.sql", "w") or die("Unable to open file!"); /* Create a databaseName.sql file in backup folder */
    fwrite($myfile, $newImport->backup()); /* write sql to .sql file */
    fclose($myfile); // close file
    ########################   Now zip that file    ################################
    $zip = new ZipArchive();
    $filename = "backup/backup-" . date("Y-m-d_H-i-s") . ".sql.zip"; /* Create a .zip file and name it to backup-year-month-day_hour-min-sec.sql.zip in backup folder */
    if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
        exit("cannot open <$filename>n");
    }
    $zip->addFile("backup/$db_name.sql", "$db_name.sql"); /* add previous .sql file to .sql.zip file */
    $zip->close();

    unlink("backup/$db_name.sql"); /* Delete databaseName.sql file , because we have .sql.zip file now*/
}
#############   End Backup    ###############

#############   Restore    ###############
if (isset($_REQUEST['restore'])) {
    $filename = $_FILES['rfile']['tmp_name'];
    $filetype = $_FILES['rfile']['type'];

    $zip = new ZipArchive;
    if ($zip->open($filename) === TRUE) {
        $zip->extractTo('backup/unzip/test/');
        $sql_file_name = $zip->getNameIndex(0);
        $zip->close();
        echo ' ok ';
    } else {
        echo ' failed ';
    }

  
    $newImport = new backup_restore($db_host, $db_name, $db_user, $db_pass);
    $filetype = $_FILES['rfile']['type'];
    $filename = $_FILES['rfile']['tmp_name'];
    $error = ($_FILES['rfile']['tmp_name'] == 0) ? false : true;
    if ($filetype == "application/octet-stream" && !$error) {
        //call of restore function
        $sql_file_name = "backup/unzip/test/" . $sql_file_name;
        $message = $newImport->restore($sql_file_name);
        echo $message;
    }
}
#############   End Restore    ###############
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"  dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Database Back up And Restore Script</title>
    </head>
    <body>
        <form name="import" action="" method="POST" enctype="multipart/form-data">
            <label>File to Restore from: </label>
            <input type="file" name="rfile" />
            <p>
                <input type="submit" name="backup" value="Backup">
                <input type="submit" name="restore" value="Restore">
            </p>
        </form>
    </body>
</html>