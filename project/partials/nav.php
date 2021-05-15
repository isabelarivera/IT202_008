<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="static/css/styles.css">
<?php
require_once(__DIR__ . "/../lib/helpers.php");
?>
<nav>
<ul class="nav">
    <li><a href="home.php">Home</a></li>
     <li><a href="scoreboards.php">Scoreboards</a></li>
     <li><a href="pong.php">Game</a></li>
    <?php if (!is_logged_in(false)): ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if(HAS_ROLE("Admin")): ?>
        <li><a href = "create_table_scores.php"> Create Score</a></li>
        <li><a href = "test_list_scores.php"> List Score</a></li>
        <li><a href = "test_create_scorehistory.php"> Create Score History </a></li>
        <li><a href = "test_list_scorehistory.php"> List score History </a> </li>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
            <li><a href="admin_only.php">Admin Page</a></li>
        <?php endif; ?> 
    <?php if (is_logged_in(false)): ?>
	<li><a href="create_competition.php">Create a Competition</a></li>
	<li><a href="list_competition.php">Active Competition</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
        <li><a href="pong.php">PlayGame</a></li>
    <?php endif; ?>
</ul>
 <?php if (is_logged_in(false)) : ?>
            <span class="navbar-text">
                Points: <?php echo get_points_balance(); ?>

            </span>
        <?php endif; ?>
    </div>
</nav>
