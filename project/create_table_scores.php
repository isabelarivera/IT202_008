<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

 <form method="POST"> 

	<label>scores</label>
	<input type="number" min="0" name="score"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	$user = get_user_id();
	$score=$_POST["score"];
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO scores (user_id,score) VALUES(:user,:score)");
	$r = $stmt->execute([
		":user"=>$user,
		":score"=>$score,
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
