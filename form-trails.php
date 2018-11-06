<?php
include 'top.php';
print PHP_EOL . '<!-- SECTION: 1 Initialize variables -->' . PHP_EOL;
$update = false;

print PHP_EOL . '<!-- SECTION: 1a. debugging setup -->' . PHP_EOL;
if (DEBUG) {
    print '<p>Post Array:</p><pre>';
    print_r($_POST);
    print '</pre>';
}

print PHP_EOL . '<!-- SECTION: 1b form variables -->' . PHP_EOL;

$pmkTrailsId = -1;
$trailName = ""; 
$totalDistance = "";
$hikingTime = "";
$verticalRise = "";
$rating = "";

// If the form is an update we need to intial the values from the table
if (isset($_GET["id"])) {
    $pmktrailsId = (int) htmlentities($_GET["id"], ENT_QUOTES, "UTF-8");

    $query = 'SELECT fldTrailName, fldTotalDistance, fldHikingTime, fldVerticalRise, fldRating ';
    $query .= 'FROM tblTrails WHERE pmkTrailsId = ?';

    $data = array($pmktrailsId);

    if ($thisDatabaseReader->querySecurityOk($query, 1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $trails = $thisDatabaseReader->select($query, $data);
        }

    $trailName = $trails[0]["fldTrailName"];
    $totalDistance = $trails[0]["fldTotalDistance"];
    $hikingTime = $trails[0]["fldHikingTime"];
    $verticalRise = $trails[0]["fldVerticalRide"];
    $rating = $trails[0]["fldRating"];
    }
print PHP_EOL . '<!-- SECTION: 1c form error flags -->' . PHP_EOL;

$trailNameERROR = false;
$totalDistanceERROR = false;
$hikingTimeERROR = false;
$verticalRiseERROR = false;
$ratingERROR = false;

print PHP_EOL . '<!-- SECTION: 1d misc variables -->' . PHP_EOL;

$errorMsg = array();

$mailed = false;

$dataEntered = false;

print PHP_EOL . '<!-- SECTION: 2 Process for when the form is submitted -->' . PHP_EOL;

if (isset($_POST["btnSubmit"])) {
        print PHP_EOL . '<!-- SECTION: 2a Security -->' . PHP_EOL;

    $thisURL = DOMAIN . PHP_SELF;

    if (!securityCheck($thisURL)) {
        $msg = '<p>Sorry you cannot access this page.</p>';
        $msg.= '<p>Security breach detected and reported.</p>';
        die($msg);
    }

    print PHP_EOL . '<!-- SECTION: 2b Sanitize (clean) data  -->' . PHP_EOL;

    $pmktrailsId = (int) htmlentities($_POST["hidtrailsId"], ENT_QUOTES, "UTF-8");
    if ($pmktrailsId > 0) {
        $update = true;
    }

    $trailName = htmlentities($_POST["txtTrailName"], ENT_QUOTES, "UTF-8");

    $totalDistance = htmlentities($_POST["intTotalDistance"], ENT_QUOTES, "UTF-8");

    $hikingTime = htmlentities($_POST["txtHikingTime"], ENT_QUOTES, "UTF-8");

    $verticalRise = htmlentities($_POST["txtVerticalRise"], ENT_QUOTES, "UTF-8");

    $rating = htmlentities($_POST["txtRating"], ENT_QUOTES, "UTF-8");

    print PHP_EOL . '<!-- SECTION: 2c Validation -->' . PHP_EOL;

    if ($trailName == "") {
        $errorMsg[] = "Please enter your first name";
        $trailNameERROR = true;
    } elseif (!verifyAlphaNum($trailName)) {
        $errorMsg[] = "Your first name appears to have extra character.";
        $trailNameERROR = true;
    }

    if ($totalDistance == "") {
        $errorMsg[] = "Enter your last name";
        $totalDistanceERROR = true;
    } elseif (!verifyAlphaNum($totalDistance)) {
        $errorMsg[] = "Your last name appears to have extra character.";
        $totalDistanceERROR = true;
    }

    if ($hikingTime == "") {
        $errorMsg[] = "Enter the trail's hiking timed duration";
        $hikingTimeERROR = true;
    }// should check to make sure its the correct date format

    if($verticalRise == ""){
        $errorMsg[] = "Enter the trail's Height";
        $verticalRiseERROR = true;
    }
    if(rating == ""){
        $errorMsg[] = "Enter the trail's Height";
        $ratingERROR = true;
    }


    print PHP_EOL . '<!-- SECTION: 2d Process Form - Passed Validation -->' . PHP_EOL;

    if (!$errorMsg) {
        if (DEBUG) {
            print "<p>Form is valid</p>";
        }

    print PHP_EOL . '<!-- SECTION: 2e Save Data -->' . PHP_EOL;

    $dataEntered = false;
    $data = array();

    $data[] = $trailName;
    $data[] = $totalDistance;
    $data[] = $hikingTime;
    $data[] = $verticalRise;
    $data[] = $rating;

    try {
    $thisDatabaseWriter->db->beginTransaction();

    if ($update) {
        $query = 'UPDATE tbltrails SET ';
    } else {
        $query = 'INSERT INTO tbltrails SET ';
    }

    $query .= 'fldTrailName = ?, ';
    $query .= 'fldTotalDistance = ?, ';
    $query .= 'fldHikingTime = ?, ';
    $query .= 'fldVerticalRise = ? ';
    $query .= 'fldRating = ? ';

    if (DEBUG) {
        $thisDatabaseWriter->TestSecurityQuery($query, 0);
        print_r($data);
    }

    if ($update) {
        $query .= 'WHERE pmktrailsId = ?';
        $data[] = $pmktrailsId;

        if ($thisDatabaseReader->querySecurityOk($query, 1)) {
            $query = $thisDatabaseWriter->sanitizeQuery($query);
            $results = $thisDatabaseWriter->update($query, $data);
        }
        } else {
            if ($thisDatabaseWriter->querySecurityOk($query, 0)) {
                $query = $thisDatabaseWriter->sanitizeQuery($query);

                $results = $thisDatabaseWriter->insert($query, $data);

                $primaryKey = $thisDatabaseWriter->lastInsert();
            }
    }

    if (DEBUG) {
    print "<p>pmk= " . $primaryKey;
        }

        // all sql statements are done so lets commit to our changes

        $dataEntered = $thisDatabaseWriter->db->commit();

        if (DEBUG)
         print "<p>transaction complete ";
        } catch (PDOExecption $e) {
            $thisDatabaseWriter->db->rollback();
            if (DEBUG)
                 print "Error!: " . $e->getMessage() . "</br>";
                 $errorMsg[] = "There was a problem with accepting your data please contact us directly.";
        }

        print PHP_EOL . '<!-- SECTION: 2f Create message -->' . PHP_EOL;

        print PHP_EOL . '<!-- SECTION: 2g Mail to user -->' . PHP_EOL;
        } // end form is valid
        } // ends if form was submitted.


    print PHP_EOL . '<!-- SECTION 3 Display Form -->' . PHP_EOL;
    ?>
    <main>
        <article id="main">
            <?php
            print PHP_EOL . '<!-- SECTION 3a  -->' . PHP_EOL;

            if ($dataEntered) { // closing of if marked with: end body submit
                print "<h1>Record Saved</h1> ";

                // Display the message you created in in SECTION: 2f
            } else {

            print PHP_EOL . '<!-- SECTION 3b Error Messages -->' . PHP_EOL;

            if ($errorMsg) {
                print '<div id="errors">' . PHP_EOL;
                print '<h2>Your form has the following mistakes that need to be fixed.</h2>' . PHP_EOL;
                print '<ol>' . PHP_EOL;
                foreach ($errorMsg as $err) {
                    print '<li>' . $err . '</li>' . PHP_EOL;
                }
                print '</ol>' . PHP_EOL;
                print '</div>' . PHP_EOL;
            }

            print PHP_EOL . '<!-- SECTION 3c html Form -->' . PHP_EOL;
            ?>
                
                
            <h2>Trails</h2>
            <form action="<?php print PHP_SELF; ?>"
                  method="post"
                  id="frmRegister">
                
                
                <input type="hidden" id="hidtrailsId" name="hidtrailsId"
                       value="<?php print $pmktrailsId; ?>"
                >

 <fieldset class = "contact">
    <p>
        <label class="required" for="txtTrailName">Trail Name</label>
        <input autofocus
            <?php if ($trailNameERROR)
                print 'class="mistake"'; ?>
               id="txtTrailName"
               maxlength="45"
               name="txtTrailName"
               onfocus="this.select()"
               placeholder="Enter Trail name"
               tabindex="100"
               type="text"
               value="<?php print $trailName; ?>"
        >
    </p>

    <p>
        <label class="required" for="intTotalDistance">Distance (Miles)</label>
        <input
            <?php if ($totalDistanceERROR)
                print 'class="mistake"'; ?>
            id="intTotalDistance"
            maxlength="45"
            name="intTotalDistance"
            onfocus="this.select()"
            tabindex="110"
            type="number"
            min = "0"
            value="<?php print $totalDistance; ?>"
        >
    </p>

    <p>
        <label class="required" for="txtHikingTime">Hiking Duration (hh:mm:ss)</label>
        <input
            <?php if ($hikingTimeERROR)
                print 'class="mistake"'; ?>
            id="txtHikingTime"
            name="txtHikingTime"
            step = "1"
            type="time"
            value="<?php print date("H:i:s", $hikingTime); ?>"
        >
    </p>

     <p>
         <label class="required" for="txtVerticalRise">Height (ft)</label>
         <input
             <?php if ($verticalRiseERROR)
                 print 'class="mistake"'; ?>
             id="txtVerticalRise"
             name="txtVerticalRise"
             onfocus="this.select()"
             tabindex="120"
             type="number"
             min = "0"
             max = "5000"
             value="<?php print $verticalRise; ?>"
         >
     </p>

     <p>
         <div class="required" for="txtRating">Difficulty</div>
        <input
             <?php if ($ratingERROR)
                 print 'class="mistake"'; ?>
             id="easy"
             name="txtRating"
             type="radio"
             value="Easy"
         > <label for="easy">Easy</label>

         <input
             <?php if ($ratingERROR)
                 print 'class="mistake"'; ?>
             id="moderate"
             name="txtRating"
             type="radio"
             value="Moderate"
         > <label for="moderate">Moderate</label>

         <input
             <?php if ($ratingERROR)
                 print 'class="mistake"'; ?>
             id="moderately-strenuous"
             name="txtRating"
             type="radio"
             value="Moderately Strenuous"
         > <label for="moderately-strenuous">Moderately Strenuous</label>

         <input
             <?php if ($ratingERROR)
                 print 'class="mistake"'; ?>
             id="strenuous"
             name="txtRating"
             type="radio"
             value="Strenuous"
         > <label for="strenuous">Strenuous</label>
     </p>

</fieldset>


<fieldset class="buttons">
    <input type="submit" id="btnSubmit" name="btnSubmit" value="Save" tabindex="900" class="button">
</fieldset> <!-- ends buttons -->
</form>
<?php
} // end body submit
?>
</article>
</main>

<?php
include "footer.php";
if (DEBUG)
    print "<p>END OF PROCESSING</p>";
?>

</body>
</html>