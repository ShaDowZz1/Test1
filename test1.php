<DOCTYPE html>
<?php 
$nameError = "";
$surnameError = "";
$IDError = "";
$DOBError = ""; ?>
<html>
    <head>
        <title> Register </title>
        <link rel="stylesheet" type="text/css" href="CSS.css">
    </head>
    <body>

<?php
    

    if(isset($_POST['btnSubmit'])) {
    $inName = "";
    $inSurname = "";
    $inID = "";
    $inDOB = "";
    $testdate = "";
    $explotestdate = " ";
    $compdate = "";
    $exploID = "";
    $valid = True;
    $exists = "";
    $nameError = "";
    $surnameError = "";
    $IDError = "";
    $DOBError = "";
    $pattern_stringonly = "/^\s+|[0-9]+$/";
    $d = "";
    $m = "";
    $y = "";
    $a = "";
    $b = "";
    $c = "";
    
    //validate name
    if (empty($_POST["Name"])) {
    $nameError = "Name is required.";
    $valid = false;
    } else {
        if(preg_match($pattern_stringonly ,$_POST["Name"]) == 0) {
        $inName = $_POST["Name"];
                
        }else {
        $nameError = "Only letters are allowed";
        $valid = false;
        }
    }

        //validate surname
    if (empty($_POST["Surname"])) {
            $surnameError = "Surname is required.";
            $valid = false; 
    }else {
        if(preg_match($pattern_stringonly ,$_POST["Surname"]) == 0) {
            $inSurname = $_POST["Surname"];    
        }else {
            $surnameError = "Only letters are allowed";
            $valid = false;
        }
    }

        //validate ID
    if ((strlen($_POST["IDNum"])<>13) or (!is_numeric($_POST["IDNum"])) or (empty($_POST["IDNum"]))) { //check for isNumeric() and if exists in db
        $IDError = "Your ID number must be 13 characaters long and only contain numbers";
        $valid = false;
    }else {
        $inID = $_POST["IDNum"];
        //check if record already exists
        $manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');
        $filter = [ 'ID' => $inID ];
        $dupIDqry = new MongoDB\Driver\Query($filter);
            
        $res = $manager->executeQuery('UserDB.UserInfo', $dupIDqry);

        $dups = current($res->toArray());

        //checking for existing records with same ID number
        if (!empty($dups)) {
            $IDError = "A record with this ID number already exists";
            $valid = false;
        }
            
    }

        
    //validate DOB
    $inDOB = $_POST["DOB"];
    $inDOB = date("d/m/Y", strtotime($inDOB));  
    $explotestdate = getDayMonthYear($inDOB);
    $restID = substr($inID, 0,6);
    if ($explotestdate == $restID){
        $valid = true;
    }else{
            $IDError = "ID number doesn't match with Date of Birth entered";
            $valid = false;
        }
              
    if ($valid) {
        
        $bulk = new MongoDB\Driver\BulkWrite([]);
        $bulk->insert(['Name'=> $inName, 'Surname' => $inSurname, 'ID' => $inID, 'DOB' => $inDOB]);

        $manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');

            

        try{
                
            $result = $manager->executeBulkWrite('UserDB.UserInfo', $bulk);
            echo "Data stored successfully!";

        }catch(MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                printf("%s (%d): %s\n", $writeConcernError->getMessage(),
                $writeConcernError->getCode(),
                var_export($writeConcernError->getInfo(), true));
            }
        } 
    }
          
        
    }
    function getDayMonthYear(string $DOB){
        $compdate = explode("/",$DOB); //explode testdate to validate
        $d = $compdate[0];
        $m = $compdate[1];
        $y = $compdate[2];
        $a = (string) $d;
        $b= (string) $m;
        $c = (string) $y;
        $explotestdate = $c.$b.$a;

        return $explotestdate;
    }
?>

    <h2> Test 1 </h2>
    
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <fieldset>
                <legend> Register </legend>
                
                <p><span class="error">* required field</span></p>
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="Name" value="<?php if (isset($_POST['Name'])) echo $_POST['Name']; ?>"><span class="error">* <?php echo $nameError;?></span>
            </div> 

            <div class="input-group">
                <label>Surname</label>
                <input type="text" name="Surname" value="<?php if (isset($_POST['Surname'])) echo $_POST['Surname']; ?>"><span class="error">* <?php echo $surnameError;?></span>
            </div>

            <div class="input-group">
                <label>ID Number</label>
                <input type="text" name="IDNum" value="<?php if (isset($_POST['IDNum'])) echo $_POST['IDNum']; ?>"/><span class="error">* <?php echo $IDError;?></span>
            </div>

            <div class="input-group">
                <label>Date of birth</label>
                <input type="date" name="DOB" value="<?php if (isset($_POST['DOB'])) echo date("d/m/Y", strtotime($_POST["DOB"]);?>" min="1922-01-01" max="2200-01-01">
            </div>

            <div class="input-group">
                <button type="submit" name="btnSubmit" class="btnOut">Submit</button>
            </div>

        </form> 
    </body>

</html>
