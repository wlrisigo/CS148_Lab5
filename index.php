<?php

//##############################################################################
//
// main home page for the site 
// 
//##############################################################################
include "top.php";

// Begin output
print '<article>';
print '<h2>A Hikers Home</h2>';
$records = '';

$query = 'SELECT fldFirstName, fldLastName FROM tblHikers';

// NOTE: The full method call would be:
//           $thisDatabaseReader->querySecurityOk($query, 0, 0, 0, 0, 0)
if ($thisDatabaseReader->querySecurityOk($query, 0)) {
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, '');
    
}

if (DEBUG) {
    print '<p>Contents of the array<pre>';
    print_r($records);
    print '</pre></p>';
}

print '<h2 class="alternateRows">Meet the Hikers!</h2>';
if (is_array($records)) {
    foreach ($records as $record) {
        print '<p>' . $record['fldFirstName'] . ' ' . $record['fldLastName'] . '</p>';
    }
}


print '</article>';
include "footer.php";
?>