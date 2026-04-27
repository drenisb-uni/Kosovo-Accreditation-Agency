<?php
session_start();

// Rruga ku admini i lexon raportet (duhet të jetë e njëjtë me atë të adminit)
$reports_dir = '../../../Akreditimet/Reports/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Krijo folderin nëse nuk ekziston
    if (!is_dir($reports_dir)) {
        mkdir($reports_dir, 0777, true);
    }

    // Përgatit të dhënat
    $ankesa = [
        'përdoruesi' => $_SESSION['emri'] ?? 'Përdorues i panjohur',
        'subjekti'   => htmlspecialchars($_POST['titulli']),
        'mesazhi'    => htmlspecialchars($_POST['pershkrimi']),
        'data'       => date('d.m.Y H:i')
    ];

    // Krijo një emër unik për skedarin JSON
    $emri_file = $reports_dir . "ankesa_" . time() . ".json";
    
    // Ruaj të dhënat
    if (file_put_contents($emri_file, json_encode($ankesa, JSON_PRETTY_PRINT))) {
        echo "<script>
                alert('Raporti u dërgua me sukses!');
                window.location.href='index.php'; 
              </script>";
    } else {
        echo "Gabim: Nuk u mundësua ruajtja e raportit. Kontrolloni aksesin e folderit.";
    }
} else {
    // Nëse dikush tenton ta hapë direkt këtë faqe pa dërguar formën
    header("Location: index.php");
    exit();
}
?>

