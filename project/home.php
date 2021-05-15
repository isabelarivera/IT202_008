<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
    <p>Welcome to the arcade, <?php echo $email; ?></p>
<?php
get10week(); 
get10month(); 
get10lifetime();

?>

<li><a href="pong.html">PlayGame</a></li>
    
<?php require(__DIR__ . "/partials/flash2.php");?>
<?php require(__DIR__ . "/partials/flash.php");
