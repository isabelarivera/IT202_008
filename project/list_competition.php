<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to waste...spend your points");
    die(header("Location: login.php"));
}
?>
<?php
$db = getDB();

// $stmt = $db->prepare("select fee from Competitions where id = :id && expires > current_timestamp && paid_out = 0 LIMIT 10");
   // $stmt = $db->prepare("SELECT c.* FROM Competitions c WHERE c.expires > current_timestamp AND paid_out = 0 ORDER BY expires ASC LIMIT 10");//Use this one or you can only see what you created


///*
$per_page = 10;
$query = "SELECT count(*) as total FROM competitions WHERE expires > current_timestamp ORDER BY expires ASC";
paginate($query, [], $per_page);


$stmt = $db->prepare("SELECT * FROM competitions WHERE expires > current_timestamp ORDER BY expires ASC LIMIT :offset,:count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<div class="container-fluid">
    <div class="h3">Active Competitions</div>
    <?php if (count($results) > 0) : ?>
        <ul class="list-group">
            <?php foreach ($results as $c) : ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col"><?php safer_echo(safe_get($c, "title", "N/A")); ?></div>
                        <div class="col">Participants: <?php safer_echo(safe_get($c, "participants", 0)); ?> / <?php safer_echo(safe_get($c, "min_participants", 0)); ?></div>
                        <div class="col">Ends: <?php safer_echo(safe_get($c, "expires", "N/A")); ?></div>
                        <div class="col">Reward: <?php safer_echo(safe_get($c, "points", 0)); ?></div>
                        <div class="col">
                            <?php if (safe_get($c, "registered", 0) == 0) : ?>
                                <button id="<?php safer_echo(safe_get($c, 'id', -1)); ?>" class="btn btn-primary" onclick="join(<?php safer_echo(safe_get($c, 'id', -1)); ?>)">Join (<?php $cost = (int)safe_get($c, "entry_fee", 0);
                                                                                                                                                                                    safer_echo($cost ? "Cost: $cost" : "Cost: Free"); ?>)
                                </button>
                            <?php else : ?>
                                <button class="btn btn-secondary" disabled="disabled">Already registered
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No competitions available yet, please check back later</p>
    <?php endif; ?>
</div>
<script>
    function join(compId) {
        $.post("api/join_competition.php", {
            compId: compId
        }, (data, status) => {
            console.log(data, status);
            let resp = JSON.parse(data);
            if (resp.status === 200) {
                let button = document.getElementById(compId);
                button.disabled = true;
                button.innerText = "Already Registered";
            }
            alert(resp.message);
        });
    }

</script>
<?php include(__DIR__ . "/partials/pagination.php");?>
<?php require(__DIR__ . "/partials/flash.php");
