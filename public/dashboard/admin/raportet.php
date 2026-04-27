<?php
require_once '../authentication.php';
kontrolloQasjen('admin');

$reports_dir = '../../../Akreditimet/Reports/';
$mesazhi = '';

// Logjika për fshirjen/zgjidhjen e raportit
if (isset($_GET['fshi'])) {
    $file_fshirje = basename($_GET['fshi']);
    if (file_exists($reports_dir . $file_fshirje)) {
        unlink($reports_dir . $file_fshirje);
        $mesazhi = "<div class='alert success'>✅ Raporti u shënua si i zgjidhur dhe u arkivua!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menaxhimi i Raporteve - KSHC</title>
    <style>
        /* Duke përdorur të njëjtat stile bazë për konsistencë */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f6f9; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 18px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #34495e; text-align: center; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px; margin-bottom: 8px; border-radius: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; }
        .logout-btn { margin-top: auto; background-color: #c0392b; text-align: center; color: white !important; font-weight: bold; }

        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        
        /* Stili i Kartave të Raporteve */
        .report-card { 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            margin-bottom: 20px; 
            border-left: 5px solid #e74c3c; /* Ngjyra e kuqe për vëmendje */
            transition: 0.3s;
        }
        .report-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .report-header { 
            padding: 15px 20px; 
            border-bottom: 1px solid #eee; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: #fafafa;
        }
        .report-user { font-weight: bold; color: #2c3e50; display: flex; align-items: center; gap: 8px; }
        .report-date { color: #7f8c8d; font-size: 13px; }
        
        .report-body { padding: 20px; }
        .report-subject { font-size: 16px; font-weight: bold; color: #e67e22; margin-bottom: 10px; }
        .report-message { color: #34495e; line-height: 1.6; }
        
        .report-footer { 
            padding: 12px 20px; 
            background: #fdfdfd; 
            border-top: 1px solid #eee; 
            text-align: right; 
        }

        .btn-solve { 
            background: #27ae60; 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold; 
            text-decoration: none;
            font-size: 13px;
        }
        .btn-solve:hover { background: #219150; }

        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .no-reports { 
            text-align: center; 
            padding: 50px; 
            color: #bdc3c7; 
            background: white; 
            border-radius: 8px; 
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AKA - KSHC</h2>
        <a href="index.php">Menaxho Akreditimet</a>
        <a href="menaxho_raportet.php" class="active">Raportimet e Problemeve</a>
        <a href="../../logout.php" class="logout-btn">Dilni</a>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Ankesat dhe Raportimet</h2>
            
        </div>

        <?php echo $mesazhi; ?>

        <div class="reports-container">
            <?php
            if (is_dir($reports_dir)) {
                $files = glob($reports_dir . "*.json");
                
                // Renditja: Raportet më të reja në fillim
                array_multisort(array_map('filemtime', $files), SORT_DESC, $files);

                if (empty($files)) {
                    echo "<div class='no-reports'><h3>🎉 Nuk ka asnjë raportim të pazgjidhur.</h3></div>";
                } else {
                    foreach ($files as $file) {
                        $data = json_decode(file_get_contents($file), true);
                        $file_name = basename($file);
                        ?>
                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-user">
                                    <span>👤</span> <?php echo htmlspecialchars($data['përdoruesi']); ?>
                                </div>
                                <div class="report-date">
                                    📅 <?php echo $data['data']; ?>
                                </div>
                            </div>
                            <div class="report-body">
                                <div class="report-subject">
                                    📌 Subjekti: <?php echo htmlspecialchars($data['subjekti']); ?>
                                </div>
                                <div class="report-message">
                                    <?php echo nl2br(htmlspecialchars($data['mesazhi'])); ?>
                                </div>
                            </div>
                            <div class="report-footer">
                                <a href="?fshi=<?php echo $file_name; ?>" class="btn-solve" onclick="return confirm('A dëshironi ta shënoni këtë problem si të zgjidhur?')">
                                    Mark as Resolved ✓
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
    </div>

</body>
</html>