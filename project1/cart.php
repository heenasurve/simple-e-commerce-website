

<?php

    require_once "includes/page_start.inc.php";
    require_once PATH_INC . "header.inc.php";
    require_once "DB.class.php";
    require_once "lib_project1.php";

    $db = DB::getInstance();

    //empty cart selected
    if(isset($_POST['submit'])){

        //get all items from cart
        $query = "SELECT * FROM cart";
        $db->do_query( $query, array(), array() );
        $cart_items = $db->fetch_all_array();

        //increment prouct quantity by 1 in the products database for each item in the cart
        foreach ($cart_items as $item){
                $id = $item['ProductID'];
                $update_quantity_query = "UPDATE products SET Quantity=Quantity+1 WHERE ProductID=$id";
                $db->do_query($update_quantity_query);
            }

        //remove from cart and redirect user to the home page
        if(count($db->get_affected_rows())>0) {
            $empty_cart_query = "DELETE FROM cart";
            $db->do_query($empty_cart_query, array(), array());
            header("Location:index.php");
            exit();
        }
    }

    //show items for the current user/session only
    $cart_query = "SELECT * FROM cart WHERE SessionID=?";
    $get_cart_items_query_error = $db->do_query( $cart_query, array($_COOKIE['PHPSESSID']), array("s") );
    $cart_items = $db->fetch_all_array();

    //check if there are any items in the cart at all
    if(count($cart_items)>0){
        Product::renderCartItems($cart_items);
    }else{ //zero case
        $zero_items_msg = "<div class='container'><h2 style='color: whitesmoke'>You have not added any items to your cart yet</h2>";
        $zero_items_msg .= "<h3 style='color: whitesmoke'> Go <a href='index.php'>here</a> to add some books !</h3></div>";
        echo $zero_items_msg;

    }



?>