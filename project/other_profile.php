<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change



if(isset($_GET["id"])){
$id = $_GET["id"];
//flash("yyyyyyyyyooooooooooooooo $id");
}
else{
$id= get_user_id();
//flash("grouuuuuuuuuuup");
}



$db = getDB();

$stmt = $db->prepare("SELECT status from users WHERE id = :id LIMIT 1");
    $params = array(":id" => $id);
    $r = $stmt->execute($params);
    if($r){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	if($result["status"]=="private"){
	flash("You cannot see the profiles of private accounts.");
	die(header("Location: home.php"));
		//flash("HHHHHHHHHHHHHHHHHHHHH");
		
	}
        //flash("This account is " . $result["status"]);
//flash("This account has " . $profilePoints . " points.");
    }


//get users points and show on profile page
    $stmt = $db->prepare("SELECT points from users WHERE id = :id LIMIT 1");
    $params = array(":id" => $id);
    $r = $stmt->execute($params);
    if($r){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $profilePoints = $result["points"];
flash("This account has " . $profilePoints . " points.");
    }



?>



<?php

//THIS PHP SECTION WAS CREAED FOR MILESTONE 2, THIS IS WHAT SENDS THE SCORE TO THE DATABASE WHEN THE USER IS LOGGED IN.
///*
$stmt = $db->prepare("SELECT * from scores where user_id = :id order by id desc limit 10");
$params = array(":id" => $id);
$results = $stmt->execute($params);
$results = $stmt->fetchAll();
//flash("array length check " . count($results));
/*
//ADD A FOR LOOP HERE TO CREATE THE TOP 10 CHART    USE ECHO OR FLASH   TO CREATE THE CHART
$hasScores=true;
if (count($results)==0) {
    $hasScores=false;
    flash2("You do not have any scores recorded, try playing the game!");
}
if($hasScores) {
        flash2("Your last " . count($results) . " scores");
    $i=10-count($results);
    $a=1;
    do {
        //So $results was printing double, like 27 came out at 2727 and 0 as 00 so I am modding by 10^(number of digits / 2)
        //so when $results is 2727 we do ( $results % $modifier ) where $modifier will be 100 since 27 is a 2 digit number and 10^2=100
        //doing modifier like this will get rid of the extra digits being produced
        //$numlength = strlen((string)$num);
        $numlength = strlen(implode($results[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $finalNum = strlen(implode($results[$a-1])) % $modifier;
        flash2("#" . $a . " most recent score is " . $finalNum);
        //flash("#" . $a . " most recent score is " . implode($results[$i]) . " finalNum " . $finalNum);//%$check);//for some reason the score displayed is being doubled
      $a++;
      $i++;
    }
    while($i<10);
}
//*/


/*$stmt = $db->prepare("SELECT * FROM scores WHERE user_id = :id ORDER BY created DESC LIMIT :offset,:count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", get_user_id(), PDO::PARAM_INT);
$stmt->execute();
//$stmt->execute([":id"=>get_user_id()]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);*/


?>


<html>
    
    <script>
        //var statusState = "Public";
        //document.getElementById("currStatus").innerHTML = "Your profile is currently set to "+statusState; 
    </script>
    
    
    

</html>

<div class="container-fluid">
        <h3>The Last 10 Scores of this account</h3>
        <div class="list-group">
            <?php if (isset($results) && count($results)): ?>
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item" style="background-color: #25E418">
                        <div class="row">
				
                            <div class="col">
                                They scored: 
                                <?php safer_echo($r["score"]); ?>
                            </div>
                            <div class="col">
                                Scored on: 
                                <?php safer_echo($r["created"]); ?>
                            </div>
			    <div class="col">
                                <form method="POST">
				</form>
                            </div>
                             
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item">
                    No scores to show, sorry.
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php require(__DIR__ . "/partials/flash2.php");?>
<?php require(__DIR__ . "/partials/flash.php");?>

