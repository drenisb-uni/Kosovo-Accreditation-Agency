<?php
require_once '../authentication.php';
kontrolloQasjen('user');
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Raporto Problem - AKA</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .report-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #e67e22; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 150px; resize: none; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn-send { background: #e67e22; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; flex: 2; font-weight: bold; }
        .btn-back { background: #95a5a6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; flex: 1; text-align: center; font-size: 14px; }
        .btn-send:hover { background: #d35400; }
    </style>
</head>
<body>

<div class="report-container">
    <h2>Raporto një Problem</h2>
    <form action="procesi_raportit.php" method="POST">
        <div class="form-group">
            <label>Subjekti</label>
            <input type="text" name="titulli" placeholder="Psh. Gabim në ngarkimin e PDF..." required>
        </div>
        <div class="form-group">
            <label>Përshkrimi i Problemit</label>
            <textarea name="pershkrimi" placeholder="Shpjegoni problemin në detaje..." required></textarea>
        </div>
        <div class="btn-group">
            <a href="index.php" class="btn-back">Kthehu</a>
            <button type="submit" name="dergo_ankesen" class="btn-send">Dërgo Raportin</button>
        </div>
    </form>
</div>

</body>
</html>