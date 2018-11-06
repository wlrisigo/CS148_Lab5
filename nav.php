<!-- ######################     Main Navigation   ########################## -->
<nav>
    <ol>
        <?php
        // This sets a class for current page so you can style it differently
        
        print '<li ';
        if ($PATH_PARTS['filename'] == 'index') {
            print ' class="activePage" ';
        }
        print '><a href="index.php">Home</a></li>';
       
        print '<li ';
        if ($PATH_PARTS['filename'] == ' form-hiker-trails') {
            print ' class="activePage" ';
        }
        print '><a href=" form-hiker-trails.php">Form</a></li>';
        
        print '<li ';
        if ($PATH_PARTS['filename'] == 'tables') {
            print ' class="activePage" ';
        }
        print '><a href="tables.php">Tables</a></li>';
        
        print '<li ';
        if ($PATH_PARTS['filename'] == 'form-trails') {
            print ' class="activePage" ';
        }
        print '><a href="form-trails.php">Add Trails</a></li>';

        ?>
    </ol>
</nav>
<!-- #################### Ends Main Navigation    ########################## -->

