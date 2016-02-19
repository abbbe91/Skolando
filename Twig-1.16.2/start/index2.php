<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
       // error_reporting(E_ALL);

       // ini_set('display_errors',1);
        //inkluderar
        include_once '../controllers/Controller.php';
        //Formatering för att avskilja metoderna
        $queryArray = explode('/', $_SERVER['QUERY_STRING']);
        $cont = new Controller();
       
        $cont->$queryArray[0]($queryArray[1]);
        //$cont->visaForstaSida();
        ?>
    </body>
</html>

