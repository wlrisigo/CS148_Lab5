<?php
include 'top.php';
print PHP_EOL . '<!-- SECTION: 1 Initialize variables -->' . PHP_EOL;
$update = false;
print PHP_EOL . '<!-- SECTION: 1a. debugging setup -->' . PHP_EOL;
//if (DEBUG) {
print '<p>Post Array:</p><pre>';
print_r($_POST);
print '</pre>';
//}
print PHP_EOL . '<!-- SECTION: 1b form variables -->' . PHP_EOL;
$dataDelete =[];
$primaryKey = 0;
$pmkTrailsId = -1;
$trailName = "";
$totalDistance = "";
$hikingTime = "";
$verticalRise = "";
$rating = "";
$trials = [];
//Default - new or update
$tagSet =[];
//trail tags
$trailTags = [];
//For Formatting into Database (HH MM SS)
$HOURS = "00";
$MIN = "00";
$SEC = "00";
$alreadyChosen = array();
$getTags = 'SELECT pmkTag, fldBinary FROM tblTags';
if ($thisDatabaseReader->querySecurityOk($getTags, 0)) {
    $query = $thisDatabaseReader->sanitizeQuery($getTags);
    $tagSet = $thisDatabaseReader->select($getTags);
}
// If the form is an update we need to initial the values from the table
if (isset($_GET["id"])) {
    $pmkTrailsId = (int) htmlentities($_GET["id"], ENT_QUOTES, "UTF-8");
    $query = 'SELECT fldTrailName, fldTotalDistance, fldHikingTime, fldVerticalRise, fldRating ';
    $query .= 'FROM tblTrails WHERE pmkTrailsId = ? ';
    $data = array($pmkTrailsId);
    if ($thisDatabaseReader->querySecurityOk($query, 1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $trails = $thisDatabaseReader->select($query, $data);
    }
    $queryTags = "SELECT * FROM tblTrailTags ";
    $queryTags .= "WHERE pfkTrailsId = ?";
    $trailTagsData = array($pmkTrailsId);
    if ($thisDatabaseReader->querySecurityOk($query, 1)) {
        $queryTags = $thisDatabaseReader->sanitizeQuery($queryTags);
        $trailTags = $thisDatabaseReader->select($queryTags, $trailTagsData);
    }
    $count = 0;
    //makes checkboxes sticky and populates an array holding just the tags
    for($count; $count < count($trailTags); $count++) {
        array_push($alreadyChosen,$trailTags[$count]["pfkTag"]);
        foreach ($tagSet as &$tagging) {
            if ($tagging["pmkTag"] == $trailTags[$count]["pfkTag"]) {
                $tagging["fldBinary"] = 1;
                $tagging[1] = 1;
            }
        }
        unset($tagging);
    }

    $trailName = $trails[0]["fldTrailName"];
    $totalDistance = $trails[0]["fldTotalDistance"];
    $hikingTime = $trails[0]["fldHikingTime"];
    $verticalRise = $trails[0]["fldVerticalRise"];
    $rating = $trails[0]["fldRating"];
    $HOURS=substr($hikingTime, 0,2);
    $MIN=substr($hikingTime, 3,2);
    $SEC=substr($hikingTime, 6,2);
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
    $pmkTrailsId = (int) htmlentities($_POST["hidtrailsId"], ENT_QUOTES, "UTF-8");
    if ($pmkTrailsId > 0) {
        $update = true;
        $dataDelete [0] = $pmkTrailsId;
        $DELETE = 'DELETE FROM tblTrailTags WHERE pfkTrailsId = ?';
        if ($thisDatabaseReader->querySecurityOk($DELETE, 1)) {
            $query = $thisDatabaseWriter->sanitizeQuery($DELETE);
            $results = $thisDatabaseWriter->update($DELETE, $dataDelete);
        }
    }
    if(isset($_POST["txtTrailName"]))
        $trailName = htmlentities($_POST["txtTrailName"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["intTotalDistance"]))
        $totalDistance = htmlentities($_POST["intTotalDistance"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["HOURS"]))
        $HOUR = htmlentities($_POST["HOURS"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["MIN"]))
        $MIN = htmlentities($_POST["MIN"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["SEC"]))
        $SEC = htmlentities($_POST["SEC"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["txtVerticalRise"]))
        $verticalRise = htmlentities($_POST["txtVerticalRise"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["txtRating"]))
        $rating = htmlentities($_POST["txtRating"], ENT_QUOTES, "UTF-8");
    if(isset($_POST["HOURS"]) && isset($_POST["MIN"]) && isset($_POST["SEC"]))
        $hikingTime = $HOUR . ':' . $MIN . ':' . $SEC;


    $Checked = [];
    $CheckedName = [];


    if(isset($_POST["chkEasy"])){
        $easy = htmlentities($_POST["chkEasy"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $easy);
        array_push($CheckedName, "Easy");
    }
    if(isset($_POST["chkDogsAllowed"])){
        $dogs = htmlentities($_POST["chkDogsAllowed"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $dogs);
        array_push($CheckedName, "Dogs Allowed");
    }
    if(isset($_POST["chkHiking"])){
        $hikable = htmlentities($_POST["chkHiking"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $hikable);
        array_push($CheckedName, "Hiking");
    }
    if(isset($_POST["chkHard"])){
        $hard = htmlentities($_POST["chkHard"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $hard);
        array_push($CheckedName, "Hard");
    }
    if(isset($_POST["chkSkiing"])){
        $skiing = htmlentities($_POST["chkSkiing"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $skiing);
        array_push($CheckedName, "Skiing");
    }
    if(isset($_POST["chkViews"])){
        $views = htmlentities($_POST["chkViews"], ENT_QUOTES, "UTF-8");
        array_push($Checked, $views);
        array_push($CheckedName, "Views");
    }


    print PHP_EOL . '<!-- SECTION: 2c Validation -->' . PHP_EOL;
    if ($trailName == "") {
        $errorMsg[] = "Please enter your first name";
        $trailNameERROR = true;
    } elseif (!verifyAlphaNum($trailName)) {
        $errorMsg[] = "Your first name appears to have extra character.";
        $trailNameERROR = true;
    }
    if ($totalDistance == "") {
        $errorMsg[] = "Enter Distance";
        $totalDistanceERROR = true;
    }
    if($HOUR < 0 || $MIN < 0 || $SEC < 0){
        $errorMsg[] = "Cannot Enter a Negative Number";
    }
    if(strlen($hikingTime) > 9 || strlen($hikingTime)<8){
        $errorMsg[] = "Format must be HH:MM:SS";
        $hikingTimeERROR = true;
    }elseif($hikingTime == "00:00:00"){
        $errorMsg[] = "Must be more than no time";
    }
    if($verticalRise == ""){
        $errorMsg[] = "Enter the trail's Height";
        $verticalRiseERROR = true;
    }
    if($rating == ""){
        $errorMsg[] = "Enter the trail's Difficulty ";
        $ratingERROR = true;
    }
    print PHP_EOL . '<!-- SECTION: 2d Process Form - Passed Validation -->' . PHP_EOL;
    if (!$errorMsg) {
        if (DEBUG) {
            print "<p>Form is valid</p>";
        }
        print PHP_EOL . '<!-- SECTION: 2e Save Data -->' . PHP_EOL;
        $dataEntered = false;
        $dataEntered2 = false;
        $data = array();
        $data2 = array();
        $data[] = $trailName;
        $data[] = $totalDistance;
        $data[] = $hikingTime;
        $data[] = $verticalRise;
        $data[] = $rating;
        try {
            $thisDatabaseWriter->db->beginTransaction();
            if ($update) {
                $query = 'UPDATE tblTrails SET ';
            } else {
                $query = 'INSERT INTO tblTrails SET ';
            }
            $query .= 'fldTrailName = ?, ';
            $query .= 'fldTotalDistance = ?, ';
            $query .= 'fldHikingTime = ?, ';
            $query .= 'fldVerticalRise = ?, ';
            $query .= 'fldRating = ? ';
            //This is where we will be inserting into table TrailTags


            $query2 = 'INSERT INTO tblTrailTags SET ';
            $query2 .= 'pfkTrailsId = ?, ';
            $query2 .= 'pfkTag = ? ';


            if (DEBUG) {
                $thisDatabaseWriter->TestSecurityQuery($query, 0);
                $thisDatabaseWriter->TestSecurityQuery($query2, 0);
                print_r($data);
                print_r($data2);
            }


            if ($update) {
                $query .= 'WHERE pmkTrailsId = ?';
                $data[] = $pmkTrailsId;
                if ($thisDatabaseReader->querySecurityOk($query, 1)) {
                    $query = $thisDatabaseWriter->sanitizeQuery($query);
                    $results = $thisDatabaseWriter->update($query, $data);
                }
                if ($thisDatabaseReader->querySecurityOk($query2, 0)) {
                    $data2[0] = $pmkTrailsId;
                    foreach($CheckedName as $name){
                        $data2[1]  = $name;
                        $query2 = $thisDatabaseWriter->sanitizeQuery($query2);
                        $results = $thisDatabaseWriter->insert($query2, $data2);
                    }
                }
            } else {
                if ($thisDatabaseWriter->querySecurityOk($query, 0)) {
                    $query = $thisDatabaseWriter->sanitizeQuery($query);
                    $results = $thisDatabaseWriter->insert($query, $data);
                    $primaryKey = $thisDatabaseWriter->lastInsert();
                }
//
                if ($thisDatabaseWriter->querySecurityOk($query2, 0)) {
                    $data2[0] = $primaryKey;
                    foreach($CheckedName as $name){
                        $data2[1]  = $name;
                        $query2 = $thisDatabaseWriter->sanitizeQuery($query2);
                        $results = $thisDatabaseWriter->insert($query2, $data2);
                    }
                }
            }
            if (DEBUG) {
                print "<p>pmk= " . $primaryKey;
            }
            // all sql statements are done so lets commit to our changes
            $dataEntered = $thisDatabaseWriter->db->commit();
            $dataEntered2 = $thisDatabaseWriter->db->commit();
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
                       value="<?php print $pmkTrailsId; ?>"
                >

                <fieldset class = "contact">
                    <p>
                        <label class="required">Trail Name</label>
                        <input autofocus
                            <?php if ($trailNameERROR)
                                print 'class="mistake"'; ?>
                               id="txtTrailName"
                               name="txtTrailName"
                               onfocus="this.select()"
                               placeholder="Enter Trail name"
                               tabindex="100"
                               type="text"
                               value="<?php print $trailName; ?>"
                        >
                    </p>

                    <p>
                        <label class="required">Distance (Miles)</label>
                        <input
                            <?php if ($totalDistanceERROR)
                                print 'class="mistake"'; ?>
                                id="intTotalDistance"
                                name="intTotalDistance"
                                onfocus="this.select()"
                                tabindex="110"
                                type="number"
                                step="0.01"
                                min = "0"
                                value="<?php print $totalDistance; ?>"
                        >
                    </p>

                    <p>
                        <label class="required">Hiking Duration (hh:mm:ss)</label>
                        <input
                            <?php if ($hikingTimeERROR)
                                print 'class="mistake"'; ?>
                                id="hr"
                                name="HOURS"
                                min="0"
                                max="24"
                                type="number"

                                value="<?php print $HOURS; ?>"        >
                        <label>:</label>
                        <input
                            <?php if ($hikingTimeERROR)
                                print 'class="mistake"'; ?>
                                id="min"
                                name="MIN"
                                min="0"
                                max="60"
                                type="number"
                                value= "<?php print $MIN; ?>"
                        >
                        <label>:</label>
                        <input
                            <?php if ($hikingTimeERROR)
                                print 'class="mistake"'; ?>
                                id="sec"
                                name="SEC"
                                min="0"
                                max="60"
                                type="number"
                                value="<?php print $SEC; ?>" >


                    </p>



                    <p>
                        <label class="required">Height (ft)</label>
                        <input
                            <?php if ($verticalRiseERROR)
                                print 'class="mistake"'; ?>
                                id="txtVerticalRise"
                                name="txtVerticalRise"
                                tabindex="120"
                                type="number"
                                min = "0"
                                max = "5000"
                                value="<?php print $verticalRise; ?>"
                        >
                    </p>



                    <p>
                        <label class="required">Difficulty: </label>

                        <input
                            <?php if ($ratingERROR)
                                print 'class="mistake"';
                            ?>
                                id="easy"
                                name="txtRating"
                                type="radio"
                            <?php
                            //if rating is Easy then make it sticky
                            if($rating == "Easy")
                                print "checked";
                            ?>
                                value="Easy"
                        > <span>Easy</span>

                        <input
                            <?php if ($ratingERROR)
                                print 'class="mistake"'; ?>
                                id="moderate"
                                name="txtRating"
                                type="radio"
                            <?php
                            //if rating is Easy then make it sticky
                            if($rating == "Moderate")
                                print "checked";
                            ?>
                                value="Moderate"
                        > <span>Moderate</span>

                        <input
                            <?php if ($ratingERROR)
                                print 'class="mistake"'; ?>
                                id="moderately-strenuous"
                                name="txtRating"
                                type="radio"
                            <?php
                            //if rating is Easy then make it sticky
                            if($rating == "Moderately Strenuous")
                                print "checked";
                            ?>
                                value="Moderately Strenuous"
                        > <span>Moderately Strenuous</span>

                        <input
                            <?php if ($ratingERROR)
                                print 'class="mistake"'; ?>
                                id="strenuous"
                                name="txtRating"
                                type="radio"
                            <?php
                            //if rating is Easy then make it sticky
                            if($rating == "Strenuous")
                                print "checked";
                            ?>
                                value="Strenuous"
                        > <span>Strenuous</span>

                    </p>

                    <label>Pick Applicable Tags: </label>

                    <?php
                    $i = 0;
                    foreach($tagSet as $tag){
                        print "\t" . '<label for="chk' . str_replace(" ", "", $tag["pmkTag"]) . '"><input type="checkbox" ';
                        print ' id="chk' . str_replace(" ", "", $tag["pmkTag"]) . '" ';
                        print ' name="chk' . str_replace(" ", "", $tag["pmkTag"]) . '" ';
                        if ($tag["fldBinary"]) {
                            print ' checked ';
                        }
                        // the value is the index number of the hobby array
                        print 'value="' . $i++ . '">' . $tag["pmkTag"];
                        print '</label>';
                    }
                    ?>


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