<?php
require_once '../authentication.php';
kontrolloQasjen('admin');

if(empty($_SESSION['emri'])) { $_SESSION['emri'] = 'Admin'; $_SESSION['roli'] = 'admin'; }

$akreditimet_dir = '../../../Akreditimet/';
$mesazhi = '';
$requests_dir = '../../../Akreditimet/Requests/';
// --- 1. LOGJIKA E FSHIRJES DHE PËRDITËSIMIT TË STATUSIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fshi_pdf']) && isset($_POST['fakulteti']) && isset($_POST['universiteti'])) {
    $pdf_per_fshirje = basename($_POST['fshi_pdf']);
    $uni_fshirje = str_replace(['.', '/', '\\'], '', $_POST['universiteti']);
    $fakulteti_fshirje = str_replace(['.', '/', '\\'], '', $_POST['fakulteti']);
    $statusi_pas_fshirjes = $_POST['statusi_pas_fshirjes'] ?? 'Refuzuar';
    
    // Gjejmë rrugën e saktë të skedarit
    if ($fakulteti_fshirje === 'MAIN_INST') {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/';
    } else {
        $folder_path = $akreditimet_dir . $uni_fshirje . '/' . $fakulteti_fshirje . '/';
    }
    
    $file_path = $folder_path . $pdf_per_fshirje;
    
    if (file_exists($file_path) && unlink($file_path)) {
        // Përditësojmë config.txt me statusin e ri pas fshirjes
        $config_path = $folder_path . 'config.txt';
        $data_fundit = 'N/A';
        $vlefshme = 'N/A';
        
        // Nxjerrim datat e vjetra nëse ekzistojnë
        if(file_exists($config_path)) {
            $config_vjeter = parse_ini_file($config_path);
            if($config_vjeter) {
                $data_fundit = $config_vjeter['data_e_fundit'] ?? 'N/A';
                $vlefshme = $config_vjeter['vlefshme_deri'] ?? 'N/A';
            }
        }
        
        // Ruajmë config-un e ri
        $config_content = "statusi=\"$statusi_pas_fshirjes\"\ndata_e_fundit=\"$data_fundit\"\nvlefshme_deri=\"$vlefshme\"\n";
        file_put_contents($config_path, $config_content);
        
        $mesazhi = "<div class='alert success'>Dokumenti <strong>$pdf_per_fshirje</strong> u fshi dhe statusi u ndryshua në: <strong>$statusi_pas_fshirjes</strong>!</div>";
    } else {
        $mesazhi = "<div class='alert error'>Gabim: Nuk u mund të fshihej dokumenti.</div>";
    }
}

// --- 2. LOGJIKA E UPLOADIT DHE PËRDITËSIMIT TË AKREDITIMIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file']) && isset($_POST['target_uni_fak'])) {
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

        $file_name = basename($_FILES["pdf_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($file_type != "pdf") {
            $mesazhi = "<div class='alert error'>Gabim: Vetëm fajllat PDF lejohen!</div>";
        } else {
            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                // KRIJIMI OSE PËRDITËSIMI I SKEDARIT TË KONFIGURIMIT
                $config_content = "statusi=\"$statusi_ri\"\ndata_e_fundit=\"$data_nga\"\nvlefshme_deri=\"$data_deri\"\n";
                file_put_contents($target_dir . 'config.txt', $config_content);
                
                $mesazhi = "<div class='alert success'>Dokumenti u ngarkua dhe akreditimi u përditësua për <strong>$emri_per_mesazh</strong>!</div>";
            } else {
                $mesazhi = "<div class='alert error'>Pati një gabim gjatë ngarkimit të fajllit.</div>";
            }
        }
    }
}

// --- VARIABLAT PËR STATISTIKAT ---
$stats_institucione = 0;
$stats_aprovuar = 0;
$stats_ne_shqyrtim = 0;
$stats_skadojne = 0;

// Funksion i vogël për llogaritjen e datave dhe statusit
function analizoStatistikat($statusi, $data_deri) {
    global $stats_aprovuar, $stats_ne_shqyrtim, $stats_skadojne;
    $st = strtolower(trim($statusi));
    
    if ($st == 'aprovuar') $stats_aprovuar++;
    if ($st == 'në shqyrtim' || $st == 'ne shqyrtim') $stats_ne_shqyrtim++;
    
    if ($data_deri !== 'N/A' && !empty($data_deri)) {
        $koha_deri = strtotime($data_deri);
        if ($koha_deri) {
            $ditet = ($koha_deri - time()) / 86400; // 86400 sekonda = 1 ditë
            if ($ditet <= 30) {
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

// --- 3. LEXIMI I TË DHËNAVE NGA FOLDERAT ---
$te_gjitha_institucionet = [];

if (is_dir($akreditimet_dir)) {
    $universitetet = scandir($akreditimet_dir);
    foreach ($universitetet as $uni) {
        if ($uni !== '.' && $uni !== '..' && is_dir($akreditimet_dir . $uni)) {
            $stats_institucione++; // Rrisim numrin e institucioneve
            
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

// Funksion për të përcaktuar klasën CSS të statusit
function merrKlasenEStatusit($statusi) {
    $st = strtolower(trim($statusi));
    if ($st == 'aprovuar') return 'aprovuar';
    if ($st == 'refuzuar') return 'refuzuar';
    if ($st == 'e paakredituar' || $st == 'epaakredituar') return 'e-paakredituar';
    return 'ne-shqyrtim';
}
// LOGJIKA E PROCESIMIT TË KËRKESAVE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uni = str_replace(['.', '/', '\\'], '', $_POST['uni'] ?? '');
    $fak = str_replace(['.', '/', '\\'], '', $_POST['fak'] ?? '');
    $file = basename($_POST['file'] ?? '');
    $source = $requests_dir . "$uni/$fak/$file";

    // --- APROVIMI ---
    if (isset($_POST['aprovo_kerkesen'])) {
        $data_nga = $_POST['data_fillimi'] ?? date('Y-m-d');
        $data_deri = $_POST['data_mbarimi'] ?? date('Y-m-d', strtotime('+3 years'));
        
        $dest_folder = $akreditimet_dir . "$uni/$fak/";
        if (!is_dir($dest_folder)) mkdir($dest_folder, 0777, true);

        if (file_exists($source) && rename($source, $dest_folder . $file)) {
            $config_content = "statusi=\"Aprovuar\"\ndata_e_fundit=\"$data_nga\"\nvlefshme_deri=\"$data_deri\"\n";
            file_put_contents($dest_folder . 'config.txt', $config_content);
            @unlink($requests_dir . "$uni/$fak/status_request.json");
            $mesazhi = "<div class='alert success'>Programi u aprovua me sukes (Vlefshmëria: $data_nga deri $data_deri)!</div>";
        }
    } 

    // --- REFUZIMI ---
    if (isset($_POST['refuzo_kerkesen'])) {
        if (file_exists($source)) {
            // Opsionale: Mund ta fshini fajllin ose ta zhvendosni në një folder 'Archive/Refused'
            unlink($source); 
            @unlink($requests_dir . "$uni/$fak/status_request.json");
            $mesazhi = "<div class='alert error'>Kërkesa u refuzua dhe u fshi nga sistemi.</div>";
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

        /* Karta e Statistikave */
        .stats-grid { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-bottom: 4px solid #bdc3c7; text-align: center;}
        .stat-card h3 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; }
        .stat-card p { color: #7f8c8d; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .stat-card.blue { border-bottom-color: #3498db; }
        .stat-card.green { border-bottom-color: #2ecc71; }
        .stat-card.yellow { border-bottom-color: #f1c40f; }
        .stat-card.red { border-bottom-color: #e74c3c; }

        .upload-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid #9b59b6;widht:500px }
        .upload-row { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; align-items: center; }
        .upload-card select, .upload-card input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #3498db; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #2980b9; }

        .btn-fshi { background: #e74c3c; color: white; border: none; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 11px; margin-left: 5px; }
        .btn-fshi:hover { background: #c0392b; }

        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .tabela-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tabela-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .search-input { padding: 8px 15px; border: 1px solid #ccc; border-radius: 20px; width: 300px; outline: none; transition: 0.3s; }
        .search-input:focus { border-color: #3498db; box-shadow: 0 0 5px rgba(52,152,219,0.3); }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: top;}
        th { background-color: #f8f9fa; color: #333; font-weight: 600; }
        tr:hover { background-color: #f1f5f8; }
        
        .status { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status.aprovuar { background: #d4edda; color: #155724; }
        .status.refuzuar { background: #f8d7da; color: #721c24; }
        .status.ne-shqyrtim { background: #fff3cd; color: #856404; }
        .status.e-paakredituar { background: #e2e3e5; color: #383d41; } 
        
        .pdf-link { color: #2c3e50; text-decoration: none; font-size: 13px; font-weight: bold; }
        .pdf-item { display: flex; align-items: center; margin-bottom: 5px; background: #f1f2f6; padding: 5px 8px; border-radius: 4px; width: fit-content; }
        .uni-header { background: #eef2f5; font-weight: bold; color: #2c3e50; }
        .inst-row { background-color: #fdfefe; }
        .inst-row td { border-bottom: 2px solid #ecf0f1; }

        /* E bën kokën e tabelës të qëndrojë lart kur bën scroll */
        th { 
            position: sticky; 
            top: 0; 
            z-index: 11; 
            background-color: #f8f9fa; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* E bën rreshtin e Universitetit (psh. UBT) të rrijë fiks poshtë kokës së tabelës */
        .uni-header td { 
            position: sticky; 
            top: 42px; /* Përshtatet me lartësinë e <th> */
            z-index: 10; 
            background: #eef2f5; /* Sigurohet që të mos jetë transparente */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
    </style>
    <script>
        function konfirmoFshirjen(form) {
            if (confirm("Kujdes! A jeni të sigurt që dëshironi të fshini këtë dokument?")) {
                let statusiRi = prompt(
                    "Cili do të jetë statusi i ri i këtij institucioni/fakulteti pas fshirjes?\n\n" +
                    "Shkruaj numrin:\n" +
                    "1 - Aprovuar\n" +
                    "2 - Refuzuar\n" +
                    "3 - Në shqyrtim\n" +
                    "4 - E paakredituar", 
                    "4"
                );
                
                let vlera = "E paakredituar"; 
                if (statusiRi === "1") vlera = "Aprovuar";
                if (statusiRi === "2") vlera = "Refuzuar";
                if (statusiRi === "3") vlera = "Në shqyrtim";
                if (statusiRi === "4") vlera = "E paakredituar";
                
                form.statusi_pas_fshirjes.value = vlera;
                return true;
            }
            return false;
        }

        // Funksioni për Kërkim Live (I Përmirësuar me Grupim)
        function filtroTabelen() {
            let input = document.getElementById("kerkimLive").value.toLowerCase();
            let table = document.getElementById("tabelaAkreditimeve");
            let tbody = table.querySelector("tbody");
            let trs = tbody.querySelectorAll("tr");

            let currentHeader = null;
            let groupRows = [];

            // Funksion i brendshëm për të kontrolluar një universitet dhe fakultetet e tij
            function processGroup(header, rows) {
                if (!header) return;
                
                let headerText = header.textContent.toLowerCase();
                let headerMatches = headerText.includes(input);
                let anyRowMatches = false;

                rows.forEach(row => {
                    let rowText = row.textContent.toLowerCase();
                    // Nëse vetë Universiteti përmban fjalën e kërkuar, i tregojmë të gjitha fakultetet e tij.
                    // OSE nëse fakulteti e përmban fjalën e kërkuar, e tregojmë.
                    if (headerMatches || rowText.includes(input)) {
                        row.style.display = "";
                        anyRowMatches = true;
                    } else {
                        row.style.display = "none";
                    }
                });

                // Trego Universititetin (Header) nëse ka përputhje tek emri i tij, ose tek ndonjë nga fakultetet
                if (headerMatches || anyRowMatches) {
                    header.style.display = "";
                } else {
                    header.style.display = "none";
                }
            }

            for (let i = 0; i < trs.length; i++) {
                let tr = trs[i];
                
                // Kapërce rreshtin e zbrazët "Nuk u gjet asnjë..."
                if (tr.children.length === 1 && tr.textContent.includes("Nuk u gjet")) {
                    continue; 
                }

                if (tr.classList.contains('uni-header')) {
                    // Së pari, përpuno universitetin e mëparshëm që kishim në memorie
                    processGroup(currentHeader, groupRows);
                    
                    // Fillo grumbullimin për universitetin e ri
                    currentHeader = tr;
                    groupRows = [];
                } else {
                    // Shto fakultetin/institucionin tek grupi aktual
                    groupRows.push(tr);
                }
            }
            
            // Sigurohu që të përpunosh edhe grupin e fundit të universitetit në fund të tabelës
            processGroup(currentHeader, groupRows);
        }
    </script>
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

        <div class="stats-grid">
            <div class="stat-card blue">
                <h3><?php echo $stats_institucione; ?></h3>
                <p>Institucione në Total</p>
            </div>
            <div class="stat-card green">
                <h3><?php echo $stats_aprovuar; ?></h3>
                <p>Programe / Inst. të Aprovuara</p>
            </div>
            <div class="stat-card yellow">
                <h3><?php echo $stats_ne_shqyrtim; ?></h3>
                <p>Në Shqyrtim</p>
            </div>
            <div class="stat-card red">
                <h3><?php echo $stats_skadojne; ?></h3>
                <p>Në rrezik Skadimi (< 30 ditë)</p>
            </div>
        </div>

        <div class="upload-card">
            <h3 style="color: #9b59b6; margin-bottom: 15px;">Shto dokument dhe përditëso akreditimin</h3>
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
                    
                    <input type="file" name="pdf_file" accept=".pdf" required style="width: 250px;">
                </div>
                
                <div class="upload-row">
                    <select name="statusi_ri" required>
                        <option value="" disabled selected>2. Statusi i ri...</option>
                        <option value="Aprovuar">✅ Aprovuar</option>
                        <option value="Refuzuar">❌ Refuzuar</option>
                        <option value="Në shqyrtim">⏳ Në shqyrtim</option>
                        <option value="E paakredituar">🚫 E paakredituar</option>
                    </select>
                    
                    <span style="font-size: 14px; color: #555;">E vlefshme nga:</span>
                    <input type="date" name="data_nga" required title="Data e fillimit të akreditimit">
                    
                    <span style="font-size: 14px; color: #555;">Deri më:</span>
                    <input type="date" name="data_deri" required title="Data e përfundimit">
                    
                    <button type="submit" class="btn">Ngarko & Përditëso</button>
                </div>
            </form>
                <div class="tabela-container" style="margin-top: 30px;margin-bottom:150px; border-top: 4px solid #f1c40f;">
    <h3 style="margin-bottom:15px;">📥 Kërkesat e Reja për Shqyrtim</h3>
    <table>
        <thead>
            <tr>
                <th>Institucioni / Fakulteti</th>
                <th>Programi / Dokumenti</th>
                <th>Cakto Vlefshmërinë</th>
                <th>Veprime</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (is_dir($requests_dir)) {
                $universitetet_req = scandir($requests_dir);
                foreach ($universitetet_req as $uni_folder) {
                    if ($uni_folder == '.' || $uni_folder == '..') continue;
                    
                    $fakultetet_req = scandir($requests_dir . $uni_folder);
                    foreach ($fakultetet_req as $fak_folder) {
                        if ($fak_folder == '.' || $fak_folder == '..') continue;
                        
                        $json_path = $requests_dir . "$uni_folder/$fak_folder/status_request.json";
                        
                        if (file_exists($json_path)) {
                            $req_data = json_decode(file_get_contents($json_path), true);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($uni_folder . " / " . $fak_folder); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($req_data['programi']); ?></strong><br>
                                    <a href="<?php echo $requests_dir . $uni_folder . '/' . $fak_folder . '/' . $req_data['file']; ?>" target="_blank" style="font-size: 11px; color: #3498db;">📄 Shiko Dokumentin</a>
                                </td>
                                
                                <form method="POST">
                                    <input type="hidden" name="uni" value="<?php echo htmlspecialchars($uni_folder); ?>">
                                    <input type="hidden" name="fak" value="<?php echo htmlspecialchars($fak_folder); ?>">
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($req_data['file']); ?>">
                                    
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 5px;">
                                            <small>Nga:</small>
                                            <input type="date" name="data_fillimi" value="<?php echo date('Y-m-d'); ?>" required style="font-size: 12px;">
                                            <small>Deri:</small>
                                            <input type="date" name="data_mbarimi" required style="font-size: 12px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 5px;">
                                            <button type="submit" name="aprovo_kerkesen" class="btn" style="background:#27ae60; padding: 5px; font-size: 11px;">Aprovo</button>
                                            <button type="submit" name="refuzo_kerkesen" class="btn" style="background:#e74c3c; padding: 5px; font-size: 11px;" onclick="return confirm('A jeni të sigurt?')">Refuzo</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                            <?php
                        }
                    }
                }
            } else {
                echo "<tr><td colspan='4'>Nuk ka folder për kërkesa.</td></tr>";
            }
            ?>
        </tbody>
    </table>
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
                                $klasa_statusit = merrKlasenEStatusit($inst['statusi']);
                            ?>
                                <tr class="inst-row">
                                    <td style="padding-left: 40px; color: #2980b9;">🎓 <strong>Akreditimi Institucional (Qendror)</strong></td>
                                    <td>
                                        <small style="color: gray;">Nga:</small> <?php echo $inst['data_e_fundit']; ?> <br>
                                        <small style="color: gray;">Deri:</small> <strong><?php echo $inst['vlefshme_deri']; ?></strong>
                                        <?php echo gjeneroBadgeSkadimi($inst['vlefshme_deri']); // ALARMI I SKADIMIT ?>
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
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($data['fakultetet'] as $fak_emri => $fak_data): 
                                $klasa_statusit = merrKlasenEStatusit($fak_data['statusi']);
                            ?>
                                <tr>
                                    <td style="padding-left: 40px;">↳ <strong><?php echo htmlspecialchars($fak_emri); ?></strong></td>
                                    <td>
                                        <small style="color: gray;">Nga:</small> <?php echo $fak_data['data_e_fundit']; ?> <br>
                                        <small style="color: gray;">Deri:</small> <strong><?php echo $fak_data['vlefshme_deri']; ?></strong>
                                        <?php echo gjeneroBadgeSkadimi($fak_data['vlefshme_deri']); // ALARMI I SKADIMIT ?>
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