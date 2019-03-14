<?php
session_start();
define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'MiiSiNdb');

$result;
$sSQL;
$obj;

function getDB()
{
	$connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    $connected = true;
    // Check connection
    if (mysqli_connect_error($connection)){//->connect_error) {
        $connected = false;
        
        die("Connection failed: " . mysqli_connect_error($connection));
    }
    else{
        $connection->query("SET NAMES 'utf8'");
    }
    
    return $connection;
}


function runQuery($db, $query) {
    //Runs a query $db is the connection to the database and $query is the query to be ran
    
    //Checks the 
    if(isset($db) and isset($query)){
        
        $result =  mysqli_query($db, $query);
        
    }
    
    return $result;
	// takes a reference to the DB and a query and returns the results of running the query on the database
}

function checkLogin($db, $User, $Pass){
    //This function will return true if the user was found and will return 
    //false if the user was not found
    //$User is the username being passed
    //$Pass is the password being passed
    //$db is the database connection
    
    $bChecked = false;
    
    //Check to see if there is a database connection
    if (isset($db)){
        
        //make sure the username and password is not empty
        if(!empty($User) and !empty($Pass)){
            
            $sSQL = "Select * from tbUserInfo where Password = '$Pass' and Username = '$User'";
            
            $result = runQuery($db, $sSQL);
            
            $obj = mysqli_fetch_object($result);
            
            //Check to see if the object is empty
            //If it is empty then the user was not found in the database 
            if (empty($obj)){
                
                
                $bChecked = false;
            }
            else{
                
                $bChecked = true;
                
            }
            
        }
        
    }
    
    //return whether or not the user and password was found
    return $bChecked;
}

function getMiiInfo($db){
  //will get set all of the MiiInfo into $_SESSION
    $sArray;
    if (isset($db)){
        
        if (!empty($_SESSION['user']['username'])){

            $sSQL = "Select MiiID from tbUserInfo where Username = '" . $_SESSION['user']['username'] . "'";
                
            $result = runQuery($db, $sSQL);
            
            $obj = mysqli_fetch_object($result);
            
            $sSQL = "Select * from tbMii where MiiID  =' $obj->MiiID'";
            
            $result = runQuery($db, $sSQL);
            
            $obj = mysqli_fetch_object($result);
            
            $_SESSION['user']['Head'] = $obj->HeadID;
            
            $_SESSION['user']['Shirt'] = $obj->ShirtID;
            
            $_SESSION['user']['Pants'] = $obj->PantsID;
            
            $_SESSION['user']['Skin'] = $obj->SkinID;
                        
            //$sArray = getPants($db,$obj->PantsID);
            
            getPantsSession($db,$obj->PantsID);
                
            getShirtSession($db,$obj->ShirtID);
                
            getSkinSession($db,$obj->SkinID);
                
            getHeadSession($db,$obj->HeadID);
        }
    }
    
}

function getHeadSession($db, $HeadID){
    //will set $_SESSION['user']['HeadSVG']
    $sPantsArray = false;
    
    $sSQL = "Select * from tbHead where HeadID = '" . $HeadID . "'";
    
    if (isset($db) and isset($HeadID)){
        
        $result = runQuery($db, $sSQL);

        $obj = mysqli_fetch_object($result);

        $sPantsArray= true;
        
        $_SESSION['user']['HeadSVG'] = $obj->HeadSVG;
        
    }
    return $sPantsArray;
    
}

function getSkinSession($db, $SkinID){
    //will set $_SESSION['user']['SkinColor'] and $_SESSION['user']['SkinSVG']
    $sPantsArray = false;
    
    $sSQL = "Select * from tbSkin where SkinID = '" . $SkinID . "'";
    
    if (isset($db) and isset($SkinID)){
        
        $result = runQuery($db, $sSQL);

        $obj = mysqli_fetch_object($result);

        $sPantsArray= true;
        
        $_SESSION['user']['SkinColor'] = $obj->SkinColor;

        $_SESSION['user']['SkinSVG'] = $obj->SkinSVG;
        
    }
    return $sPantsArray;
    
}

function getShirtSession($db, $ShirtID){
    //will set $_SESSION['user']['ShirtColor'] and $_SESSION['user']['ShirtSVG'].
    $sPantsArray = false;
    
    $sSQL = "Select * from tbShirt where ShirtID = '" . $ShirtID . "'";
    
    if (isset($db) and isset($ShirtID)){
        
        $result = runQuery($db, $sSQL);

        $obj = mysqli_fetch_object($result);

        $sPantsArray= true;
        
        $_SESSION['user']['ShirtColor'] = $obj->ShirtColor;

        $_SESSION['user']['ShirtSVG'] = $obj->ShirtSVG;
        
    }
    return $sPantsArray;
    
}

function getPantsSession($db, $PantsID){
    //will set $_SESSION['user']['PantsColor'] and $_SESSION['user']['PantsSVG'].
    $sPantsArray = false;
    
    $sSQL = "Select * from tbPants where PantsID = '" . $PantsID . "'";
    
    if (isset($db) and isset($PantsID)){
        
        $result = runQuery($db, $sSQL);

        $obj = mysqli_fetch_object($result);

        $sPantsArray= true;
        
        $_SESSION['user']['PantsColor'] = $obj->PantsColor;

        $_SESSION['user']['PantsSVG'] = $obj->PantsSVG;
        
    }
    return $sPantsArray;
    
}

function closeDB($db){
  //will close the connection of the database.
    
    if (isset($db)){
        
        mysqli_close($db);
        
    }
}


?>
