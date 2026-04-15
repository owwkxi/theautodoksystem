<?php
if(!isset($_SESSION['usrID'])){
    header("Location:index.php");
exit();
}