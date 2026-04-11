<?php

require_once __DIR__ . '/User.php';

// 1. Trashëgimia: Institucioni zgjeron (extends) Perdoruesin
class Institution extends User {
    
    // Atribut specifik vetëm për Institucionet (Enkapsulim: private)
    private $lloji_institucionit; // p.sh. "Universitet Publik", "Kolegj Privat"
    
    // 2. Konstruktori i klasës fëmijë
    public function __construct($emri, $email, $lloji_institucionit) {
        parent::__construct($emri, $email, 'user');
        $this->lloji_institucionit = $lloji_institucionit;
    }

    // Metoda GET specifike për këtë klasë
    public function getLlojiInstitucionit() {
        return $this->lloji_institucionit;
    }

    // Metodë e logjikës së biznesit të domenit AKA
    public function aplikoPerAkreditim($programi_studimit) {
        return "Institucioni <strong>{$this->emri}</strong> ({$this->lloji_institucionit}) ka dorëzuar me sukses aplikimin për akreditimin e programit: <em>{$programi_studimit}</em>.";
    }
}
?>