<?php
$conn = mysqli_connect("localhost", "root", "", "filesharing", 3308);
if(!$conn) {
    die('Connection Failed'. mysqli_connect_error());
}
?>