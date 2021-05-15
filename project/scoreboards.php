<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<?php

//This was created for milestone 3 to show that these methods work, they get the top 10 scores. The functions are defined in helpers.php

echo " ";
//need something here or it might not see any changes made; 
get10week();

get10month();

get10lifetime();
?>



<?php require(__DIR__ . "/partials/flash2.php");?>
<?php require(__DIR__ . "/partials/flash.php");
