<?php
session_start(); //we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in(){
    return isset($_SESSION["user"]);
}
/*function is_logged_in($redirect = true)
{
    if (safe_get($_SESSION, "user", false)) {
        return true;
    }
    if ($redirect) {
        flash("You must be logged in to access this page", "warning");
        die(header("Location: " . getUrl("login.php")));
    } else {
        return false;
    }
}*/
function has_role($role)
{
    if (is_logged_in(false) && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}
function get_username()
{
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email()
{
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id()
{
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}
function safer_echo($var)
{
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}
//for flash feature
function flash($msg)
{
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    } else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }
}

function getMessages()
{
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash
function getURL($path)
{
    if (substr($path, 0, 1) == "/") {
        return $path;
    }
    //edit just the appended path
    return $_SERVER["CONTEXT_PREFIX"] . "/Spring2021/Project/$path";
}
//Made this second messages to help styling for milestone 2
function getMessages2() {
    if (isset($_SESSION['flash2'])) {
        $flashes2 = $_SESSION['flash2'];
        $_SESSION['flash2'] = array();
        return $flashes2;
    }
    return array();
}

/*** Attempts to safely retrieve a key from an array, otherwise returns the default
 * @param $arr
 * @param $key
 * @param string $default
 * @return mixed|string
 */
function safe_get($arr, $key, $default = "")
{
    if (is_array($arr) && isset($arr[$key])) {
        return $arr[$key];
    }
    return $default;
}

function changePoints($user_id, $points, $reason)
{
    $db = getDB();
    $query = "INSERT INTO PointsHistory (user_id, points_change, reason) VALUES (:uid, :change, :reason)";
    $stmt = $db->prepare($query);
    $r = $stmt->execute([":uid" => $user_id, ":change" => $points, ":reason" => $reason]);
    if ($r) {
        $query = "UPDATE Users set points = IFNULL((SELECT sum(points_change) FROM PointsHistory where user_id = :uid),0) WHERE id = :uid";
        $stmt = $db->prepare($query);
        $r = $stmt->execute([":uid" => $user_id]);

        //refresh session data
        $_SESSION["user"]["points"] = get_points_balance();
        return $r;
    }
    return false;
}
/*** Helper to get seconds between two dates. May only be accurate if $date1 is older than $date2.
 * @param $date1
 * @param null $date2 defaults to NOW
 * @return int
 * @throws Exception
 */
function get_seconds_since_dates($date1, $date2 = NULL)
{
    if (!isset($date2)) {
        $date2 = new DateTime();
    }
    if (!$date1 instanceof DateTime) {
        //poor check for DT conversion, TODO make more robust.
        $date1 = new DateTime($date1);
    }
    return $date2->getTimestamp() - $date1->getTimestamp();
}
/*** Used as part of game validation to prevent cheating
 * @return int
 */
function get_seconds_since_start()
{
    //TODO update this to use get_seconds_since_dates()
    $started = safe_get($_SESSION, "started", false);
    if ($started) {
        try {
            if (is_string($started)) {
                $started = new DateTime($started);
            }
            $now = new DateTime();
            if ($started < $now) {
                return $now->getTimestamp() - $started->getTimestamp();
            }
        } catch (Exception $e) {
            //invalid date
            error_log($e->getMessage());
        }
    }
    return -1;
}

/*** Basis of anti cheating check, still WIP
 * @param $isWin
 * @return bool
 */
function is_valid_game($isWin)
{
    $seconds = get_seconds_since_start();
    error_log("Seconds $seconds");
    $min = 10; //Make sure game has been played a significant amount of time
    if (!$isWin) {
        $min = 5; //hopefully the player survives longer than 5 seconds.
    }
    //error_log("Is win $isWin");
    $max = 3600; //make sure it has been started within 60 mins
    //adjust the above constraints as necessary to reduce some basic cheats
    //a game shouldn't be finished in under a set amount of seconds and
    //a game shouldn't take an hour to complete
    error_log("min $min max $max");
    return ($seconds >= $min && $seconds <= $max);
}

function update_experience($user_id){
    $db = getDB();
    $query = "UPDATE Users set experience = (select (SUM(IFNULL(score, 0)) * 10) FROM scores WHERE user_id = :uid) WHERE user_id = :uid";
     $stmt = $db->prepare($query);
        $r = $stmt->execute([":uid" => $user_id]);
        return $r;
}

function get_points_balance(){
    $uid = get_user_id();
    $db = getDB();
    $query = "SELECT IFNULL(points,0) as `points` from Users where id = :id";
    $stmt = $db->prepare($query);
    $r = $stmt->execute([":id"=>$uid]);
    if($r){
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($stats["points"])){
            return (int)$stats["points"];
        }
    }
    return 0;
}
function paginate($query, $params = [], $per_page = 10) {
    global $page;
    if (isset($_GET["page"])) {
        try {
            $page = (int)$_GET["page"];
        }
        catch (Exception $e) {
            $page = 1;
        }
    }
    else {
        $page = 1;
    }
    $db = getDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    if ($result) {
        $total = (int)$result["total"];
        //flash("total is $total");
    }
    global $total_pages;
    $total_pages = ceil($total / $per_page);
    global $offset;
    $offset = ($page - 1) * $per_page;
}
function get10week(){
$arr = [];
$db = getDB();
$stmt = $db->prepare("SELECT score from scores where created >=DATE_SUB(NOW(), INTERVAL 1 Week) limit 10");

$timeType="Week";
$testtime=strtotime("-1 " . $timeType); // THIS IS WHERE TO CHANGE BY WEEK/MONTH/YEAR
$params = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results = $stmt->execute($params);
$results = $stmt->fetchAll();
    
$stmt2 = $db->prepare("SELECT Users.username FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Week) limit 10");   
$params2 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results2 = $stmt2->execute($params2);
$results2 = $stmt2->fetchAll();
        //flash2(" hope this appears2 " . implode($results2[$a-1]));//THIS IS THE WINNER
$stmt3 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Week) limit 10");   
$params3 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results3 = $stmt3->execute($params2);
$results3 = $stmt3->fetchAll();

$stmt4 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Week) limit 10");   
$params4 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results4 = $stmt4->execute($params4);
$results4 = $stmt4->fetchAll();
    

$hasScores=true;
if (count($results)==0) {
    $hasScores=false;
    echo "There have been no scores set in the past " . $timeType . "</br>";
}
if($hasScores) {
        echo "The Top " . count($results) . " scores of the last " . $timeType . "</br>";
    $i=10-count($results);
    $a=1;
    $w=0;
    do {
        //flash2(" hope this appears2 " . implode($results3[$a-1]));//THIS IS THE WINNER
        //Check profile.php code comments to see why this code is here. Basically its because the scores were being printed twice so this fixes that.
        $numlength = strlen(implode($results[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $finalNum = implode($results[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results2[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $userbro = substr(implode($results2[$a-1]),0,$numlength);// % $modifier;
//$userbro="<a href='profile.php'>$userbro</a>"
//echo '<a href="mycgi?foo=', urlencode($userbro), '">';
        $numlength = strlen(implode($results3[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $pointsbro = implode($results3[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results4[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $idbro = implode($results4[$a-1]) % $modifier;
        $arr[$w]=$a;
        $w++;
        $arr[$w]=$finalNum;
        $w++;
        $arr[$w]=$userbro;
        $w++;
        $arr[$w]=$pointsbro;
        $w++;
        
        //flash2("he $idbro " . get_username() . " ye ");
        if(get_username() == $userbro){
           
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }else{
            $id=  get_user_id();
            //flash2("the id should be " . implode($results4[$a-1]));
            //if(isset($_GET[$idbro])){
            //$id = $_GET[$idbro];
            //    echo "the id is $id" . "</br>";
            //}
            
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='other_profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }
      $a++;//flash("testing, <a href='profile.php'>$email</a>");
      $i++;
    }
    while($i<10);
}
echo "</br>";
echo "</br>";
echo "</br>";
        foreach($results as $r):
        endforeach;
    return $arr;
}








//one of the scoreboard functions for milestone 2
function get10month(){
$arr = [];
$db = getDB();
$stmt = $db->prepare("SELECT score from scores where created >= DATE_SUB(NOW(), INTERVAL 1 Month) limit 10");

$timeType="Month";
$testtime=strtotime("-1 " . $timeType); // THIS IS WHERE TO CHANGE BY WEEK/MONTH/YEAR
$params = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results = $stmt->execute($params);
$results = $stmt->fetchAll();
    
$stmt2 = $db->prepare("SELECT Users.username FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Month) limit 10");   
$params2 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results2 = $stmt2->execute($params2);
$results2 = $stmt2->fetchAll();
        //flash2(" hope this appears2 " . implode($results2[$a-1]));//THIS IS THE WINNER
$stmt3 = $db->prepare("SELECT Users.points FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Month) limit 10");   
$params3 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results3 = $stmt3->execute($params2);
$results3 = $stmt3->fetchAll();
      
$stmt4 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= DATE_SUB(NOW(), INTERVAL 1 Month) limit 10");   
$params4 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results4 = $stmt4->execute($params4);
$results4 = $stmt4->fetchAll();
    
    
$hasScores=true;
if (count($results)==0) {
    $hasScores=false;
    echo "There have been no scores set in the past " . $timeType . "</br>";
}
if($hasScores) {
        echo "The Top " . count($results) . " scores of the last " . $timeType . "</br>";
    $i=10-count($results);
    $a=1;
    $w=0;
    do {
        //flash2(" hope this appears2 " . implode($results3[$a-1]));//THIS IS THE WINNER
        //Check profile.php code comments to see why this code is here. Basically its because the scores were being printed twice so this fixes that.
        $numlength = strlen(implode($results[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $finalNum = implode($results[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results2[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $userbro = substr(implode($results2[$a-1]),0,$numlength);// % $modifier;
        
        $numlength = strlen(implode($results3[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $pointsbro = implode($results3[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results4[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $idbro = implode($results4[$a-1]) % $modifier;
        $arr[$w]=$a;
        $w++;
        $arr[$w]=$finalNum;
        $w++;
        $arr[$w]=$userbro;
        $w++;
        $arr[$w]=$pointsbro;
        $w++;
        
        if(get_username() == $userbro){
           
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }else{
            $id=  get_user_id();
            //flash2("the id should be " . implode($results4[$a-1]));
            //if(isset($_GET[$idbro])){
            //$id = $_GET[$idbro];
            //    echo "the id is $id" . "</br>";
            //}
            
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='other_profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }
      $a++;
      $i++;
    }
    while($i<10);
}
echo "</br>";
    echo "</br>";
    echo "</br>";
        foreach($results as $r):
        endforeach;
}








//the lifetime scoreboard funtion for milestone 2
function get10lifetime(){
$arr = [];
$db = getDB();
$stmt = $db->prepare("SELECT score from scores order by score desc limit 10");
//THIS SHOULD BE LIFETIME NOT YEAR
$timeType="Lifetime";
$testtime=strtotime("-1 Year"); //SINCE GOT RID OF "WHERE" PART IN $STMT IT DOESN'T MATTER WHAT GOES HERE
$params = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results = $stmt->execute($params);
$results = $stmt->fetchAll();
    
$stmt2 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= :timeCon order by scores.score desc limit 10");   
$params2 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results2 = $stmt2->execute($params2);
$results2 = $stmt2->fetchAll();
        //flash2(" hope this appears2 " . implode($results2[$a-1]));//THIS IS THE WINNER
$stmt3 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= :timeCon order by scores.score desc limit 10");   
$params3 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results3 = $stmt3->execute($params2);
$results3 = $stmt3->fetchAll();
    
$stmt4 = $db->prepare("SELECT Users.id FROM Users JOIN scores on Users.id = scores.user_id where scores.created >= :timeCon order by scores.score desc limit 10");   
$params4 = array(":timeCon" => date("Y-m-d h:i:s", $testtime));
$results4 = $stmt4->execute($params4);
$results4 = $stmt4->fetchAll();
    
    
$hasScores=true;
if (count($results)==0) {
    $hasScores=false;
    echo "There have been no scores set in the past " . $timeType . "</br>";
}
if($hasScores) {
        echo "The Top " . count($results) . " scores of the games whole " . $timeType . "</br>";
    $i=10-count($results);
    $a=1;
    $w=0;
    do {
        //Check profile.php code comments to see why this code is here. Basically its because the scores were being printed twice so this fixes that.
        $numlength = strlen(implode($results[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $finalNum = implode($results[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results2[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $userbro = substr(implode($results2[$a-1]),0,$numlength);// % $modifier;
        
        $numlength = strlen(implode($results3[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $pointsbro = implode($results3[$a-1]) % $modifier;
        
        $numlength = strlen(implode($results4[$a-1]))/2; //this gets the number of digits that is supposed to be printed
        $modifier = 10**$numlength;//this is the number that $results will be modified by, it just gets 10^power of $numlength
        $idbro = implode($results4[$a-1]) % $modifier;
        $arr[$w]=$a;
        $w++;
        $arr[$w]=$finalNum;
        $w++;
        $arr[$w]=$userbro;
        $w++;
        $arr[$w]=$pointsbro;
        $w++;
        
        if(get_username() == $userbro){
           
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }else{
            $id=  get_user_id();
            //flash2("the id should be " . implode($results4[$a-1]));
            //if(isset($_GET[$idbro])){
            //$id = $_GET[$idbro];
            //    echo "the id is $id" . "</br>";
            //}
            
            echo "The #" . $a . " top score is " . $finalNum . " scored by user <a href='other_profile.php?id=$idbro'>$userbro</a> who has " . $pointsbro . " profile points" . "</br>";
        }
      $a++;
      $i++;
    }
    while($i<10);
}
echo "</br>";
echo "</br>";
echo "</br>";
foreach($results as $r):
endforeach;
}
?>
