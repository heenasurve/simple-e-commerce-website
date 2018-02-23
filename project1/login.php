<?php
require_once "includes/page_start.inc.php";
require_once PATH_INC . "header.inc.php";

session_start();

//redirect to admin page if already logged in
if(isset($_SESSION['loggedIn']) && !empty($_SESSION['loggedIn']))
{
    header('Location:admin.php');
    exit();
}
else
{
    //get credentials for admin and test entered credetials against that data
    if(isset($_POST['submit'])){
        $db = DB::getInstance();
        $get_user_query = "SELECT * FROM user";
        $get_user_query_error = $db->do_query($get_user_query,array(),array());
        $user_credentials = $db->fetch_all_array();

        //credentials match
        if ($_POST['username'] == $user_credentials[0]['username'] && $_POST['password'] ==$user_credentials[0]['password']){

            $_SESSION['loggedIn'] = true;
            header('Location:admin.php');
            exit();
        }
        else{
            echo "<script type='text/javascript'> 
                        function login_error(){ 
                            setTimeout(function(){swal(
                                {title: 'Error logging in',
                                text: 'Please check your credentials',
                                type: 'error'})
                                },500);
                            };
                        login_error();
                        </script>";
        }
    }else{
            session_unset();
        }
}


?>
<!doctype HTML>
<html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <div class="container">
            <div class="panel panel-default">
                <h3><div class="panel-heading list">Login</div></h3>
                <div class="panel-body">
                    <form name="admin_login_form" action="login.php" method="post">

                        <div class="form-group row">
                            <label for="username" class="col-md-2 col-form-label username">Username</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="username" name="username" value="<?= $username ?>" required/><br>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-md-2 col-form-label password"> Password </label>
                            <div class="col col-md-10">
                                <input type="password" class="form-control" id="password" name="password" required /><?= $password ?><br>
                            </div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>