<?php

class User {
    protected $emri;
    protected $email;
    protected $roli;

    public function __construct($emri, $email, $roli) {
        $this->emri = $emri;
        $this->email = $email;
        $this->roli = $roli;
    }

    public function getEmri() {
        return $this->emri;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRoli() {
        return $this->roli;
    }

    public function setEmri($emri_i_ri) {
        if(strlen($emri_i_ri) > 2) {
            $this->emri = $emri_i_ri;
        }
    }
    public function setEmail($email_i_ri) {
        if(strlen($email_i_ri) > 2) {
            $this->email = $email_i_ri;
        }
    }
}
?>