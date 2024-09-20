<?php
session_start();
require_once("./config/connect.php");

if(isset($_POST['delete_product']))
{
    $product_id = mysqli_real_escape_string($conn, $_POST['delete_product']);

    $query = "DELETE FROM products WHERE product_id='$product_id'";
    $query_run = mysqli_query($conn, $query);


    if($query_run)
    {
        $_SESSION['message'] = "products Deleted Successfully";
        header("Location: admin.php");
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "products Not Deleted";
        header("Location: admin.php");
        exit(0);
    }
}

if(isset($_POST['update_products'])) {
    $id = mysqli_real_escape_string($conn, $_POST['products_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $discount = mysqli_real_escape_string($conn, $_POST['discount']);

    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $_SESSION['message'] = "Sorry, there was an error uploading the image.";
                header("Location: admin.php");
                exit(0);
            }
        } else {
            $_SESSION['message'] = "File is not an image.";
            header("Location: admin.php");
            exit(0);
        }
    }

    if ($image_url) {
        $query = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock', discount='$discount', image_url='$image_url' WHERE product_id='$id'";
    } else {
        $query = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock', discount='$discount' WHERE product_id='$id'";
    }

    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Product Updated Successfully";
        header("Location: admin.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Product Not Updated";
        header("Location: admin.php");
        exit(0);
    }
}
