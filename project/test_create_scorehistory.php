<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(!has_role("Admin")){

    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
    <h2>Create Score table</h2>
    <form method = "POST">
        <label>reason</label>
        <input type = "text" name = "reason" />
        <label>score</label>
        <input type = "number" min = "0" name = "score"/>
        <input type = "submit" name = "save" value = "Create"/>
    </form>

<?php
if (isset ($_POST["save"])){
    $score = $_POST["score"];
    $user = get_user_id();
    $reason = $_POST["reason"];
    $db = getDB();
    $stmt = $db -> prepare("INSERT INTO PointsHistory(user_id, points_change, reason)VALUES(:users,:score,:reason)");
    $r = $stmt -> execute([
       ":users" => $user,
       ":score" => $score,
       ":reason" => "$reason"
    ]);

    if($r)
    {
        flash("Created successfully with id: " .$db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
