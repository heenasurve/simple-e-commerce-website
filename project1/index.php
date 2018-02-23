
<?php
    require_once "includes/page_start.inc.php";
    require_once PATH_INC . "header.inc.php";

    session_start();
    $db = DB::getInstance();

    //Add an item product to cart
    if(isset($_POST['submit'])){
        //get product to add to cart..
        $product_id= $_POST['submit'];

        $get_product_query = "SELECT * FROM products WHERE ProductID=$product_id";
        $get_product_query_error = $db->do_query($get_product_query,array(),array());
        $product_info = $db->fetch_all_array();
        $session_id = $_COOKIE['PHPSESSID'];

        //..add it to cart - check if it's a discounted product
        $add_to_cart_query = "INSERT INTO cart(ProductID, SessionID, Name, Description, Quantity, Price) VALUES (?,?,?,?,?,?)";
        if(intval($product_info[0]['Sale Price'])==0) {
            $add_to_cart_data = array(intval($product_id), $session_id, $product_info[0]['Product Name'], $product_info[0]['Description'], 1, doubleval($product_info[0]['Price']));
        }
        else{
            $add_to_cart_data = array(intval($product_id), $session_id, $product_info[0]['Product Name'], $product_info[0]['Description'], 1, doubleval($product_info[0]['Sale Price']));
        }
            $product_add_error = $db->do_query($add_to_cart_query, $add_to_cart_data, array("i","s","s","s","i","d"));

        //..update products table - reduce quantity by 1
        $update_products_query = "UPDATE products SET Quantity=? WHERE ProductID=$product_id";
        $update_products_data = array(intval($product_info[0]['Quantity']-1));
        $update_products_query_error = $db->do_query($update_products_query,$update_products_data,array("i"));
        }

    //(re)render product list
    $query = "SELECT * FROM products";
    $db->do_query( $query, array(), array() );
    $productList = $db->fetch_all_array();

    Product::separateProductLists($productList);


/*

$desired_quantity = 0;
if(isset($_POST['desired_quantity'])){
$desired_quantity = intval($_POST['desired_quantity']);
}
$cart_price = $desired_quantity * intval($product_info[0]['Price']);
$add_to_cart_data = array(intval($product_id),$product_info[0]['Product Name'],$product_info[0]['Description'],$desired_quantity,$cart_price);
$update_products_data = array(intval($product_info[0]['Quantity']-$desired_quantity));
*/

?>



