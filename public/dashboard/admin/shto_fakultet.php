<?php
session_start();
require_once '../authentication.php';
kontrolloQasjen('admin');

$mesazhi = "";

// 2. LOGJIKA E RUAJTJES NË DOSJE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $universiteti = trim($_POST['universiteti']);
    $fakulteti = trim($_POST['fakulteti']); // Do të jetë bosh nëse përdoruesi nuk shkruan gjë
    $statusi = $_POST['statusi'];

    $base_dir = __DIR__ . '../../../../Akreditimet';
    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0777, true);
    }

    // Pastrojmë emrat për t'i përdorur si emra dosjesh pa shkaktuar probleme
    $safe_uni = preg_replace('/[^a-zA-Z0-9]/', '_', $universiteti);
    $safe_fak = preg_replace('/[^a-zA-Z0-9]/', '_', $fakulteti);
    
    // Logjika e re: Nëse fakulteti është bosh, dosja quhet vetëm emri i universitetit
    if (empty($fakulteti)) {
        $folder_name = $safe_uni;
    } else {
        $folder_name = $safe_uni . "_" . $safe_fak;
    }
    
    $target_dir = $base_dir . '/' . $folder_name;

    // Krijojmë dosjen specifike nëse nuk ekziston
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Përgatisim përmbajtjen për skedarin config.txt
    $file_path = $target_dir . '/config.txt';
    
    $config_content = "Universiteti: " . $universiteti . "\n";
    if (!empty($fakulteti)) {
        $config_content .= "Fakulteti: " . $fakulteti . "\n";
    }
    $config_content .= "Statusi: " . $statusi . "\n";
    $config_content .= "Data: " . date("Y-m-d H:i:s") . "\n";

    // Ruajmë skedarin config.txt
    if (file_put_contents($file_path, $config_content)) {
        $mesazhi = "<div class='alert success'>U shtua me sukses! U krijua dosja: accreditations/$folder_name/</div>";
    } else {
        $mesazhi = "<div class='alert error'>Pati një problem me ruajtjen e skedarit config.txt.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shto Institucion/Fakultet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .optional-text { font-weight: normal; color: #888; font-size: 0.9em; }
        input[type="text"], select {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn-submit {
            background-color: #28a745; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 10px;
        }
        .btn-submit:hover { background-color: #218838; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
        .btn-back:hover { text-decoration: underline; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Shto Institucion / Fakultet</h2>
        
        <?php if (!empty($mesazhi)) echo $mesazhi; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            
            <div class="form-group">
                <label for="universiteti">Universiteti / Institucioni (p.sh. UBT)</label>
                <input type="text" id="universiteti" name="universiteti" placeholder="Shkruaj emrin e Universitetit" required>
            </div>

            <div class="form-group">
                <label for="fakulteti">Emri i Fakultetit <span class="optional-text">(Opsionale)</span></label>
                <input type="text" id="fakulteti" name="fakulteti" placeholder="p.sh. Fakulteti i Arteve (Lëre bosh nëse s'ka)">
            </div>

            <div class="form-group">
                <label for="statusi">Statusi i Akreditimit</label>
                <select id="statusi" name="statusi">
                    <option value="Aprovuar">Aprovuar</option>
                    <option value="Në shqyrtim">Në shqyrtim</option>
                    <option value="Refuzuar">Refuzuar</option>
                    <option value="E paakredituar">E paakredituar</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Ruaj të Dhënat</button>
        </form>

        <a href="index.php" class="btn-back">← Kthehu te Tabela Kryesore</a>
    </div>

</body>
</html>