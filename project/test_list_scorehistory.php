<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare ("SELECT scores.score,PointsHistory.id, PointsHistory.points_change, PointsHistory.reason, Users.username from PointsHistory JOIN Users on PointsHistory.user_id = Users.id LEFT JOIN scores on scores.user_id = PointsHistory.user_id WHERE PointsHistory.user_id like :q LIMIT 10 ");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
}
?>
<h2>List Scores</h2>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div>
    <?php if (count($results) > 0): ?>
        <div>
            <?php foreach ($results as $r): ?>
                <div>
                    <div>
                        <div>Scores:</div>
                        <div><?php safer_echo($r["score"]); ?></div>
                    </div>
                    <div>
                        <div>Reason:</div>
                        <div><?php safer_echo($r["reason"]); ?></div>
                    </div>
                    <div>
                        <div>Owner:</div>
                        <div><?php safer_echo($r["username"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_scorehistory.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_scorehistory.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
