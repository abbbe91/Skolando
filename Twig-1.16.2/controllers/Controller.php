<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Controller
 *
 * @author h14jonre
 */
require_once '../lib/Twig/Autoloader.php';
require_once '../models/Model.php';
session_start();

class Controller {

    private $twig;
    private $loader;
    private $modell;
    private $cart;
    private $unikArtikel;
    private $template;

    function __construct() {
        Twig_Autoloader::register();
        $this->loader = new Twig_Loader_Filesystem('../templates/');
        $this->twig = new Twig_Environment($this->loader);        
        $this->modell = new Model();
        $this->cartArray = array();
        $this->template = $this->twig->loadTemplate('Skolando.twig');
        
    }

    //Visa alla herrprodukter
    public function getAllHerr() {
        //hämtar in data via model
        $skorna = $this->modell->getAllHerr();
//bestämmer vilken vy som ska visas
        $template = $this->twig->loadTemplate('SkolandoHerr.twig');
//hämtar och sätter vilket data som ska visas
        $template->display(array('skor' => $skorna));
    }
    
    public function visaForstaSida(){
        
        $template = $this->twig->loadTemplate('Skolando.twig');
        $template->display(array());
    }

    //Visa alla barnprodukter
    public function getAllBarn() {

        $skorna = $this->modell->getAllBarn();

        $template = $this->twig->loadTemplate('SkolandoBarn.twig');

        $template->display(array('skor' => $skorna));
    }

    //Visa alla damprodukter
    public function getAllDam() {

        $skorna = $this->modell->getAllDam();

        $template = $this->twig->loadTemplate('SkolandoDam.twig');

        $template->display(array('skor' => $skorna));
    }

    //Hitta enskild produkt
    public function getSkoByArtnr($artikelNummer) {

        $unikProdukt = $this->modell->getSkoByArtnr($artikelNummer);
        //laddar vyn som ska visa data om skor
        $template = $this->twig->loadTemplate('ProduktSida.twig');
        //sätter data till variabeln skor som sedan är åtkomlig i vyn via detta namn
        $template->display(array('skor' => $unikProdukt));
    }

    public function addToCart($artikelNummer) {
        //$vagnarray = $this->modell->getSkoByArtnr($artikelNummer);
        //var_dump($vagnarray);

        if ($_SESSION['cart']) {
            $this->cart = $_SESSION['cart'];

            if (!array_key_exists($artikelNummer, $this->cart)) {
//arraykey kollar om det inte finns ett matchande artikelnummer som keyvärde.
                //finns det så läggs en vara till i arrayen.
                $vagnarray = $this->modell->getSkoByArtnr($artikelNummer);

                $this->cart [$artikelNummer] = array($vagnarray[0], 1);
                //säger att cart som innehåller sessionen med artikelnummerarrayen ska vara indexpositionen och innehålla En [1] vara
                //cart innehåller sessionen som innehåller artikelnummerarrayen som är lika med vagnarrayen med varan på plats [0] med [1] vara.
                $_SESSION['cart'] = $this->cart;
            } else {
                //annars om det finns en matchning ökar antal med 1 för varje klick.
                $this->cart [$artikelNummer][1] ++;
                $_SESSION['cart'] = $this->cart;
            }
        } else {
            $_SESSION['cart'] = $this->cart;
            $vagnarray = $this->modell->getSkoByArtnr($artikelNummer);

            $this->cart[$artikelNummer] = array($vagnarray[0], 1);

            $_SESSION['cart'] = $this->cart;
        }

        $this->showCart();
    }

    public function showCart() {

        if ($_SESSION['cart']) {
            $vagnarray = $_SESSION['cart'];


            $this->template = $this->twig->loadTemplate('Varukorg.twig');

            $this->template->display(array('cart' => $this->unikArtikel,
                'vagnarray' => $vagnarray,
                'attBetala' => $this->toPay()));
        } else {
            $this->template = $this->twig->loadTemplate('ErrorPage.twig');
            $this->template->display(array('unikaMarken' => $this->unikArtikel,
                'felmeddelande' => 'Tomt i kundvagen'));
        }
    }

    public function removeVaraFromCart($artikelNummer) {

        if ($_SESSION['cart']) {
            $this->cart = $_SESSION['cart'];
            //om artikelnummer finns så ta bort ett från antal
            if (array_key_exists($artikelNummer, $this->cart)) {
                $this->cart[$artikelNummer][1] --;
                /* om det finns en matchning med artikelnumret i cart
                 * så minskas produkten med 1 om den finns
                 */
            }
            //om noll antal ta bort hela "varan" från cartarrayen
            if ($this->cart[$artikelNummer][1] <= 0) {
                unset($this->cart[$artikelNummer]);
                /*
                 * om artikelnumret är mindre eller är lika med 0 så tas allt bort.
                 */
            }
            $_SESSION['cart'] = $this->cart;
            $this->showCart();
        }
    }

    public function removeProdukttypFromCart($artikelNummer) {

        if ($_SESSION['cart']) {
            $this->cart = $_SESSION['cart'];

            //ta bort från kundvagn och visa kundvagns sidan
            if (array_key_exists($artikelNummer, $this->cart)) {

                unset($this->cart[$artikelNummer]);
            }
            $_SESSION['cart'] = $this->cart;
            $this->showCart();
        }
    }

    public function toPay() {
        $toPay = 0;
        foreach ($_SESSION['cart']as $sko) {

            $toPay+=$sko[0]['Pris'] * $sko[1];
        }
        return $toPay;
    }

    public function showAdmin() {
        if ($_SESSION['loggedin'] == TRUE) {
            $skorna = $this->modell->getAllShoes();
            $template = $this->twig->loadTemplate('Admin.twig');
            $template->display(array('allShoes' => $skorna));
        } else {
            $_SESSION['loggedin'] = FALSE;
            $template = $this->twig->loadTemplate('LoginForm.twig');
            $template->display(array('artskor' => $this->unikArtikel));
        }
    }

    //LÃ¤gg till sko
    public function addShoe() {

        if (count($this->validation()) == 0) {
            $this->modell->addShoe();
            $shoeArray = $this->modell->getAllShoes();
            $template = $this->twig->loadTemplate('Admin.twig');
            $template->display(array('allShoes' => $shoeArray));
        } else {
            $template = $this->twig->loadTemplate('AddForm.twig');
            $template->display(array('allShoes' => $shoeArray, 'errormessages' => $this->validation(), 'postatdata' => $_POST));
        }
    }

    //Metod fÃ¶r att se lÃ¤ggtill-twigen


    private function validation() {
        $errormsg = array();
        //loopar igenom arrayen med postat data
        foreach ($_POST as $key => $value) {

            if ($value == NULL) {
                $errormsg[$key] = 'Kan inte vara tomt, fyll i fältet';
            } else {
                switch ($key) {
                    case 'Artikelnummer':
                        if ($this->validateArtnr($value) != NULL) {
                            $errormsg[$key] = $this->validateArtnr($value);
                        }
                        break;
                        case 'Antal':
                        if ($this->validateArtnr($value) != NULL) {
                            $errormsg[$key] = $this->validateAntal($value);
                        }
                        break;
                        case 'Storlek':
                        if ($this->validateArtnr($value) != NULL) {
                            $errormsg[$key] = $this->validateStorlek($value);
                        }
                        break;
                        case 'Pris':
                        if ($this->validateArtnr($value) != NULL) {
                            $errormsg[$key] = $this->validatePris($value);
                        }
                        break;
                    default:
                }
            }
        }
        return $errormsg;
    }

    private function validateArtnr($artikelNummer) {
        
        foreach ($_POST as $key => $value) {
            if (!is_numeric($artikelNummer))
                $error = 'Artikelnummer måste innehålla siffror.';
        }
        return $error;
    }
    private function validateAntal($antal) {
        
        foreach ($_POST as $key => $value) {
            if (!is_numeric($antal))
                $error = 'Antal måste innehålla siffror.';
        }
        return $error;
    }
    private function validateStorlek($storlek) {
        
        foreach ($_POST as $key => $value) {
            if (!is_numeric($storlek))
                $error = 'Storlek måste innehålla siffror.';
        }
        return $error;
    }
    private function validatePris($pris) {
        
        foreach ($_POST as $key => $value) {
            if (!is_numeric($pris))
                $error = 'Pris måste innehålla siffror.';
        }
        return $error;
    }

    public function deleteShoe($artikelNummer) {
        $this->modell->deleteShoe($artikelNummer);
        //$shoeArray = $this->modell->getAllShoes();
        //$this->template->display(array('allShoes' => $shoeArray));

        $skorna = $this->modell->getAllShoes();
        $template = $this->twig->loadTemplate('Admin.twig');
        $template->display(array('allShoes' => $skorna));
    }

    public function showUpdateForm($artikelNummer) {

        //$artSko = $this->modell->getAllShoes();
        $artSko = $this->modell->getSkoByArtnr($artikelNummer);
        $template = $this->twig->loadTemplate('UpdateForm.twig');
        $template->display(array('artskor' => $artSko));
    }

    public function updateShoe($artikelNummer) {

       
        $this->modell->updateShoe($artikelNummer);
            $artSko = $this->modell->getAllShoes();
            $template = $this->twig->loadTemplate('Admin.twig');
            $template->display(array('allShoes' => $artSko));
    }

    
    
    public function showAddForm() {

        $this->template = $this->twig->loadTemplate('AddForm.twig');
        $this->template->display(array('unikArtikel' => $this->unikArtikel));
    }

    public function login() {
        if (strip_tags($_POST['Användarnamn']) == 'admin' && strip_tags($_POST['Lösenord']) == 'Skolando') {
            $_SESSION['loggedin'] = TRUE;
            $this->showAdmin();
        } else {
            $_SESSION['loggedin'] = FALSE;
            $template = $this->twig->loadTemplate('LoginForm.twig');
            $template->display(array('artskor' => $this->unikArtikel));
        }
    }

}
