<?php
include 'top.php';
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//       
print  PHP_EOL . '<!-- SECTION: 1 Initialize variables -->' . PHP_EOL;       
// These variables are used in both sections 2 and 3, otherwise we would
// declare them in the section we needed them



print  PHP_EOL . '<!-- SECTION: 1a. debugging setup -->' . PHP_EOL;
// We print out the post array so that we can see our form is working.
// Normally i wrap this in a debug statement but for now i want to always
// display it. when you first come to the form it is empty. when you submit the
// form it displays the contents of the post array.
// if ($debug){ 
    print '<p>Post Array:</p><pre>';
    print_r($_POST);
    print '</pre>';
   
    
// }

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
print PHP_EOL . '<!-- SECTION: 1b form variables -->' . PHP_EOL;
//
// Initialize variables one for each form element
// in the order they appear on the form

$currentHiker = "Mark";

$hikeQuery = "SELECT pmkHikersId, fldFirstName, fldLastName FROM tblHikers";

$mountainQuery = "SELECT pmkTrailsId, fldTrailName FROM tblTrails";
if ($thisDatabaseReader->querySecurityOk($hikeQuery, 0)) {
    $hikeQuery = $thisDatabaseReader->sanitizeQuery($hikeQuery);
    $hikers = $thisDatabaseReader->select($hikeQuery, '');
}
$date = "";

if ($thisDatabaseReader->querySecurityOk($mountainQuery, 0)) {
                 $mountainQuery = $thisDatabaseReader->sanitizeQuery($mountainQuery);           
                 $mountains = $thisDatabaseReader->select($mountainQuery, '');        
}


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
print PHP_EOL . '<!-- SECTION: 1c form error flags -->' . PHP_EOL;
//
// Initialize Error Flags one for each form element we validate
// in the order they appear on the form
$hikerERROR = false;
$dateERROR = false;
$trailERROR = false;

////%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
print PHP_EOL . '<!-- SECTION: 1d misc variables -->' . PHP_EOL;
//
// create array to hold error messages filled (if any) in 2d displayed in 3c.
$errorMsg = array();
$mailed = false;
$dataEntered = false; 
 
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
print PHP_EOL . '<!-- SECTION: 2 Process for when the form is submitted -->' . PHP_EOL;
//
if (isset($_POST["btnSubmit"])) {

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    print PHP_EOL . '<!-- SECTION: 2a Security -->' . PHP_EOL;
    
    // the url for this form
    $thisURL = DOMAIN . PHP_SELF;
    
    if (!securityCheck($thisURL)) {
        $msg = '<p>Sorry you cannot access this page.</p>';
        $msg.= '<p>Security breach detected and reported.</p>';
        die($msg);
    }

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    print PHP_EOL . '<!-- SECTION: 2b Sanitize (clean) data  -->' . PHP_EOL;
    // remove any potential JavaScript or html code from users input on the
    // form. Note it is best to follow the same order as declared in section 1c.
   if(isset($_POST["selectedHiker"]))
        $xHiker = htmlentities($_POST["selectedHiker"], ENT_QUOTES, "UTF-8");
    
   if(isset($_POST["txtDate"]))
        $date = htmlentities($_POST["txtDate"], ENT_QUOTES, "UTF-8"); 
    
    if(isset($_POST["Trails"]))
        $trailClicked = htmlentities($_POST["Trails"], ENT_QUOTES, "UTF-8");
    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    print PHP_EOL . '<!-- SECTION: 2c Validation -->' . PHP_EOL;
    //
    // Validation section. Check each value for possible errors, empty or
    // not what we expect. You will need an IF block for each element you will
    // check (see above section 1c and 1d). The if blocks should also be in the
    // order that the elements appear on your form so that the error messages
    // will be in the order they appear. errorMsg will be displayed on the form
    // see section 3b. The error flag ($emailERROR) will be used in section 3c.
    if($date == ""){
        $errorMsg[] = "Please Enter the Date";
        $dateError = true;
    }
    elseif (!validateDate($date)) {
        $errorMsg[] = "Invalid Date Entry";
        $dateError = true;
    }
    if($_answer = ""){
         $errorMsg[] = "Please Select a Trail";
         $trailError = true;
    }
    
  
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    print PHP_EOL . '<!-- SECTION: 2d Process Form - Passed Validation -->' . PHP_EOL;
    //
    // Process for when the form passes validation (the errorMsg array is empty)
    //    
    if (!$errorMsg) {
        if (DEBUG)
                print '<p>Form is valid</p>';
    
        print PHP_EOL . '<!-- SECTION: 2e Save Data -->' . PHP_EOL;
        
        $dataEntered = false;
        $dataRecord = array();
      
         $dataRecord[] = $xHiker;
        $dataRecord[] = $trailClicked;
        $dataRecord[] = $date;
        
        
        try{
            $thisDatabaseWriter->db->beginTransaction();
            
            $query = 'INSERT INTO tblHikersTrails SET '
                . 'fnkHikersId = ?, '
                . 'fnkTrailsId = ?, '
                . 'fldDateHiked = ?';
            if(DEBUG){
                 $thisDatabaseWriter->TestSecurityQuery($query, 0);
                print_r($dataRecord);
            }
        
             if ($thisDatabaseWriter->querySecurityOk($query, 0)) {
                $query = $thisDatabaseWriter->sanitizeQuery($query);
                
                $results = $thisDatabaseWriter->insert($query, $dataRecord);
                $primaryKey = $thisDatabaseWriter->lastInsert();

                if (DEBUG) {
                    print "<p>pmk= " . $primaryKey;
                }
            }


             $dataEntered = $thisDatabaseWriter->db->commit();
                if (DEBUG)
                print "<p>transaction complete ";
                 
            
   
            
        } catch (PDOExecption $e) {
            $thisDatabase->db->rollback();
            if (DEBUG)
                print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accepting your data please contact us directly.";
        }
       
    
     
        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        //
        print PHP_EOL . '<!-- SECTION: 2f Create message -->' . PHP_EOL;
        //
        // build a message to display on the screen in section 3a and to mail
        // to the person filling out the form (section 2g).
        
        
        

    } // end form is valid     

}   // ends if form was submitted.



//#############################################################################
//
print PHP_EOL . '<!-- SECTION 3 Display Form -->' . PHP_EOL;
//
?>       
<main>     
    <article>
<?php
    //####################################
    //
    print PHP_EOL . '<!-- SECTION 3a  -->' . PHP_EOL;
    // 
    // If its the first time coming to the form or there are errors we are going
    // to display the form.
    
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { // closing of if marked with: end body submit
        print '<h2>Thank you for providing your information.</h2>';
    

    } else {       
     print '<h2>Add Your Hike</h2>';
     print '<p class="form-heading">Compete with local hikers, and take a hike!</p>';
     
        //####################################
        //
        print PHP_EOL . '<!-- SECTION 3b Error Messages -->' . PHP_EOL;
        //
        // display any error messages before we print out the form
   
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

        //####################################
        //
        print PHP_EOL . '<!-- SECTION 3c html Form -->' . PHP_EOL;
        //
        /* Display the HTML form. note that the action is to this same page. $phpSelf
            is defined in top.php
            NOTE the line:
            value="<?php print $email; ?>
            this makes the form sticky by displaying either the initial default value (line ??)
            or the value they typed in (line ??)
            NOTE this line:
            <?php if($emailERROR) print 'class="mistake"'; ?>
            this prints out a css class so that we can highlight the background etc. to
            make it stand out that a mistake happened here.
       */
?>    

        


<form action = "<?php print PHP_SELF; ?>"
          id = "frmRegister"
          method = "post">

      <fieldset>
        <h2>List of Hikers</h2>

        <label for="lstHikers" 
            <?php if($hikerERROR)
            print 'class = "mistake"'; ?>
        >Hiker
            <select id="lstHikers"
                    name="selectedHiker"
                    tabindex = "300">
                
            <?php
             foreach ($hikers as $hiker) {

                print '<option ';
                if ($currentHiker == $hiker["pmkHikersId"])
                    print " selected='selected' ";
                print 'value="' . $hiker["pmkHikersId"] . '">' . $hiker["fldFirstName"] . " " . $hiker["fldLastName"];
                print '</option>';
              }
      ?>
            </select></label>
                      

        </fieldset>
    
                <fieldset class = "contact">
                    <p>
                        <label class="required" for="txtDate">Date</label>  
                        <input 
                               <?php //What type of data type is date? ?>
                                <?php if ($dateERROR) 
                                    print 'class="mistake"'; ?>
                                id="txtDate"
                                name="txtDate"                              
                                tabindex="100"
                                type="date" 
                                value ="<?php print $date; ?>"
                        >                    
                    </p>
                 <script>   
                          var today = new Date();
                    var dd = today.getDate();
                    var mm = today.getMonth()+1; //January is 0!
                    var yyyy = today.getFullYear();
                     if(dd<10){
                            dd='0'+dd
                        } 
                        if(mm<10){
                            mm='0'+mm
                        } 

                    today = yyyy+'-'+mm+'-'+dd;
                    document.getElementById("txtDate").setAttribute("max", today);
                </script>
                       
                </fieldset> <!-- ends contact -->
                
                <fieldset>
        <h2>Chose Mountain</h2>
            <?php
            $x = 0;
             foreach ($mountains as $mountain) {
                 if ($trailERROR)
                     print 'class="mistake"';
                  
                print '<input type = "radio"';
                print 'value="' . $mountain["pmkTrailsId"] . '" name="Trails" >' . $mountain["fldTrailName"];
             
                
                print '<br>';
              }
      ?>
                <script> 
                var allRadios = document.getElementsByName('Trails');
                var booRadio;
                var x = 0;
                for(x = 0; x < allRadios.length; x++){
                  allRadios[x].onclick = function() {
                    if(booRadio == this){
                      this.checked = false;
                      booRadio = null;
                    } else {
                      booRadio = this;
                    }
                  };
                }   
                      
                      </script>
        </fieldset>

            <fieldset class="buttons">
                <legend></legend>
                <input class = "button" id = "btnSubmit" name = "btnSubmit" tabindex = "900" type = "submit" value = "Add" >
      
            </fieldset> <!-- ends buttons -->
</form>     
<?php
    } // ends body submit
?>
    </article>     
</main>     

<?php include 'footer.php'; ?>

</body>     
</html>