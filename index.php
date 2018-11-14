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

$query = 'SELECT fldTrailName, fldRating FROM tblTrails';

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
if (is_array($records)) {
    foreach ($records as $record) {
        print '<p>' . $record['fldTrailName'] . ' <b>Rating:  </b>' . $record['fldRating'] . '</p>';
    }
}

if($isAdmin){
    print '<a id = "isAdmin" href= "https://wrisigo.w3.uvm.edu/cs148/dev-lab5/form.php"> EDIT </a>';
}


print '</article>';
include "footer.php";
?>