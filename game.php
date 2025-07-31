<?php
session_start();
if (!isset($_SESSION['player'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
?>
<html lang="eng">
    <head>

    </head>
    <body>
        <div>hello</div>
        <script type="text/javascript" src="gameboard.js"></script>
    </body>
</html>