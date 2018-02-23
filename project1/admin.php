
<?php

    require_once "includes/page_start.inc.php";
    require_once PATH_INC . "header.inc.php";

    /*allow adding and editing of products only if the admin is logged in
    Redirect to a login page if the admin isn't logged in*/

    if(isset($_COOKIE['PHPSESSID'])) {
        $db = DB::getInstance();
        $file_of_doubts = 'debug.txt';

        //ADD A PRODUCT
        if (isset($_POST['add_submit'])) {
            Product::add_product();
        }

        //populate dropdown with products for editing data
        $get_products_query = "SELECT * FROM products";
        $get_products_query_error = $db->do_query($get_products_query, array(), array());
        $products = $db->fetch_all_array();

        foreach ($products as $product) {
            $product_options[] = $product['Product Name'];
            $product_options[] = $product['Description'];
            $products_to_load[$product['ProductID']] = implode(", ", $product_options);
            unset($product_options);
        }

        //populate edit form on selection of a product
        if (isset($_POST['select_submit'])) {
            $selected_product_id = $_POST['product_select'];

            $get_product_data_query = "SELECT * FROM products WHERE ProductID=?";
            $get_product_data_query_error = $db->do_query($get_product_data_query, array($selected_product_id), array("i"));
            file_put_contents($file_of_doubts, $get_product_data_query_error, FILE_APPEND);

            $product_data = $db->fetch_all_array();

            $pid = $product_data[0]['ProductID'];
            $name = $product_data[0]['Product Name'];
            $description = $product_data[0]['Description'];
            $price = $product_data[0]['Price'];
            $quantity = $product_data[0]['Quantity'];
            $discounted_price = $product_data[0]['Sale Price'];
            //$image = $product_data[0]['Image Name'];

        }

        //UPDATE AN EXISTING PRODUCT
        if (isset($_POST['edit_submit'])) {
            Product::edit_product();
        }

    }else{
        header("Location:login.php");
        exit();
    }

?>
<html>

    <head>
    </head>
    <body>

    <div class="container">
        <div class="panel panel-default">
            <h3><div class="panel-heading list">Edit an existing product</div></h3>
            <div class="panel-body">
                <form name="product_select_form" method="post" action="admin.php">
                    <div class="form-group-row">
                        <div class="col-md-2 col-form-label">
                            <label for="product_select" class="">Select a product </label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="product_select" id="product_select">
                                <?php foreach ($products_to_load as $id=>$option){ ?>
                                    <option value="<?php echo $id ?>"><?php echo $option ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="select_submit" class="btn btn-primary"> Select Book </button>
                        </div>
                    </div>
                </form>
                <br /> <br />
                <form name="product_edit_form" action="admin.php" method="post" enctype="multipart/form-data">

                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="name" class="col-md-12 col-form-label">Name </label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control" id="edit_name" name="edit_name" value="<?= $name ?>" required/><br>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="Description" class="col-md-12 col-form-label"> Description </label>
                        </div>
                        <div class="col col-md-10">
                            <textarea class="form-control" id="edit_description" name="edit_description" required ><?= $description ?></textarea><br>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="price" class="col-md-12 col-form-label"> Price </label>
                        </div>
                        <div class="col col-md-10">
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="edit_price" min="0" max="500" value="<?= $price ?>" required /><br>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="quantity" class="col-md-12 col-form-label"> Quantity</label>
                        </div>
                        <div class="col-md-10">
                            <input type="number" class="form-control" name="edit_quantity" id="edit_quantity" min="1" max="500" value="<?= $quantity ?>" required/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="discounted_price" class="col-md-12 col-form-label" > Discounted Price</label>
                        </div>
                        <div class="col-md-10">
                            <input type="number" step="0.01" class="form-control" name="edit_discounted_price" id="edit_discounted_price" min="0" value="<?= $discounted_price ?>"  />
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2 col-form-label">
                            <label for="image" class="col-md-12 col-form-label" > Upload an Image</label>
                        </div>
                        <div class="col-md-10">
                            <input type="file" class="form-control" name="edit_product_image" id="edit_product_image" required/>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" name="edit_id" id="edit_id" value="<?=$pid ?>"/>
                    <button type="submit" name="edit_submit" class="btn btn-primary"> Submit Changes </button>
                </form>
            </div>
        </div>
    </div>


        <div class="container">
            <div class="panel panel-default">
                <h3><div class="panel-heading list">Add a product</div></h3>
                <div class="panel-body">
                    <form name="product_add_form" action="admin.php" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="name" class="col-md-2 col-form-label">Name </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="name" name="name" required/><br>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="description" class="col-md-12 col-form-label"> Description </label>
                            </div>
                            <div class="col col-md-10">
                            <textarea class="form-control" id="description" name="description"  required ></textarea><br>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="price" class="col-md-12 col-form-label"> Price </label>
                            </div>
                            <div class="col col-md-10">
                                <input type="number" step="0.01" class="form-control" id="price" name="price" min="0" max="500" required /><br>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="quantity" class="col-md-12 col-form-label"> Quantity</label>
                            </div>
                            <div class="col-md-10">
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" max="500" required/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="discounted_price" class="col-md-12 col-form-label"> Discounted Price</label>
                            </div>
                            <div class="col-md-10">
                                <input type="number" step="0.01" class="form-control" name="discounted_price" id="discounted_price" min="0" value="0.0" />
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="col-md-2 col-form-label">
                                <label for="image" class="col-md-12 col-form-label"> Upload an Image</label>
                            </div>
                            <div class="col-md-10">
                                <input type="file" class="form-control" name="product_image" id="product_image" required/>
                            </div>
                        </div>
                        <button type="submit" name="add_submit" class="btn btn-primary"> Submit Product </button>
                        <button type="reset" name="reset_submit" class="btn btn-secondary"> Reset </button>
                    </form>
                </div>
            </div>

    </body>
</html>
