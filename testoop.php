<?php
// Thërrasim klasën Institucioni (e cila automatikisht thërret edhe Perdoruesin)
require_once 'classes/Institution.php';

// Krijojmë një objekt të ri
$uni_pr = new Institution("Universiteti i Prishtinës", "rektorati@uni-pr.edu", "Universitet Publik");

// Testojmë metodat e trashëguara nga Perdoruesi (GET)
echo "Emri i Institucionit: " . $uni_pr->getEmri() . "<br>";
echo "Emaili: " . $uni_pr->getEmail() . "<br>";

// Testojmë metodën e klasës fëmijë
echo "<br><strong>Veprimi:</strong><br>";
echo $uni_pr->aplikoPerAkreditim("Inxhinieri Kompjuterike (BSc)");
?>