<?php
require_once '../authentication.php';
kontrolloQasjen('admin');

if(empty($_SESSION['emri'])) { $_SESSION['emri'] = 'Admin'; $_SESSION['roli'] = 'admin'; }

$akreditimet_dir = '../../../Akreditimet/';
$mesazhi = '';

// --- 1. LOGJIKA E FSHIRJES (Për të gjitha institucionet) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fshi_pdf']) && isset($_POST['fakulteti']) && isset($_POST['universiteti'])) {
    $pdf_per_fshirje = basename($_POST['fshi_pdf']);
    $uni_fshirje = str_replace(['.', '/', '\\'], '', $_POST['universiteti']);
    $fakulteti_fshirje = str_replace(['.', '/', '\\'], '', $_POST['fakulteti']);
    
    // Nëse është fshirje e akreditimit institucional (drejtpërdrejt në root të Universitetit)
    if ($fakulteti_fshirje === 'MAIN_INST') {
        $file_path = $akreditimet_dir . $uni_fshirje . '/' . $pdf_per_fshirje;
    } else {
        $file_path = $akreditimet_dir . $uni_fshirje . '/' . $fakulteti_fshirje . '/' . $pdf_per_fshirje;
    }
    
    if (file_exists($file_path) && unlink($file_path)) {
        $mesazhi = "<div class='alert success'>Dokumenti <strong>$pdf_per_fshirje</strong> u fshi me sukses!</div>";
    } else {
        $mesazhi = "<div class='alert error'>Gabim: Nuk u mund të fshihej dokumenti.</div>";
    }
}

// --- 2. LOGJIKA E UPLOADIT (Për të gjitha institucionet) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file']) && isset($_POST['target_uni_fak'])) {
    // Ndajmë vlerën e dërguar nga forma (Format: Universiteti||Fakulteti)
    $target_parts = explode('||', $_POST['target_uni_fak']);
    
    if (count($target_parts) == 2) {
        $uni_zgjedhur = str_replace(['.', '/', '\\'], '', $target_parts[0]);
        $fakulteti_zgjedhur = str_replace(['.', '/', '\\'], '', $target_parts[1]);
        
        if ($fakulteti_zgjedhur === 'MAIN_INST') {
            $target_dir = $akreditimet_dir . $uni_zgjedhur . '/';
            $emri_per_mesazh = "Akreditimin Institucional të: " . $uni_zgjedhur;
        } else {
            $target_dir = $akreditimet_dir . $uni_zgjedhur . '/' . $fakulteti_zgjedhur . '/';
            $emri_per_mesazh = $fakulteti_zgjedhur . " (" . $uni_zgjedhur . ")";
        }
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["pdf_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($file_type != "pdf") {
            $mesazhi = "<div class='alert error'>Gabim: Vetëm fajllat PDF lejohen!</div>";
        } else {
            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                $mesazhi = "<div class='alert success'>Dokumenti u ngarkua me sukses për <strong>$emri_per_mesazh</strong>!</div>";
            } else {
                $mesazhi = "<div class='alert error'>Pati një gabim gjatë ngarkimit të fajllit.</div>";
            }
        }
    }
}

// --- 3. LEXIMI I TË DHËNAVE NGA FOLDERAT ---
$te_gjitha_institucionet = [];

if (is_dir($akreditimet_dir)) {
    $universitetet = scandir($akreditimet_dir);
    foreach ($universitetet as $uni) {
        if ($uni !== '.' && $uni !== '..' && is_dir($akreditimet_dir . $uni)) {
            
            $uni_path = $akreditimet_dir . $uni . '/';
            $te_gjitha_institucionet[$uni] = [
                'institucionale' => [
                    'ekziston' => false,
                    'statusi' => 'E panjohur',
                    'data_e_fundit' => 'N/A',
                    'vlefshme_deri' => 'N/A',
                    'dokumentet_pdf' => []
                ],
                'fakultetet' => []
            ];

            $elementet = scandir($uni_path);
            $ka_dokumente_institucionale = false;

            // 3.1 Lexojmë Akreditimin Institucional
            $config_inst_path = $uni_path . 'config.txt';
            if (file_exists($config_inst_path)) {
                $ka_dokumente_institucionale = true;
                $config = parse_ini_file($config_inst_path);
                if ($config) {
                    $te_gjitha_institucionet[$uni]['institucionale']['statusi'] = $config['statusi'] ?? 'E panjohur';
                    $te_gjitha_institucionet[$uni]['institucionale']['data_e_fundit'] = $config['data_e_fundit'] ?? 'N/A';
                    $te_gjitha_institucionet[$uni]['institucionale']['vlefshme_deri'] = $config['vlefshme_deri'] ?? 'N/A';
                }
            }

            foreach ($elementet as $elem) {
                if ($elem !== '.' && $elem !== '..') {
                    if (is_file($uni_path . $elem) && strtolower(pathinfo($elem, PATHINFO_EXTENSION)) == 'pdf') {
                        $ka_dokumente_institucionale = true;
                        $te_gjitha_institucionet[$uni]['institucionale']['dokumentet_pdf'][] = $elem;
                    }
                    elseif (is_dir($uni_path . $elem)) {
                        $fak_data = [
                            'statusi' => 'E panjohur',
                            'data_e_fundit' => 'N/A',
                            'vlefshme_deri' => 'N/A',
                            'dokumentet_pdf' => []
                        ];

                        $config_path = $uni_path . $elem . '/config.txt';
                        if (file_exists($config_path)) {
                            $config = parse_ini_file($config_path);
                            if ($config) {
                                $fak_data['statusi'] = $config['statusi'] ?? 'E panjohur';
                                $fak_data['data_e_fundit'] = $config['data_e_fundit'] ?? 'N/A';
                                $fak_data['vlefshme_deri'] = $config['vlefshme_deri'] ?? 'N/A';
                            }
                        }

                        $files = scandir($uni_path . $elem);
                        foreach ($files as $file) {
                            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
                                $fak_data['dokumentet_pdf'][] = $file;
                            }
                        }

                        $te_gjitha_institucionet[$uni]['fakultetet'][$elem] = $fak_data;
                    }
                }
            }

            $te_gjitha_institucionet[$uni]['institucionale']['ekziston'] = $ka_dokumente_institucionale;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paneli i KSHC - Agjencia e Akreditimit</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 18px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #34495e; text-align: center; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px; margin-bottom: 8px; border-radius: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; }
        .logout-btn { margin-top: auto; background-color: #c0392b; text-align: center; color: white !important; font-weight: bold; }

        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .badge-roli { background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }

        .upload-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid #9b59b6; }
        .upload-card select { padding: 8px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; width: 350px;}
        .upload-card input[type="file"] { padding: 8px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; width: 250px;}
        .btn { background: #3498db; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #2980b9; }

        .btn-fshi { background: #e74c3c; color: white; border: none; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 11px; margin-left: 5px; }
        .btn-fshi:hover { background: #c0392b; }

        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .tabela-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: top;}
        th { background-color: #f8f9fa; color: #333; font-weight: 600; }
        tr:hover { background-color: #f1f5f8; }
        
        .status { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status.aprovuar { background: #d4edda; color: #155724; }
        .status.refuzuar { background: #f8d7da; color: #721c24; }
        .status.ne-shqyrtim { background: #fff3cd; color: #856404; }
        
        .pdf-link { color: #2c3e50; text-decoration: none; font-size: 13px; font-weight: bold; }
        .pdf-item { display: flex; align-items: center; margin-bottom: 5px; background: #f1f2f6; padding: 5px 8px; border-radius: 4px; width: fit-content; }
        .uni-header { background: #eef2f5; font-weight: bold; color: #2c3e50; }
        .inst-row { background-color: #fdfefe; }
        .inst-row td { border-bottom: 2px solid #ecf0f1; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AKA - KSHC</h2>
        <a href="index.php" class="active">Menaxho Akreditimet</a>
        <a href="../../logout.php" class="logout-btn">Dilni</a>
    </div>

    <div class="main-content">
        
        <div class="top-header">
            <h2>Paneli i Akreditimeve</h2>
            <div class="user-info">
                <span>Mirësevini, <strong><?php echo $_SESSION['emri']; ?></strong></span>
                <span class="badge-roli"><?php echo strtoupper($_SESSION['roli']); ?></span>
            </div>
        </div>

        <?php echo $mesazhi; ?>

        <div class="upload-card">
            <h3 style="color: #9b59b6; margin-bottom: 10px;">Shto dokument të ri (PDF)</h3>
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <select name="target_uni_fak" required>
                    <option value="" disabled selected>Zgjidh Institucionin dhe Fakultetin...</option>
                    
                    <?php foreach ($te_gjitha_institucionet as $uni => $data): ?>
                        <optgroup label="<?php echo htmlspecialchars($uni); ?>">
                            
                            <option value="<?php echo htmlspecialchars($uni); ?>||MAIN_INST">
                                ➜ Akreditimi Institucional i <?php echo htmlspecialchars($uni); ?>
                            </option>
                            
                            <?php foreach ($data['fakultetet'] as $fak_emri => $fak_data): ?>
                                <option value="<?php echo htmlspecialchars($uni); ?>||<?php echo htmlspecialchars($fak_emri); ?>">
                                    &nbsp;&nbsp;&nbsp;Fakulteti: <?php echo htmlspecialchars($fak_emri); ?>
                                </option>
                            <?php endforeach; ?>
                            
                        </optgroup>
                    <?php endforeach; ?>
                    
                </select>
                <input type="file" name="pdf_file" accept=".pdf" required>
                <button type="submit" class="btn">Ngarko</button>
            </form>
        </div>

        <div class="tabela-container">
            <h3 style="margin-bottom: 15px; color: #2c3e50;">Lista Gjithëpërfshirëse e Akreditimeve</h3>
            <table>
                <thead>
                    <tr>
                        <th>Institucioni / Fakulteti</th>
                        <th>Vlefshmëria</th>
                        <th>Statusi</th>
                        <th>Dokumentet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($te_gjitha_institucionet)): ?>
                        <tr><td colspan="4" style="text-align: center;">Nuk u gjet asnjë institucion.</td></tr>
                    <?php else: ?>
                        <?php foreach ($te_gjitha_institucionet as $uni => $data): ?>
                            
                            <tr class="uni-header">
                                <td colspan="4">🏫 <?php echo htmlspecialchars($uni); ?></td>
                            </tr>

                            <?php if ($data['institucionale']['ekziston']): 
                                $inst = $data['institucionale'];
                                $klasa_statusit = 'ne-shqyrtim';
                                if (strtolower($inst['statusi']) == 'aprovuar') $klasa_statusit = 'aprovuar';
                                if (strtolower($inst['statusi']) == 'refuzuar') $klasa_statusit = 'refuzuar';
                            ?>
                                <tr class="inst-row">
                                    <td style="padding-left: 40px; color: #2980b9;">🎓 <strong>Akreditimi Institucional (Qendror)</strong></td>
                                    <td>
                                        <small style="color: gray;">Nga:</small> <?php echo $inst['data_e_fundit']; ?> <br>
                                        <small style="color: gray;">Deri:</small> <strong><?php echo $inst['vlefshme_deri']; ?></strong>
                                    </td>
                                    <td><span class="status <?php echo $klasa_statusit; ?>"><?php echo htmlspecialchars($inst['statusi']); ?></span></td>
                                    <td>
                                        <?php if (empty($inst['dokumentet_pdf'])): ?>
                                            <span style="color: #999; font-size: 12px;">S'ka dokumente</span>
                                        <?php else: ?>
                                            <?php foreach ($inst['dokumentet_pdf'] as $pdf): 
                                                $file_url = $akreditimet_dir . urlencode($uni) . '/' . urlencode($pdf);
                                            ?>
                                                <div class="pdf-item">
                                                    <a href="<?php echo $file_url; ?>" target="_blank" class="pdf-link">📄 <?php echo htmlspecialchars($pdf); ?></a>
                                                    
                                                    <form action="index.php" method="POST" style="display:inline;" onsubmit="return confirm('A jeni të sigurt?');">
                                                        <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                                        <input type="hidden" name="fakulteti" value="MAIN_INST">
                                                        <input type="hidden" name="fshi_pdf" value="<?php echo htmlspecialchars($pdf); ?>">
                                                        <button type="submit" class="btn-fshi" title="Fshi Dokumentin">X</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if (empty($data['fakultetet'])): ?>
                                <tr>
                                    <td colspan="4" style="padding-left: 40px; color: #e67e22; font-size: 13px;">
                                        <em>⚠️ Nuk ka fakultete ose programe specifike të regjistruara për këtë institucion.</em>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['fakultetet'] as $fak_emri => $fak_data): 
                                    $klasa_statusit = 'ne-shqyrtim';
                                    if (strtolower($fak_data['statusi']) == 'aprovuar') $klasa_statusit = 'aprovuar';
                                    if (strtolower($fak_data['statusi']) == 'refuzuar') $klasa_statusit = 'refuzuar';
                                ?>
                                    <tr>
                                        <td style="padding-left: 40px;">↳ <strong><?php echo htmlspecialchars($fak_emri); ?></strong></td>
                                        <td>
                                            <small style="color: gray;">Nga:</small> <?php echo $fak_data['data_e_fundit']; ?> <br>
                                            <small style="color: gray;">Deri:</small> <strong><?php echo $fak_data['vlefshme_deri']; ?></strong>
                                        </td>
                                        <td><span class="status <?php echo $klasa_statusit; ?>"><?php echo htmlspecialchars($fak_data['statusi']); ?></span></td>
                                        <td>
                                            <?php if (empty($fak_data['dokumentet_pdf'])): ?>
                                                <span style="color: #999; font-size: 12px;">S'ka dokumente</span>
                                            <?php else: ?>
                                                <?php foreach ($fak_data['dokumentet_pdf'] as $pdf): 
                                                    $file_url = $akreditimet_dir . urlencode($uni) . '/' . urlencode($fak_emri) . '/' . urlencode($pdf);
                                                ?>
                                                    <div class="pdf-item">
                                                        <a href="<?php echo $file_url; ?>" target="_blank" class="pdf-link">📄 <?php echo htmlspecialchars($pdf); ?></a>
                                                        
                                                        <form action="index.php" method="POST" style="display:inline;" onsubmit="return confirm('A jeni të sigurt?');">
                                                            <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                                            <input type="hidden" name="fakulteti" value="<?php echo htmlspecialchars($fak_emri); ?>">
                                                            <input type="hidden" name="fshi_pdf" value="<?php echo htmlspecialchars($pdf); ?>">
                                                            <button type="submit" class="btn-fshi" title="Fshi Dokumentin">X</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>