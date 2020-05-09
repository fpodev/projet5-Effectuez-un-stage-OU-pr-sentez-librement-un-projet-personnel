<?php
/*
Author: fpodev (fpodev@gmx.fr)
TravauxModel.php (c) 2020
Desc: description
Created:  2020-04-13T15:10:34.230Z
Modified: !date!
*/
namespace App\model;

use PDO;
use App\Objet\Travaux;
use RuntimeException;

class TravauxModel{

    private $db;
   
    public function __construct(PDO $db)
    {
       $this->db = $db;   
    }    
    public function add(Travaux $travaux)
    {
        $q = $this->db->prepare('INSERT INTO Travaux (id_lieu, id_batiment, id_secteur, id_materiel, descriptions,
                                detail, id_demandeur, urgence, date_demande) VALUES (:id_lieu, :id_batiment, :id_secteur,
                                :id_materiel, :descriptions, :detail, :id_demandeur, :urgence, NOW())');
        $q->bindValue(':id_lieu', $travaux->id_lieu(), PDO::PARAM_INT);
        $q->bindValue(':id_batiment', $travaux->id_batiment(), PDO::PARAM_INT);
        $q->bindValue(':id_secteur', $travaux->id_secteur(), PDO::PARAM_INT);
        $q->bindValue(':id_materiel', $travaux->id_materiel(), PDO::PARAM_INT);  
        $q->bindValue(':descriptions', $travaux->descriptions(), PDO::PARAM_STR); 
        $q->bindValue(':detail', $travaux->detail(), PDO::PARAM_STR);  
        $q->bindValue(':id_demandeur', $travaux->id_demandeur(), PDO::PARAM_INT); 
        $q->bindValue(':urgence', $travaux->urgence(), PDO::PARAM_INT);                 

        $q->execute();
    }
    public function countAll()
    {
        $_SESSION['lieuId'] = 'null' || $_SESSION['lieuId'] ;
        
        return $this->db->query('SELECT COUNT(*) FROM Travaux WHERE date_fin IS NULL AND id_lieu ='. $_SESSION["lieuId"].'') ->fetchColumn(); 
    }
    public function countUser($id)
    {
        return $this->db->query('SELECT COUNT(*) FROM Travaux WHERE id_technicien = '.$id.' AND date_fin IS NULL')->fetchColumn(); 
    }
    public function countPlanif()
    {     
        return $this->db->query('SELECT COUNT(date_prevu) FROM Travaux WHERE id_lieu = '.$_SESSION['lieuId'].' AND date_fin IS NULL')->fetchColumn();                         
    }
    public function delete($id)
    {        
        $this->db->exec('DELETE FROM Travaux WHERE id= '.(int)$id);
    }
    public function travauxList($param)
    {
        $q = $this->db->query('SELECT Travaux.id, Travaux.descriptions, Travaux.urgence, Travaux.id_demandeur, Travaux.date_demande, Materiel.nom, Batiment.nom AS "nBatiment"
                               FROM Travaux 
                               INNER JOIN Materiel 
                               ON Travaux.id_materiel = Materiel.id
                               INNER JOIN Batiment
                               ON Travaux.id_batiment = Batiment.id
                               WHERE Travaux.date_prevu IS '.$param.' AND Travaux.id_lieu = '.$_SESSION['lieuId'].' AND date_fin IS NULL');       
            
        $travauxList = $q->fetchAll(PDO::FETCH_ASSOC);
        
        $q->closeCursor();
    
        return $travauxList;
    }
    public function technicienList($id){
        $param = "NULL";
        $q = $this->db->query('SELECT  Travaux.id, descriptions, detail, urgence, date_demande, date_prevu, date_debut, date_fin, externe, Lieu.id AS "nLieu", Lieu.nom AS "lieu", Batiment.nom AS "batiment", Materiel.nom AS "materiel", Secteur.nom AS "secteur", demandeur.email, technicien.id AS techId, technicien.nom AS techNom 
                                FROM Travaux  
                                INNER JOIN Lieu                               
                                ON Travaux.id_lieu = Lieu.id                              
                                INNER JOIN Batiment
                                ON Travaux.id_batiment = Batiment.id
                                INNER JOIN Materiel
                                ON Travaux.id_materiel = Materiel.id
                                INNER JOIN Secteur
                                ON Travaux.id_secteur = Secteur.id 
                                LEFT JOIN User as demandeur  
                                ON Travaux.id_demandeur = demandeur.id  
                                LEFT JOIN User as technicien
                                ON Travaux.id_technicien = technicien.id  
                                WHERE Travaux.id_lieu = '.$_SESSION['lieuId'].' AND technicien.id = '.$id.' AND Travaux.date_fin IS '.$param.'');       
     
        $q->execute();   

        $q->setFetchMode(PDO::FETCH_ASSOC);

        $travaux = $q->fetchAll();  

        $q->closeCursor();

        return $travaux;

}
    public function uniqueTravaux($id)
    {
        $q = $this->db->query('SELECT  Travaux.id, Batiment.id AS idBatiment, Materiel.id AS idMateriel, Secteur.id AS idSecteur, demandeur.id AS idDemandeur, Travaux.id AS nTravaux, descriptions, detail, urgence, date_demande, date_prevu, date_debut, date_fin, externe, Lieu.id AS "nLieu", Lieu.nom AS "lieu", Batiment.nom AS "batiment", Materiel.nom AS "materiel", Secteur.nom AS "secteur", demandeur.email, technicien.id AS techId, technicien.nom AS techNom 
                               FROM Travaux  
                               INNER JOIN Lieu                               
                               ON Travaux.id_lieu = Lieu.id                              
                               INNER JOIN Batiment
                               ON Travaux.id_batiment = Batiment.id
                               INNER JOIN Materiel
                               ON Travaux.id_materiel = Materiel.id
                               INNER JOIN Secteur
                               ON Travaux.id_secteur = Secteur.id 
                               LEFT JOIN User as demandeur  
                               ON Travaux.id_demandeur = demandeur.id  
                               LEFT JOIN User as technicien
                               ON Travaux.id_technicien = technicien.id                                                                                                    
                               WHERE Travaux.id = '.$id.'');                                 
                               
        $q->execute();   
        
        $q->setFetchMode(PDO::FETCH_ASSOC);

        $travaux = $q->fetchAll();  
       
        $q->closeCursor();
           
        return $travaux;       
    }
    protected function update(Travaux $travaux)
    {
        $q = $this->db->prepare('UPDATE Travaux SET id_technicien = :id_technicien, date_prevu = :date_prevu, 
                                date_debut = :date_debut, date_fin = :date_fin, externe = :externe, 
                                descriptions = :descriptions, detail = :detail WHERE id = :id');
        $q->bindValue(':descriptions', $travaux->descriptions(), PDO::PARAM_STR); 
        $q->bindValue(':detail', $travaux->detail(), PDO::PARAM_STR) ; 
        $q->bindValue(':id_technicien', $travaux->id_technicien(), PDO::PARAM_INT);    
        $q->bindValue(':date_prevu', $travaux->date_prevu());  
        $q->bindValue(':date_debut', $travaux->date_debut()); 
        $q->bindValue(':date_fin', $travaux->date_fin(), PDO::PARAM_INT);  
        $q->bindValue(':externe', $travaux->externe(), PDO::PARAM_STR);            
        $q->bindValue(':id', $travaux->id(), PDO::PARAM_INT);

        $q->execute();
    }  
    protected function start(Travaux $travaux)
    {
        $q = $this->db->prepare('UPDATE Travaux SET date_debut = :date_debut
                                WHERE id = :id');         
        $q->bindValue(':date_debut', $travaux->date_debut());                          
        $q->bindValue(':id', $travaux->id(), PDO::PARAM_INT);

        $q->execute(); 
    }
    protected function close(Travaux $travaux)
    {
        $q = $this->db->prepare('UPDATE Travaux SET date_fin = :date_fin 
                                WHERE id = :id');         
        $q->bindValue(':date_fin', $travaux->date_fin());                          
        $q->bindValue(':id', $travaux->id(), PDO::PARAM_INT);

        $q->execute(); 
    }    
    
    public function save(Travaux $travaux)
    {   
        if ($travaux->validDemande() || $travaux->validPlanif())
        {   
            $travaux->isNew() ? $this->add($travaux) : $this->update($travaux);
        }
        elseif($travaux->validStart())
        {
            $this->start($travaux);
        }
        elseif($travaux->validClose())
        {
            $this->close($travaux);
        }
        else
        {
            throw new RuntimeException('la demande de travaux doit être valide pour être enregistré');
        }
    } 
}
