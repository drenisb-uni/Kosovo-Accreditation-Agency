<?php
require_once '../authentication.php';
kontrolloQasjen('admin');

$akreditimet_dir = '../../../Akreditimet/';
$mesazhi = '';

// Funksion ndihmës për të fshirë dosjet me gjithë përmbajtjen (Rekursivisht)
function fshi_dosjen_rekursivisht($dir) {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? fshi_dosjen_rekursivisht("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

// --- 1. LOGJIKA E FSHIRJES SË PDF-së SPECIFIKE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fshi_pdf']) && isset($_POST['fakulteti']) && isset($_POST['universiteti'])) {
    $pdf_per_fshirje = basename($_POST['fshi_pdf']);
    $uni_fshirje = str_replace(['.', '/', '\\'], '', $_POST['universiteti']);
    $fakulteti_fshirje = str_replace(['.', '/', '\\'], '', $_POST['fakulteti']);
    $statusi_pas_fshirjes = $_POST['statusi_pas_fshirjes'] ?? 'Refuzuar';
    
    if ($fakulteti_fshirje === 'MAIN_INST') {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/';
    } else {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/' . $fakulteti_fshirje . '/';
    }
    
    $file_path = $folder_path . $pdf_per_fshirje;
    
    if (file_exists($file_path) && unlink($file_path)) {
        $config_path = $folder_path . 'config.txt';
        $data_fundit = 'N/A';
        $vlefshme = 'N/A';
        
        if(file_exists($config_path)) {
            $config_vjeter = parse_ini_file($config_path);
            if($config_vjeter) {
                $data_fundit = $config_vjeter['data_e_fundit'] ?? 'N/A';
                $vlefshme = $config_vjeter['vlefshme_deri'] ?? 'N/A';
            }
        }
        
        $config_content = "statusi=\"$statusi_pas_fshirjes\"\ndata_e_fundit=\"$data_fundit\"\nvlefshme_deri=\"$vlefshme\"\n";
        file_put_contents($config_path, $config_content);
        
        $mesazhi = "<div class='alert success'>Dokumenti <strong>$pdf_per_fshirje</strong> u fshi dhe statusi u ndryshua në: <strong>$statusi_pas_fshirjes</strong>!</div>";
    } else {
        $mesazhi = "<div class='alert error'>Gabim: Nuk u mund të fshihej dokumenti.</div>";
    }
}

// --- 2. LOGJIKA E FSHIRJES SË PLOTË (DOSJES/FAKULTETIT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fshi_direktorin'])) {
    $uni_fshirje = str_replace(['.', '/', '\\'], '', $_POST['universiteti']);
    $fakulteti_fshirje = str_replace(['.', '/', '\\'], '', $_POST['fakulteti']);
    
    if ($fakulteti_fshirje === 'MAIN_INST') {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/';
        $emri_msg = "Universiteti $uni_fshirje me të gjitha fakultetet";
    } else {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/' . $fakulteti_fshirje . '/';
        $emri_msg = "Fakulteti $fakulteti_fshirje";
    }

    if (fshi_dosjen_rekursivisht($folder_path)) {
        $mesazhi = "<div class='alert success'><strong>$emri_msg</strong> u fshi me sukses nga sistemi!</div>";
    } else {
        $mesazhi = "<div class='alert error'>Gabim gjatë fshirjes së $emri_msg. Sigurohuni që dosja ekziston.</div>";
    }
}

// --- 3. LOGJIKA E PËRDITËSIMIT TË TË DHËNAVE (DHE UPLOADIT OKSIONAL) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['target_uni_fak']) && !isset($_POST['fshi_pdf']) && !isset($_POST['fshi_direktorin'])) {
    $target_parts = explode('||', $_POST['target_uni_fak']);
    $statusi_ri = $_POST['statusi_ri'] ?? 'Aprovuar';
    $data_nga = $_POST['data_nga'] ?? 'N/A';
    $data_deri = $_POST['data_deri'] ?? 'N/A';
    
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

        // Përditësojmë gjithmonë config.txt (Kjo shërben si EDIT)
        $config_content = "statusi=\"$statusi_ri\"\ndata_e_fundit=\"$data_nga\"\nvlefshme_deri=\"$data_deri\"\n";
        file_put_contents($target_dir . 'config.txt', $config_content);
        $mesazhi_shtese = "";

        // Nëse u zgjodh edhe një PDF për t'u ngarkuar
        if (!empty($_FILES['pdf_file']['name'])) {
            $file_name = basename($_FILES["pdf_file"]["name"]);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if ($file_type != "pdf") {
                $mesazhi_shtese = " <br><span style='color:red;'>Kujdes: Fajlli nuk u ngarkua sepse lejohen vetëm PDF!</span>";
            } else {
                if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                    $mesazhi_shtese = " Dokumenti i ri u ngarkua gjithashtu.";
                } else {
                    $mesazhi_shtese = " <br><span style='color:red;'>Pati një problem gjatë ngarkimit të PDF.</span>";
                }
            }
        }
        
        $mesazhi = "<div class='alert success'>Të dhënat u përditësuan për <strong>$emri_per_mesazh</strong>! $mesazhi_shtese</div>";
    }
}

// --- VARIABLAT PËR STATISTIKAT ---
$stats_institucione = 0;
$stats_aprovuar = 0;
$stats_ne_shqyrtim = 0;
$stats_skadojne = 0;

function analizoStatistikat($statusi, $data_deri) {
    global $stats_aprovuar, $stats_ne_shqyrtim, $stats_skadojne;
    $st = strtolower(trim($statusi));
    
    if ($st == 'aprovuar') $stats_aprovuar++;
    if ($st == 'në shqyrtim' || $st == 'ne shqyrtim') $stats_ne_shqyrtim++;
    
    if ($data_deri !== 'N/A' && !empty($data_deri)) {
        $koha_deri = strtotime($data_deri);
        if ($koha_deri) {
            $ditet = ($koha_deri - time()) / 86400; 
            if ($ditet <= 30 && $ditet >= 0) { // Duhet >= 0 qe mos te llogarise te skaduarat
                $stats_skadojne++;
            }
        }
    }
}

function gjeneroBadgeSkadimi($data_deri_str) {
    if ($data_deri_str === 'N/A' || empty($data_deri_str)) return '';
    $data_deri = strtotime($data_deri_str);
    if (!$data_deri) return '';
    
    $ditet_mbetura = ($data_deri - time()) / 86400;
    
    if ($ditet_mbetura < 0) {
        return '<br><span style="background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; display: inline-block; margin-top: 4px;">🔴 Skaduar</span>';
    } elseif ($ditet_mbetura <= 30) {
        return '<br><span style="background: #f39c12; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; display: inline-block; margin-top: 4px;">⚠️ Skadon së shpejti</span>';
    }
    return '';
}

// --- 4. LEXIMI I TË DHËNAVE NGA FOLDERAT ---
$te_gjitha_institucionet = [];

if (is_dir($akreditimet_dir)) {
    $universitetet = scandir($akreditimet_dir);
    foreach ($universitetet as $uni) {
        if ($uni !== '.' && $uni !== '..' && is_dir($akreditimet_dir . $uni)) {
            $stats_institucione++; 
            
            $uni_path = $akreditimet_dir . $uni . '/';
            $te_gjitha_institucionet[$uni] = [
                'institucionale' => ['ekziston' => false, 'statusi' => 'E panjohur', 'data_e_fundit' => 'N/A', 'vlefshme_deri' => 'N/A', 'dokumentet_pdf' => []],
                'fakultetet' => []
            ];

            $elementet = scandir($uni_path);
            $ka_dokumente_institucionale = false;

            $config_inst_path = $uni_path . 'config.txt';
            if (file_exists($config_inst_path)) {
                $ka_dokumente_institucionale = true;
                $config = @parse_ini_file($config_inst_path);
                if ($config) {
                    $te_gjitha_institucionet[$uni]['institucionale']['statusi'] = $config['statusi'] ?? 'E panjohur';
                    $te_gjitha_institucionet[$uni]['institucionale']['data_e_fundit'] = $config['data_e_fundit'] ?? 'N/A';
                    $te_gjitha_institucionet[$uni]['institucionale']['vlefshme_deri'] = $config['vlefshme_deri'] ?? 'N/A';
                    
                    analizoStatistikat($te_gjitha_institucionet[$uni]['institucionale']['statusi'], $te_gjitha_institucionet[$uni]['institucionale']['vlefshme_deri']);
                }
            }

            foreach ($elementet as $elem) {
                if ($elem !== '.' && $elem !== '..') {
                    if (is_file($uni_path . $elem) && strtolower(pathinfo($elem, PATHINFO_EXTENSION)) == 'pdf') {
                        $ka_dokumente_institucionale = true;
                        $te_gjitha_institucionet[$uni]['institucionale']['dokumentet_pdf'][] = $elem;
                    } elseif (is_dir($uni_path . $elem)) {
                        $fak_data = ['statusi' => 'E panjohur', 'data_e_fundit' => 'N/A', 'vlefshme_deri' => 'N/A', 'dokumentet_pdf' => []];
                        $config_path = $uni_path . $elem . '/config.txt';
                        if (file_exists($config_path)) {
                            $config = @parse_ini_file($config_path);
                            if ($config) {
                                $fak_data['statusi'] = $config['statusi'] ?? 'E panjohur';
                                $fak_data['data_e_fundit'] = $config['data_e_fundit'] ?? 'N/A';
                                $fak_data['vlefshme_deri'] = $config['vlefshme_deri'] ?? 'N/A';
                                analizoStatistikat($fak_data['statusi'], $fak_data['vlefshme_deri']);
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

function merrKlasenEStatusit($statusi) {
    $st = strtolower(trim($statusi));
    if ($st == 'aprovuar') return 'aprovuar';
    if ($st == 'refuzuar') return 'refuzuar';
    if ($st == 'e paakredituar' || $st == 'epaakredituar') return 'e-paakredituar';
    return 'ne-shqyrtim';
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
        .main-content { flex: 1; padding: 30px; overflow-y: auto; scroll-behavior: smooth; }
        .top-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .badge-roli { background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }

        .stats-grid { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-bottom: 4px solid #bdc3c7; text-align: center;}
        .stat-card h3 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; }
        .stat-card p { color: #7f8c8d; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .stat-card.blue { border-bottom-color: #3498db; }
        .stat-card.green { border-bottom-color: #2ecc71; }
        .stat-card.yellow { border-bottom-color: #f1c40f; }
        .stat-card.red { border-bottom-color: #e74c3c; }

        .upload-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid #9b59b6; transition: 0.3s; }
        .upload-card.highlight { box-shadow: 0 0 15px rgba(155, 89, 182, 0.5); transform: scale(1.01); }
        .upload-row { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; align-items: center; }
        .upload-card select, .upload-card input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #3498db; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #2980b9; }

        .btn-fshi { background: #e74c3c; color: white; border: none; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 11px; margin-left: 5px; }
        .btn-fshi:hover { background: #c0392b; }
        
        .btn-aksion { padding: 6px 10px; border-radius: 4px; border: none; font-size: 12px; font-weight: bold; cursor: pointer; color: white; text-decoration: none; display: inline-block; margin-right: 4px; }
        .btn-edit { background-color: #f39c12; }
        .btn-edit:hover { background-color: #d68910; }
        .btn-delete-folder { background-color: #c0392b; }
        .btn-delete-folder:hover { background-color: #922b21; }

        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .tabela-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tabela-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .search-input { padding: 8px 15px; border: 1px solid #ccc; border-radius: 20px; width: 300px; outline: none; transition: 0.3s; }
        .search-input:focus { border-color: #3498db; box-shadow: 0 0 5px rgba(52,152,219,0.3); }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle;}
        th { background-color: #f8f9fa; color: #333; font-weight: 600; position: sticky; top: 0; z-index: 11; box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
        tr:hover { background-color: #f1f5f8; }
        
        .status { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status.aprovuar { background: #d4edda; color: #155724; }
        .status.refuzuar { background: #f8d7da; color: #721c24; }
        .status.ne-shqyrtim { background: #fff3cd; color: #856404; }
        .status.e-paakredituar { background: #e2e3e5; color: #383d41; } 
        
        .pdf-link { color: #2c3e50; text-decoration: none; font-size: 13px; font-weight: bold; }
        .pdf-item { display: flex; align-items: center; margin-bottom: 5px; background: #f1f2f6; padding: 5px 8px; border-radius: 4px; width: fit-content; }
        .uni-header { background: #eef2f5; font-weight: bold; color: #2c3e50; }
        .uni-header td { position: sticky; top: 42px; z-index: 10; background: #eef2f5; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .inst-row { background-color: #fdfefe; }
        .inst-row td { border-bottom: 2px solid #ecf0f1; }
    </style>
    <script>
        // Konfirmimi për fshirjen e PDF-së
        function konfirmoFshirjen(form) {
            if (confirm("Kujdes! A jeni të sigurt që dëshironi të fshini këtë dokument?")) {
                let statusiRi = prompt("Statusi i ri pas fshirjes? (1-Aprovuar, 2-Refuzuar, 3-Në shqyrtim, 4-E paakredituar)", "4");
                let vlera = "E paakredituar"; 
                if (statusiRi === "1") vlera = "Aprovuar";
                if (statusiRi === "2") vlera = "Refuzuar";
                if (statusiRi === "3") vlera = "Në shqyrtim";
                form.statusi_pas_fshirjes.value = vlera;
                return true;
            }
            return false;
        }

        // Konfirmimi për Fshirjen e të gjithë Dosjes/Fakultetit
        function konfirmoFshirjenDosjes(emri) {
            return confirm("KUJDES EKUATEM: A je i/e sigurt që dëshiron të fshish përfundimisht '" + emri + "' dhe gjithë përmbajtjen e tij? Ky veprim nuk zhbëhet!");
        }

        // Funksioni që merr të dhënat dhe i çon lart tek forma për t'i ndryshuar
        function editoAkreditimin(uni_fak_vlere, statusi, data_nga, data_deri) {
            document.querySelector('select[name="target_uni_fak"]').value = uni_fak_vlere;
            document.querySelector('select[name="statusi_ri"]').value = statusi;
            document.querySelector('input[name="data_nga"]').value = (data_nga && data_nga !== 'N/A') ? data_nga : '';
            document.querySelector('input[name="data_deri"]').value = (data_deri && data_deri !== 'N/A') ? data_deri : '';
            
            // Theksojmë formën që përdoruesi ta shohë ku po bën Edit
            let forma = document.getElementById('forma-përditësimit');
            forma.classList.add('highlight');
            setTimeout(() => forma.classList.remove('highlight'), 1500);

            // Shkojmë lart në fillim të faqes
            document.querySelector('.main-content').scrollTo(0, 0);
        }

        function filtroTabelen() {
            let input = document.getElementById("kerkimLive").value.toLowerCase();
            let tbody = document.querySelector("#tabelaAkreditimeve tbody");
            let trs = tbody.querySelectorAll("tr");
            let currentHeader = null; let groupRows = [];

            function processGroup(header, rows) {
                if (!header) return;
                let headerMatches = header.textContent.toLowerCase().includes(input);
                let anyRowMatches = false;
                rows.forEach(row => {
                    if (headerMatches || row.textContent.toLowerCase().includes(input)) {
                        row.style.display = ""; anyRowMatches = true;
                    } else { row.style.display = "none"; }
                });
                header.style.display = (headerMatches || anyRowMatches) ? "" : "none";
            }

            for (let i = 0; i < trs.length; i++) {
                let tr = trs[i];
                if (tr.children.length === 1 && tr.textContent.includes("Nuk u gjet")) continue;
                if (tr.classList.contains('uni-header')) {
                    processGroup(currentHeader, groupRows);
                    currentHeader = tr; groupRows = [];
                } else { groupRows.push(tr); }
            }
            processGroup(currentHeader, groupRows);
        }
    </script>
</head>
<body>

    <div class="sidebar">
        <h2>AKA - KSHC</h2>
        <a href="index.php" class="active">Menaxho Akreditimet</a>
        <a href="shto_fakultet.php" >Shto Fakultete</a>
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

        <div class="stats-grid">
            <div class="stat-card blue"><h3><?php echo $stats_institucione; ?></h3><p>Institucione në Total</p></div>
            <div class="stat-card green"><h3><?php echo $stats_aprovuar; ?></h3><p>Programe të Aprovuara</p></div>
            <div class="stat-card yellow"><h3><?php echo $stats_ne_shqyrtim; ?></h3><p>Në Shqyrtim</p></div>
            <div class="stat-card red"><h3><?php echo $stats_skadojne; ?></h3><p>Në rrezik Skadimi (< 30 ditë)</p></div>
        </div>

        <div class="upload-card" id="forma-përditësimit">
            <h3 style="color: #9b59b6; margin-bottom: 15px;">Përditëso Statusin / Shto Dokument</h3>
            <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 15px;">Zgjidh një fakultet për t'i ndryshuar statusin e akreditimit. Ngarkimi i një PDF-je është opsional.</p>
            <form action="index.php" method="POST" enctype="multipart/form-data">
                
                <div class="upload-row">
                    <select name="target_uni_fak" required style="width: 350px;">
                        <option value="" disabled selected>1. Zgjidh Institucionin dhe Fakultetin...</option>
                        <?php foreach ($te_gjitha_institucionet as $uni => $data): ?>
                            <optgroup label="<?php echo htmlspecialchars($uni); ?>">
                                <option value="<?php echo htmlspecialchars($uni); ?>||MAIN_INST">➜ Akreditimi Institucional i <?php echo htmlspecialchars($uni); ?></option>
                                <?php foreach ($data['fakultetet'] as $fak_emri => $fak_data): ?>
                                    <option value="<?php echo htmlspecialchars($uni); ?>||<?php echo htmlspecialchars($fak_emri); ?>">&nbsp;&nbsp;&nbsp;Fakulteti: <?php echo htmlspecialchars($fak_emri); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="file" name="pdf_file" accept=".pdf" style="width: 250px;" title="Opsionale: Ngarko dokument të ri">
                </div>

                <div class="upload-row">
                    <select name="statusi_ri" required>
                        <option value="" disabled selected>2. Statusi i ri...</option>
                        <option value="Aprovuar">✅ Aprovuar</option>
                        <option value="Refuzuar">❌ Refuzuar</option>
                        <option value="Në shqyrtim">⏳ Në shqyrtim</option>
                        <option value="E paakredituar">🚫 E paakredituar</option>
                    </select>
                    
                    <span style="font-size: 14px; color: #555;">Nga:</span>
                    <input type="date" name="data_nga" required>
                    
                    <span style="font-size: 14px; color: #555;">Deri më:</span>
                    <input type="date" name="data_deri" required>
                    
                    <button type="submit" class="btn">Ruaj Ndryshimet</button>
                </div>
            </form>
        </div>

        <div class="tabela-container">
            <div class="tabela-header-flex">
                <h3 style="color: #2c3e50;">Lista Gjithëpërfshirëse e Akreditimeve</h3>
                <input type="text" id="kerkimLive" class="search-input" placeholder="🔍 Kërko universitet, fakultet, status..." onkeyup="filtroTabelen()">
            </div>
            
            <table id="tabelaAkreditimeve">
                <thead>
                    <tr>
                        <th>Institucioni / Fakulteti</th>
                        <th>Vlefshmëria</th>
                        <th>Statusi</th>
                        <th>Dokumentet</th>
                        <th style="width: 150px; text-align: center;">Aksionet</th> </tr>
                </thead>
                <tbody>
                    <?php if (empty($te_gjitha_institucionet)): ?>
                        <tr><td colspan="5" style="text-align: center;">Nuk u gjet asnjë institucion.</td></tr>
                    <?php else: ?>
                        <?php foreach ($te_gjitha_institucionet as $uni => $data): ?>
                            
                            <tr class="uni-header">
                                <td colspan="4">🏫 <?php echo htmlspecialchars($uni); ?></td>
                                <td style="text-align: center;">
                                    <form action="index.php" method="POST" style="display:inline;" onsubmit="return konfirmoFshirjenDosjes('<?php echo htmlspecialchars($uni); ?> (Gjithë Universiteti)');">
                                        <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                        <input type="hidden" name="fakulteti" value="MAIN_INST">
                                        <input type="hidden" name="fshi_direktorin" value="1">
                                        <button type="submit" class="btn-aksion btn-delete-folder" title="Fshi të gjithë universitetin">🗑️ Fshi Uni.</button>
                                    </form>
                                </td>
                            </tr>

                            <?php if ($data['institucionale']['ekziston']): 
                                $inst = $data['institucionale'];
                                $klasa_statusit = merrKlasenEStatusit($inst['statusi']);
                                $uni_fak_id = htmlspecialchars($uni) . '||MAIN_INST';
                            ?>
                                <tr class="inst-row">
                                    <td style="padding-left: 40px; color: #2980b9;">🎓 <strong>Akreditimi Institucional (Qendror)</strong></td>
                                    <td>
                                        <small style="color: gray;">Nga:</small> <?php echo $inst['data_e_fundit']; ?> <br>
                                        <small style="color: gray;">Deri:</small> <strong><?php echo $inst['vlefshme_deri']; ?></strong>
                                        <?php echo gjeneroBadgeSkadimi($inst['vlefshme_deri']); ?>
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
                                                    <form action="index.php" method="POST" style="display:inline;" onsubmit="return konfirmoFshirjen(this);">
                                                        <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                                        <input type="hidden" name="fakulteti" value="MAIN_INST">
                                                        <input type="hidden" name="fshi_pdf" value="<?php echo htmlspecialchars($pdf); ?>">
                                                        <input type="hidden" name="statusi_pas_fshirjes" value="">
                                                        <button type="submit" class="btn-fshi" title="Fshi Dokumentin">X</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-aksion btn-edit" onclick="editoAkreditimin('<?php echo $uni_fak_id; ?>', '<?php echo $inst['statusi']; ?>', '<?php echo $inst['data_e_fundit']; ?>', '<?php echo $inst['vlefshme_deri']; ?>')">✏️ Edit</button>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($data['fakultetet'] as $fak_emri => $fak_data): 
                                $klasa_statusit = merrKlasenEStatusit($fak_data['statusi']);
                                $uni_fak_id = htmlspecialchars($uni) . '||' . htmlspecialchars($fak_emri);
                            ?>
                                <tr>
                                    <td style="padding-left: 40px;">↳ <strong><?php echo htmlspecialchars($fak_emri); ?></strong></td>
                                    <td>
                                        <small style="color: gray;">Nga:</small> <?php echo $fak_data['data_e_fundit']; ?> <br>
                                        <small style="color: gray;">Deri:</small> <strong><?php echo $fak_data['vlefshme_deri']; ?></strong>
                                        <?php echo gjeneroBadgeSkadimi($fak_data['vlefshme_deri']); ?>
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
                                                    <form action="index.php" method="POST" style="display:inline;" onsubmit="return konfirmoFshirjen(this);">
                                                        <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                                        <input type="hidden" name="fakulteti" value="<?php echo htmlspecialchars($fak_emri); ?>">
                                                        <input type="hidden" name="fshi_pdf" value="<?php echo htmlspecialchars($pdf); ?>">
                                                        <input type="hidden" name="statusi_pas_fshirjes" value="">
                                                        <button type="submit" class="btn-fshi" title="Fshi Dokumentin">X</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-aksion btn-edit" onclick="editoAkreditimin('<?php echo $uni_fak_id; ?>', '<?php echo $fak_data['statusi']; ?>', '<?php echo $fak_data['data_e_fundit']; ?>', '<?php echo $fak_data['vlefshme_deri']; ?>')">✏️ Edit</button>
                                        
                                        <form action="index.php" method="POST" style="display:inline;" onsubmit="return konfirmoFshirjenDosjes('<?php echo htmlspecialchars($fak_emri); ?>');">
                                            <input type="hidden" name="universiteti" value="<?php echo htmlspecialchars($uni); ?>">
                                            <input type="hidden" name="fakulteti" value="<?php echo htmlspecialchars($fak_emri); ?>">
                                            <input type="hidden" name="fshi_direktorin" value="1">
                                            <button type="submit" class="btn-aksion btn-delete-folder" title="Fshi Fakultetin">🗑️ Fshi</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>