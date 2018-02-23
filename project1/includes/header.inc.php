<?php

?>

<html>
    <head>
        <meta charset="utf-8">
        <title>BookHub</title>

        <link rel=”shortcut icon” href="favicon.ico" type=”image/x-icon”>
        <link rel=”icon” href="favicon.ico" type=”image/x-icon”>

        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet">

        <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="style/style.css" rel="stylesheet">
        <link href="libs/sweetalert/sweetalert-master/dist/sweetalert.css" rel="stylesheet" type="text/css" >

        <script src="libs/bootstrap/js/bootstrap.min.js" ></script>
        <script src="libs/sweetalert/sweetalert-master/dist/sweetalert.min.js"></script>

    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="./index.php">BookHub</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="nav_ele"><a href="./index.php">Home</a></li>
                        <li class="nav_ele"><a href="./cart.php">Cart</a></li>
                        <li class="nav_ele"><a href="./admin.php">Admin</a></li>
                        <?php /*if(isset($_SESSION['loggedIn'])) {
                            echo "<li class=\"nav_ele\"><a href=\"./admin.php\">Admin</a></li>";
                            echo "<li class=\"nav_ele_right\"><a href=\"./login.php\">Logout</a></li>";
                        }else{
                            echo "<li class=\"nav_ele_right\"><a href=\"./login.php\">Login</a></li>";
                        } */?>

                    </ul>
                </div>
            </div>
        </nav>
    </body>
</html>
