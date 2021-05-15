<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	/*
	$name = $_POST["name"];
	$state = $_POST["state"];
	$br = $_POST["base_rate"];
	$min = $_POST["mod_min"];
	$max = $_POST["mod_max"];
	$nst = date('Y-m-d H:i:s');//calc
	$user = get_user_id();
	$db = getDB();
	*/
	$user = get_user_id();
	$score = $_POST["score"];
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE scores set user_id=:user_id, score=:score where id=:id");
		//$stmt = $db->prepare("INSERT INTO Scores (name, state, base_rate, mod_min, mod_max, next_stage_time, user_id) VALUES(:name, :state, :br, :min,:max,:nst,:user)");
		$r = $stmt->execute([
			":id"=>$id,
			":user_id"=>$user,
			":score"=>$score,
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM scores where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>scores</label>
	<input type="number" min="0" name="score"/>
	<input type="submit" name="save" value="Create"/>
</form>


<?php require(__DIR__ . "/partials/flash.php");
