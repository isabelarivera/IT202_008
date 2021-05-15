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
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $score = $_POST["score"];
    $reason = $_POST["reason"];
    $user = get_user_id();
    $db = getDB();

    if (isset($id)) {
        $stmt = $db->prepare("UPDATE PointsHistory set points_change=:score, reason =:reason where id=:id");
        $r = $stmt->execute([
            ":score" => $score,
            ":reason" => $reason,
            ":id" => $id
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM PointsHistory where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

    <h2>Edit Scores</h2>
    <form method="POST">
        <label>Change Score</label>
        <input name="score" placeholder="score" value="<?php echo $result["points_change"]; ?>"/>
        <label>reason</label>
        <input name="reason" value="<?php echo $result["reason"];?>" />
        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");
