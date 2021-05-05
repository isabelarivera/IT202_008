<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>


<?php 

if(isset($_POST["save"])){

    $id  = get_user_id();
    $score = $_POST["score"];
    $user = get_user_id();
    $nst = date('Y-m-d H:i:s');
    $db = getDB();
    if(isset($id)){
        $stmt = $db -> prepare("UPDATE Scores set score = :score, created = :nst where id = :id");
        $r = $stmt -> execute([
            ":score" => $score,
            ":nst" => $nst,
            ":id" => $id
        ]);

        if($r)
        {
            flash("updated sucessfully with id: " . $id);

        }
        else {
            $e = $stmt -> errorInfo();
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

	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Scores where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">

     <label>Score </label>
    <input type = "number" min = "0" name = "score" value = "<?php echo $resul["score"]; ?>"/>
    <input type="submit" name="save" value="Create" />

</form>
<?php require(__DIR__ . "/partials/flash.php");
