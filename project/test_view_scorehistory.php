<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
//fetching
$id = $_GET["id"];
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Users.username, PointsHistory.points_change, scores.score FROM PointsHistory JOIN Users on PointsHistory.user_id = Users.id LEFT JOIN scores on scores.user_id = PointsHistory.user_id where PointsHistory.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo var_export($result, true);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h3>View Scores</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div>
            <div>
                <p>Stats</p>
                <div>username <?php safer_echo($result["username"]); ?></div>
                <div> points changed<?php safer_echo($result["points_change"]); ?></div>
                <div> Score<?php safer_echo($result["score"]); ?></div>
            </div>

    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
