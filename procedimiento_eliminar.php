<?php
include 'db.php';
if(!empty($_POST['id'])){
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM procedimientos WHERE id=$id");
}
header("Location: procedimientos.php");