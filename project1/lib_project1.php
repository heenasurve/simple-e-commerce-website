
<?php

    require_once "includes/page_start.inc.php";
    require_once PATH_INC . "header.inc.php";

    class Product
    {

        /*separateProductLists() takes a product list from the product table
        and splits it into 2 - discounted and normally priced + discounted products */
        public static function separateProductLists($productList)
        {
            $catalog = array();
            $products_on_sale = array();
            foreach ($productList as $product) {
                if ($product['Sale Price'] != 0) {
                    array_push($products_on_sale, $product);
                }
                array_push($catalog, $product);
            }

            self::renderProductList($products_on_sale, "On Sale");
            self::renderProductList($catalog, "Catalog");
        }

        /*Renders a list of products. Takes a product list and the type of products in the list
        The type could be "On Sale" or "Catalog" indicating discounted and all products*/
        public static function renderProductList($productList, $list_type)
        {
            $display = "<div class='container Products'>
                <div class='panel panel-default'>
                    <h3><div class='panel-heading list'>{$list_type}</div></h3>
                        <ul class=\"list-group\">";
            foreach ($productList as $product) {
                $id = $product['ProductID'];
                $display .= "<form action='index.php' method='post'>";

                $display .= "<li class=\"list-group-item product-container\">";
                $display .= "<div class='img-container'><div class='hold-img'><img class='product_image' src=" . $product['Image Name'] . " /></div></div>";
                $display .= "<div class='content-container'><span class='product_name'>" . $product['Product Name'] . " </span><br/><span class='product_desc'>" . $product['Description'] . "</span><br/>";
                if ($product['Sale Price'] != 0) {
                    $display .= "<span class='original_price'>$" . $product['Price'] . "</span>" . " <span class='discounted_price'>$" . $product['Sale Price'] . "</span>";
                } else {
                    $display .= "<span class='sale_price'>$" . $product['Price'] . "</span>";
                }
                /* $display.= "<br/> Quantity : <input type='text' name='desired_quantity' id='desired_quantity' class='desired_quantity' />";*/
                $display .= "<span class='quantity'>   (" . $product['Quantity'] . " Left in stock ! )</span></div><br/>";
                $display .= "<button class='btn btn-primary add_to_cart' type='submit' name='submit' value=$id>Add to cart</button>";


                $display .= "</li>";

            }
            $display .= "</ul></div>";
            $display .= "</form></div>";
            echo $display;
        }

        /*Renders items in the cart for the current user -  a different sessionID corresponds to a different user
        Also evaluates the total price of all items in the cart*/
        public static function renderCartItems($cartItems)
        {
            $total_price = 0.0;
            $display = "<div class='container CartItems'>
                <div class='panel panel-default'>
                    <h3><div class='panel-heading list'>Cart</div></h3>
                        <ul class=\"list-group\">";
            foreach ($cartItems as $product) {
                $display .= "<form action='cart.php' method='post'>";
                $display .= "<li class=\"list-group-item\">";
                $display .= "<div><span class='product_name'>" . $product['Name'] . " </span><br/><span class='product_desc'>" . $product['Description'] . "</span><br/>";
                $display .= "<span class='cart_price'>$" . $product['Price'] . "</span>";
                $display .= "</li>";
                $total_price += $product['Price'];
            }
            $display .= "</ul></div>";
            $display .= "<div><span class='cart_total'>Total : $" . $total_price . "</span></div>";
            $display .= "<button class='btn btn-primary' type='submit' name='submit'>Empty cart</button>";
            $display .= "</form></div></div>";
            echo $display;
        }

        /*Returns the current number of products that are on sale*/
        public static function getNumberOfDiscountedProducts()
        {
            $db = DB::getInstance();
            $discounted_product_count_query = "SELECT * FROM products WHERE `Sale Price`!=0";
            $discounted_product_count_query_error = $db->do_query($discounted_product_count_query, array(), array());
            $rows = $db->fetch_all_array();
            return count($rows);
        }

        /*Uploads an product image to the server*/
        public static function upload_product_image()
        {
            $target_dir = "assets/";
            $target_file = $target_dir . basename($_FILES["product_image"]["name"]);

            $imageType = pathinfo($target_file, PATHINFO_EXTENSION);
            if ($imageType != "jpg" && $imageType != "png" && $imageType != "jpeg") {
                echo "<script type='text/javascript'> 
                            function image_error(){ 
                                setTimeout(function(){swal(
                                    {title: 'Incorrect image type!',
                                    text: 'Sorry, only JPG, JPEG, PNG  are allowed !',
                                    type: 'error'})
                                    },500);
                                };
                            image_error();
                        </script>";
            } else {
                //echo $imageFileType;
                move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file);
            }
            return $target_dir . basename($_FILES["product_image"]["name"]);
        }

        /*Adds a new product to the product table
        Takes into consideration the constraints on the number of discounted products*/
        public static function add_product()
        {
            $db = DB::getInstance();
            $discounted_items = Product::getNumberOfDiscountedProducts();

            //adding a product ? - get data
            $product_name = $_POST['name'];
            $product_desc = $_POST['description'];
            $product_price = $_POST['price'];
            $product_quantity = $_POST['quantity'];
            $product_discounted_price = $_POST['discounted_price'];
            if (!empty($_FILES['product_image'])) {
                $product_image = Product::upload_product_image();
            }
            //adding a discounted product
            if (isset($_POST['discounted_price']) && doubleval($_POST['discounted_price']) != 0) {
                //max number of discounted items reached - prevent admin from adding this item
                if ($discounted_items == 5) {
                    echo "<script type='text/javascript'> 
                            function discount_error(){ 
                                setTimeout(function(){swal(
                                    {title: 'Too many products on sale!',
                                    text: 'You can only have 5 books on sale !',
                                    type: 'error'})
                                    },500);
                                };
                            discount_error();
                        </script>";
                } else { //lesser than max discounted items - check if prices are valid
                    if (doubleval($_POST['discounted_price']) < doubleval($_POST['price'])) {
                        //add the product to the products table
                        $add_to_products_query = "INSERT INTO products(`Product Name`, Description, Price, Quantity, `Image Name`, `Sale Price`) VALUES (?,?,?,?,?,?)";
                        $add_to_products_data = array($product_name, $product_desc, intval($product_price), intval($product_quantity), $product_image, intval($product_discounted_price));
                        $product_add_error = $db->do_query($add_to_products_query, $add_to_products_data, array("s", "s", "i", "i", "s", "i"));
                    } else { //invalid price
                        echo "<script type='text/javascript'> 
                        function price_error(){ 
                            setTimeout(function(){swal(
                                {title: 'Uh Oh !',
                                text: 'The sale price must be lesser than the products price !',
                                type: 'error'})
                                },500);
                            };
                        price_error();
                        </script>";
                    }
                }
            } else {
                //not a discounted product

                $add_to_products_query = "INSERT INTO products(`Product Name`, Description, Price, Quantity, `Image Name`, `Sale Price`) VALUES (?,?,?,?,?,?)";
                $add_to_products_data = array($product_name, $product_desc, doubleval($product_price), intval($product_quantity), $product_image, 0.0);
                $product_add_error = $db->do_query($add_to_products_query, $add_to_products_data, array("s", "s", "d", "i", "s", "d"));

                if ($db->get_affected_rows() > 0) {
                    echo "<script type='text/javascript'> 
                        function book_added(){ 
                            setTimeout(function(){swal(
                                {title: 'Success !',
                                text: 'The book was successfully added to the system !',
                                type: 'success'})
                                },500);
                            };
                        book_added();
                        </script>";
                }
            }
        }

        /*Edits a product already in the product table
        Doesn't allow an update if the constraint on the number of
        discounted items is violated in the process of updating a product*/
        public static function edit_product()
        {
            $db = DB::getInstance();
            $discounted_items = Product::getNumberOfDiscountedProducts();

            //check if discounted items constraint violated
            $product_id = $_POST['edit_id'];
            $product_name = $_POST['edit_name'];
            $product_desc = $_POST['edit_description'];
            $product_price = $_POST['edit_price'];
            $product_quantity = $_POST['edit_quantity'];
            $product_discounted_price = $_POST['edit_discounted_price'];
            if (!empty($_FILES['edit_product_image'])) {
                $product_image = Product::upload_product_image();
            }

            $product_get_query = "SELECT `Sale Price` FROM products WHERE ProductID=?";
            $product_get_query_error = $db->do_query($product_get_query, array($product_id), array("i"));
            $rows = $db->fetch_all_array();
            $sale_price = $rows[0]['Sale Price'];
           


            //number shouldn't drop below 3 or go above 5
            if (($discounted_items == 5 && $product_discounted_price > 0 && $sale_price == 0) ||
                ($discounted_items == 3 && $product_discounted_price == 0 && $sale_price > 0)
            ) {
                //file_put_contents($file_of_doubts,"in here",FILE_APPEND);
                echo "<script type='text/javascript'> 
                        function discount_products_error(){ 
                            setTimeout(function(){swal(
                                {title: 'Check number of products on sale!',
                                text: 'You can only have between 3 and 5 products on sale !',
                                type: 'error'})
                                },500);
                            };
                        discount_products_error();
                        </script>";
            } else {
                $edit_product_query = "UPDATE products SET `Description`=?,`Product Name`= ?, Price = ?, Quantity = ?,`Sale Price` = ?,`Image Name` =? WHERE ProductID=?";
                $edit_product_query_error = $db->do_query($edit_product_query, array($product_desc, $product_name, doubleval($product_price), $product_quantity, doubleval($product_discounted_price), $product_image, intval($product_id)), array("s", "s", "d", "i", "d", "s", "i"));
                //file_put_contents($file_of_doubts, $edit_product_query_error, FILE_APPEND);
                echo "<script type='text/javascript'> 
                        function book_edited(){ 
                            setTimeout(function(){swal(
                                {title: 'Success !',
                                text: 'The item was successfully updated !',
                                type: 'success'})
                                },500);
                            };
                        book_edited();
                        </script>";
            }
        }
    }
?>