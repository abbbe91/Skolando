<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Model
 *
 * @author h14jonre
 */
class Model {
    
    private $pdocon;
    private $dsn, $username, $password;
    
    function __construct() {
        $this->dsn = 'mysql:host=utb-mysql.du.se;dbname=db06';
        $this->username = 'db06';
        $this->password = 'Oy9CkDSJ';
    }

    private function openConnection(){
        try{
            if($this->pdocon == NULL){
                $this->pdocon = new PDO($this->dsn, $this->username, $this->password);
                
            }
        } catch (PDOException $ex) {
            $this->pdocon = NULL;
            throw new Exception('DATABASFEL');
        }
    }

    
    public function getAllHerr(){
        try{
            //Öppnar connection med metod
            $this->openConnection();
            //2. Preparerar en sql fråga
            $pdoStatement = $this->pdocon->prepare('CALL h14alben_getHerrSkor("herr")');
            
            //$pdoStatement = $pdocon->prepare('SELECT * FROM h14alben_skolando where Kategori ="Herr"');
            //3. Exekverar frågan
            $pdoStatement->execute();
            //4.hämtar resultat till en array
            $shoes = $pdoStatement->fetchAll();
            //5. Stänger uppkopplingen
            $this->pdocon= NULL;
            //returnerar arrayen med resultatet
            return $shoes;
            
        } catch (PDOException $ex) {
            $this->pdocon = NULL;
            throw new Exception('DATABASFEL');
        }
    }
    
        public function getAllBarn(){
        try{
            //Öppnar uppkopplingen
            $this->openConnection();
            //2. Preparerar en sql fråga
            $pdoStatement = $this->pdocon->prepare('CALL getBarnSkor("barn")');
            //3. Exekverar frågan
            $pdoStatement->execute();
            //4.hämtar resultat till en array
            $shoes = $pdoStatement->fetchAll();
            //5. Stänger uppkopplingen
            $this->pdocon= NULL;
            //returnerar arrayen med resultatet
            return $shoes;
            
        } catch (Exception $ex) {

        }
    }
    
        public function getAllDam(){
        try{
             $this->openConnection();
            //2. Preparerar en sql fråga
            $pdoStatement = $this->pdocon->prepare('CALL h14alben_getDamSkor("dam")');
            //3. Exekverar frågan
            $pdoStatement->execute();
            //4.hämtar resultat till en array
            $shoes = $pdoStatement->fetchAll();
            //5. Stänger uppkopplingen
            $this->pdocon= NULL;
            //returnerar arrayen med resultatet
            return $shoes;
            
        } catch (Exception $ex) {

        }
    }
    
    public function getSkoByArtnr($artikelNummer){
            $this->openConnection();
            $pdoStatement= $this->pdocon->prepare('SELECT * FROM h14alben_skolando WHERE Artikelnummer=:artnr');
            $pdoStatement->bindParam(':artnr',$artikelNummer);
            $pdoStatement->execute();
              
            $shoe=$pdoStatement->fetchAll(); //returnerar en array, vad den returnerar på sin indexposition innehåller den första produkten.
            $this->pdocon=NULL;
            return $shoe;
    }//end 
    
    public function getAllShoes() {
        try {
            $this->openConnection();
            //2. Preparerar en sql fråga
            $pdoStatement = $this->pdocon->prepare('SELECT * FROM h14alben_skolando');
            //3. Exekverar frågan
            $pdoStatement->execute();
            //4.hämtar resultat till en array
            $shoes = $pdoStatement->fetchAll();
            //5. Stänger uppkopplingen
            $this->pdocon = NULL;
            //returnerar arrayen med resultatet
            return $shoes;
        } catch (Exception $ex) {
            
        }
    }
    
    
    public function addShoe() {
        try {


            $this->openConnection();
            //lÃ¤gger till en sko, anvÃ¤nder namngivna platshÃ¥llare som artnr
            $pdoStatement = $this->pdocon->prepare('INSERT INTO h14alben_skolando'
                    . '(Kategori,Namn,Artikelnummer,Antal,Storlek,Pris,Sokvag)'
                    . 'VALUES(:kategori,:namn,:artikelnummer,:antal,:storlek,:pris,:sokvag)');

            $pdoStatement->bindParam(':kategori', filter_var($_POST['Kategori'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':namn', filter_var($_POST['Namn'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':artikelnummer', filter_var($_POST['Artikelnummer'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':antal', filter_var($_POST['Antal'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':storlek', filter_var($_POST['Storlek'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':pris', filter_var($_POST['Pris'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':sokvag', filter_var($_POST['Sokvag'], FILTER_SANITIZE_STRING));

            //exekverar frÃ¥gan 
            $pdoStatement->execute();
            $this->pdocon = NULL;
        } catch (Exception $ex) {
            $this->pdocon = NULL;
            throw new Exception('Databasfel-Gick inte att lägga till en sko');
        }
    }

    public function deleteShoe($artikelNummer) {
        try {
            $this->openConnection();
            $pdoStatement = $this->pdocon->prepare('DELETE FROM h14alben_skolando WHERE Artikelnummer=:artikelNummer');
            $pdoStatement->bindParam(':artikelNummer', $artikelNummer);
            $pdoStatement->execute();
            $this->pdocon = NULL;
        } catch (Exception $ex) {
            $this->pdocon = NULL;
            throw new Exception('Databasfel-Gick inte att ta bort en sko');
        }
    }

    public function updateShoe($artikelNummer) {
        try {

            $this->openConnection();

            $pdoStatement = $this->pdocon->prepare('UPDATE h14alben_skolando SET Kategori=:kategori, Namn=:namn, Antal=:antal ,Storlek=:storlek , Pris=:pris, Sokvag=:sokvag'
                    . ' WHERE Artikelnummer=:artikelNummer');

            $pdoStatement->bindParam(':kategori', filter_var($_POST['Kategori'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':namn', filter_var($_POST['Namn'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':antal', filter_var($_POST['Antal'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':storlek', filter_var($_POST['Storlek'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':pris', filter_var($_POST['Pris'], FILTER_SANITIZE_STRING));
            $pdoStatement->bindParam(':sokvag', filter_var($_POST['Sokvag'], FILTER_SANITIZE_STRING));
            
             $pdoStatement->bindParam(':artikelNummer',$artikelNummer);
             
            $pdoStatement->execute();

            $this->pdocon = NULL;
            
        } catch (PDOException $pdoexp) {

            $this->pdocon = NULL;
            throw new Exception('Databasfel - Gick inte att uppdatera skon');
        }
    }
}
//$modell=new Model();
//var_dump($modell->getSkoByArtnr(1));