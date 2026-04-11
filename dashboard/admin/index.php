<?php
// 1. Thërrasim middleware-in për të mbrojtur faqen
require_once '../authentication.php';

// 2. Lejojmë vetëm rolin 'admin' të shohë këtë faqe
kontrolloQasjen('admin');

// 3. Array Multidimensional që simulon kërkesat nga databaza (Kërkesë e Fazës 1)
$kerkesat_akreditim = [
    [
        'id' => 101, 
        'institucioni' => 'Universiteti i Prishtinës', 
        'programi' => 'Inxhinieri Kompjuterike (BSc)', 
        'statusi' => 'Në shqyrtim', 
        'data' => '2026-04-10'
    ],
    [
        'id' => 102, 
        'institucioni' => 'Universiteti i Prizrenit', 
        'programi' => 'Gjuhë Shqipe (BA)', 
        'statusi' => 'Aprovuar', 
        'data' => '2026-04-05'
    ],
    [
        'id' => 103, 
        'institucioni' => 'Kolegji UBT', 
        'programi' => 'Arkitekturë (MSc)', 
        'statusi' => 'Refuzuar', 
        'data' => '2026-04-01'
    ]
];
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paneli i KSHC - Agjencia e Akreditimit</title>
    <style>
        /* CSS Bazik për një pamje moderne Dashboard-i */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; height: 100vh; overflow: hidden; }
        
        /* Menyja Anësore (Sidebar) */
        .sidebar { width: 250px; background-color: #2c3e50; color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 18px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #34495e; text-align: center; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px; margin-bottom: 8px; border-radius: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; }
        .logout-btn { margin-top: auto; background-color: #c0392b; text-align: center; color: white !important; font-weight: bold; }
        .logout-btn:hover { background-color: #e74c3c; }

        /* Hapësira Kryesore (Main Content) */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        
        /* Header i Dashboardit */
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .user-info { font-size: 14px; color: #555; }
        .badge-roli { background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }

        /* Kartat e Statistikave */
        .karta-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .karta { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 5px solid #3498db; }
        .karta h3 { font-size: 14px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 10px; }
        .karta .numri { font-size: 28px; font-weight: bold; color: #2c3e50; }

        /* Tabela e të Dhënave */
        .tabela-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tabela-container h3 { margin-bottom: 15px; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; }
        tr:hover { background-color: #f1f5f8; }
        
        /* Ngjyrat e Statuseve */
        .status { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status.aprovuar { background: #d4edda; color: #155724; }
        .status.refuzuar { background: #f8d7da; color: #721c24; }
        .status.ne-shqyrtim { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AKA - KSHC</h2>
        <a href="index.php" class="active">Kërkesat për Akreditim</a>
        <a href="#">Lista e Institucioneve</a>
        <a href="#">Ekspertët Ndërkombëtarë</a>
        <a href="#">Raportet e Vlerësimit</a>
        <a href="../../logout.php" class="logout-btn">Dilni (Logout)</a>
    </div>

    <div class="main-content">
        
        <div class="top-header">
            <h2>Paneli i Administrimit</h2>
            <div class="user-info">
                <span>Mirësevini, <strong><?php echo $_SESSION['emri']; ?></strong></span>
                <span class="badge-roli"><?php echo strtoupper($_SESSION['roli']); ?></span>
            </div>
        </div>

        <div class="karta-container">
            <div class="karta">
                <h3>Institucione të Regjistruara</h3>
                <div class="numri">14</div>
            </div>
            <div class="karta" style="border-left-color: #f39c12;">
                <h3>Kërkesa në Pritje</h3>
                <div class="numri">7</div>
            </div>
            <div class="karta" style="border-left-color: #2ecc71;">
                <h3>Programe të Akredituara</h3>
                <div class="numri">82</div>
            </div>
        </div>

        <div class="tabela-container">
            <h3>Aplikimet e Fundit</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Institucioni</th>
                        <th>Programi i Studimit</th>
                        <th>Data</th>
                        <th>Statusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // ITERIMI: Këtu demonstrojmë ciklin foreach mbi array-n multidimensional
                    foreach ($kerkesat_akreditim as $kerkesa) { 
                        
                        // Krijojmë një klasë CSS dinamike bazuar në statusin e aplikimit
                        $klasa_statusit = '';
                        if ($kerkesa['statusi'] == 'Aprovuar') $klasa_statusit = 'aprovuar';
                        elseif ($kerkesa['statusi'] == 'Refuzuar') $klasa_statusit = 'refuzuar';
                        else $klasa_statusit = 'ne-shqyrtim';
                    ?>
                        <tr>
                            <td>#<?php echo $kerkesa['id']; ?></td>
                            <td><strong><?php echo $kerkesa['institucioni']; ?></strong></td>
                            <td><?php echo $kerkesa['programi']; ?></td>
                            <td><?php echo $kerkesa['data']; ?></td>
                            <td><span class="status <?php echo $klasa_statusit; ?>"><?php echo $kerkesa['statusi']; ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>