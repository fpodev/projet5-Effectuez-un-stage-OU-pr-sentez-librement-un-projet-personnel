<?php
/*
Author: fpodev (fpodev@gmx.fr)
MaterielModel.php (c) 2020
Desc: Liaison avec la table Materiel de la Bdd.
Created:  2020-04-13T15:12:55.132Z
Modified: !date!
*/
namespace App\model;

use PDO;
use App\Objet\Materiel;
use RuntimeException;
class MaterielModel{

    private $db;
   
    public function __construct(PDO $db)
    {
       $this->db = $db;    }
    
    public function add(Materiel $materiel)
    {
        $q = $this->db->prepare('INSERT INTO Materiel (id_secteur, nom) VALUES (:id_secteur, :nom)');
       
        $q->bindValue(':id_secteur', $materiel->id_secteur(), PDO::PARAM_INT);  
        $q->bindValue(':nom', $materiel->nom(), PDO::PARAM_STR);         

        $q->execute();
    }
    public function delete($id)
    {
        $this->db->exec('DELETE FROM Materiel WHERE id= '.(int)$id);
    }
    public function materielList($id_secteur)
    {
        $q = $this->db->prepare('SELECT * FROM Materiel WHERE id_secteur = :id_secteur');

        $q->bindValue(':id_secteur', $id_secteur, PDO::PARAM_INT);

        $q->execute();

        $q->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'App\Objet\Materiel');
    
        $MaterielList = $q->fetchAll();

        $q->closeCursor();
        
        return $MaterielList;
    }
    public function uniqueMateriel($id)
    {
        $q = $this->db->prepare('SELECT * FROM Materiel WHERE id =:id');

        $q->bindValue(':id', $id, PDO::PARAM_INT);

        $q->execute();   
        
        $q->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'App\Objet\Materiel');

        $materiel = $q->fetch();  

        $q->closeCursor();
                
        return $materiel;
    }
    protected function update(Materiel $materiel)
    {
        $q = $this->db->prepare('UPDATE Materiel SET nom = :nom WHERE id = :id');

        $q->bindValue(':nom', $materiel->nom(), PDO::PARAM_STR);          
        $q->bindValue(':id', $materiel->id(), PDO::PARAM_INT);

        $q->execute();
    }
    public function save(Materiel $materiel)
    {
        if ($materiel->isValid() || $materiel->isValidUpdate())
        {   
            $materiel->isNew() ? $this->add($materiel) : $this->update($materiel);
        }
        else
        {
            throw new RuntimeException('Le lieu doit être valide pour être enregistré');
        }
    } 
}
