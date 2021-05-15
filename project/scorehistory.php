<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php include(__DIR__ . "/partials/pagination.php");?>

<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php

$db = getDB();

$per_page = 10;
$theID = get_user_id();
//$query = "SELECT count(*) as total FROM Competitions WHERE expires > current_timestamp ORDER BY expires ASC";
$query = "SELECT count(*) as total FROM scores WHERE user_id = $theID ORDER BY created DESC";
paginate($query, [], $per_page);


$stmt = $db->prepare("SELECT scores.*, u.username FROM scores JOIN Users u on scores.user_id = u.id WHERE u.id = :id ORDER BY created DESC LIMIT :offset,:count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", get_user_id(), PDO::PARAM_INT);
$stmt->execute();
//$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<?php if (count($result) > 0): ?>
    <?php foreach ($result as $r): ?>
        <div style=" alignment: center ">
            <div>Score: <?php safer_echo($r["score"]); ?> </div>
        </div>
        <div style=" alignment: center ">
            <div>Owner: <?php safer_echo($r["username"]); ?></div>
        </div>
        <br>
    <?php endforeach; ?>
<?php else: ?>
    <p>No results</p>
<?php endif; ?>


<?php include(__DIR__ . "/partials/pagination.php");?>

<?php require(__DIR__ . "/partials/flash.php");


?>
