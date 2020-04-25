<?php
namespace App\Objet;

class ExtendsObjet{

   protected $erreurs = [];
    //protected $nom;
    protected $id;

    const NOM_INVALIDE = 1;
    
    public function __construct($valeurs=[])
    {
        if (!empty($valeurs))
        {        
            $this->hydrate($valeurs);
        }
    }    
    public function hydrate($donnees)
    {
        foreach ($donnees as $attribut => $valeur)
        {
            $methode = 'set'.ucfirst($attribut);

            if (is_callable([$this, $methode]))
            {
                $this->$methode($valeur);
            }
        }
    }  
    public function isNew()
    {
        return empty($this->id);
    }
    public function setId($id)
    {
        $this->id = (int)$id;
    }
    public function setNom($nom)
    {
        if(!is_string($nom) || empty($nom))
        {
            $this->erreurs[] = self::NOM_INVALIDE;
        }
        else
        {
            $this->nom = $nom;
        }
    }    
    public function erreurs()
    {
        return $this->erreurs;
    }
    public function id()
    {
        return $this->id;
    }
    public function nom()
    {
        return $this->nom;
    }  
}
