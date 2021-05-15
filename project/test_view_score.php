<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$id = get_user_id();

// fetching
$result = [];
if(isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Users.email,user_id,score,Users.username FROM scores as scores JOIN Users on scores.user_id = Users.id where scores.id = :id ");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)) : ?>

   <div>

        <div style="alignment: center ">

           <div>user id: <?php safer_echo($result["user_id"]); ?> </div>


        </div>

        <div>
            <div style = "alignment:center">
                <p>stats</p>
                <div>Score: <?php safer_echo($result["score"]); ?></div>
                <div>Email: <?php safer_echo($result["email"]); ?></div>
                <div>Username: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
