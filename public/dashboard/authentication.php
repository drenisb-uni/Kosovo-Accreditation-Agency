<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Funksion për të mbrojtur faqet (Kërkesë e mundshme për OOP/Funksione)
 * Merr si parametër rolin që i lejohet të shohë këtë faqe.
 */
function kontrolloQasjen($roli_i_kerkuar) {
    // 1. Nëse përdoruesi NUK është i loguar fare
    if (!isset($_SESSION['email']) || !isset($_SESSION['roli'])) {
        // Ridrejtoje te login.php (rruga mund të ndryshojë varësisht nga folderi)
        header("Location: ../../login.php"); 
        exit();
    }

    // 2. Nëse është i loguar, por roli NUK përputhet me atë të lejuar për faqen
    if ($_SESSION['roli'] !== $roli_i_kerkuar) {
        die("<h3>Ndalohet Aksesi (403 Forbidden)</h3> Ju nuk keni të drejtë (rol) për të parë këtë faqe.");
    }
}
?>