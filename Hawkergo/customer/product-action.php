<?php
session_start();

if (!empty($_GET["action"])) {
    include(__DIR__ . '/../connection/connect.php');

    $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 0;

    switch ($_GET["action"]) {
        case "add":
            if ($productId > 0 && $quantity > 0 && hawkergo_dish_available($db, $productId)) {
                $stmt = $db->prepare("SELECT d_id, title, price, rs_id FROM dishes WHERE d_id = ?");
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $productDetails = $stmt->get_result()->fetch_object();
                $stmt->close();

                if ($productDetails) {
                    $itemArray = array(
                        $productDetails->d_id => array(
                            'title' => $productDetails->title,
                            'd_id' => $productDetails->d_id,
                            'rs_id' => $productDetails->rs_id,
                            'quantity' => $quantity,
                            'price' => $productDetails->price
                        )
                    );

                    if (!empty($_SESSION["cart_item"])) {
                        if (in_array($productDetails->d_id, array_keys($_SESSION["cart_item"]))) {
                            $_SESSION["cart_item"][$productDetails->d_id]["quantity"] += $quantity;
                        } else {
                            $_SESSION["cart_item"] = $_SESSION["cart_item"] + $itemArray;
                        }
                    } else {
                        $_SESSION["cart_item"] = $itemArray;
                    }
                }
            }
            break;

        case "remove":
            if (!empty($_SESSION["cart_item"])) {
                foreach ($_SESSION["cart_item"] as $k => $v) {
                    if ($productId == $v['d_id']) {
                        unset($_SESSION["cart_item"][$k]);
                    }
                }
            }
            break;

        case "empty":
            unset($_SESSION["cart_item"]);
            break;

        case "check":
            header("location:checkout.php");
            break;
    }
}
