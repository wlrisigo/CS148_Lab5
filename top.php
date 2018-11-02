<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hikers of Vermont</title>
        <meta charset="utf-8">
        <meta name="author" content="Billy Risigo">
        <meta name="description" content="Hikers in vermont">

        <meta name="viewport" content="width=device-width, initial-scale=1">


        <link rel="stylesheet" href="css/base.css" type="text/css" media="screen">

        <?php
        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // inlcude all libraries. 
        // 
        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        print '<!-- begin including libraries -->';
        
        include 'lib/constants.php';
        include LIB_PATH . '/Connect-With-Database.php';
        require_once 'lib/security.php';
        include_once 'lib/validation-functions.php';     
        include_once 'lib/mail-message.php';   
        print '<!-- libraries complete-->';
        ?>	

    </head>

    <!-- **********************     Body section      ********************** -->
    <?php
    print '<body id="' . $PATH_PARTS['filename'] . '">';
    include 'header.php';
    include 'nav.php';
    ?>