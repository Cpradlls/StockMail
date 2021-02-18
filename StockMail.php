<?php

if (!defined('_PS_VERSION_')) { // définition de la version de Prestashop 
    exit;
}

class StockMail extends Module // Objet StockMail qui est une instanciation de l'objet Module
{

    public function __construct() // Méthode qui définie le plan de construction du module
    {
        $this->name = 'StockMail'; // Nom du module
        $this->tab = 'emailing'; // Catégorie du module
        $this->version = '1.0.0'; // Version du module
        $this->author = 'C.Pradlls'; // Auteur du module
        $this->need_instance = 0;

        $this->bootstrap = true; // Activation de Bootstrap pour la mise en page du module dans le BackOffice

        parent::__construct();

        $this->displayName = $this->l('StockMail'); // Nom du module dans le BackOffice
        $this->description = $this->l('StockMail envoie un email automatiquement au gestionnaire du site à chaque fois que le stock d\'un produit est modifié.'); // Description du module qui est affiché dans le BackOffice
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?'); //Confirmation de la désinstallation du module
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_); // Défini pour quelle version, le module est accepté

        if (!Configuration::get('STOCKMAIL')) { // permet de vérifier si la valeur NS_MONMODULE_PAGENAME est configurée ou non.
            $this->warning = $this->l('Aucun nom fourni');
        }
        
    }

 // Méthodes d'installation et de désinstallation
 // Elles font appel aux fonctions “install” et “uninstall” de la classe “Module” pour mettre toutes les configurations 
 // nécessaires à l’enregistrement du module.
    public function install() // Méthode d'installation du module 
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL); // Vérifie si le mode multi-boutique de Prestashop 1.7 est activé.
        }
     
        if (!parent::install() ||
            !$this->registerHook('actionUpdateQuantity') || // Enregistrement du module sur le hook "actionUpdateQuantity"
            !Configuration::updateValue('STOCKMAIL', 'Adresse mail') // Enregistrement du nom du module dans la base de donnée
        ) {
            return false;
        }
     
        return true;
    }

    public function uninstall() // Méthode de désinstallation du module 
    {
        if (!parent::uninstall() ||
        !Configuration::deleteByName('STOCKMAIL') // Suppression de StockMail dans la base de donnée
    ) {
        return false;
    }
 
    return true;
    }


    /*

        Méthode du module qui prend comme nom le hook utilisé en prenant 
        comme paramètre l'attribut $params qui récupère la nouvelle quantité du produit. 
        (envoie email automatique au gestionnaire du site)

    */
    public function hookActionUpdateQuantity($params)
    {
        $newquantity = $params['quantity']; // Définition de l'attribut newQuantity

        if ($newquantity) { //On teste si l'attribut est utilisé lorsque la quantité d'un stock est modifié
         //Envoie d'un mail automatiquement

                Mail::Send(
                    (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                    'contact', // email template file to be use
                    ' Stock de produit', // email subject
                    array(
                        '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address : contact principal
                        '{message}' => 'Bonjour,\n Je vous informe que le stock d\'un produit a été modifié.\n Bonne journée.' // Message du mail
                    ),
                    Configuration::get('PS_SHOP_EMAIL'), // Personne qui va recevoir l'email : là c'est également le contact principal
                    NULL, //receiver name
                    NULL, //from email address
                    NULL  //from name
                );

        }
        
    
    }
}
