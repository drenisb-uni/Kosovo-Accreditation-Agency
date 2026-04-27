<?php

include_once '../includes/header.php';
$basePath = "../Akreditimet";
$institucionet = [];

if (is_dir($basePath)) {
    // 1. Lexo folderat e institucioneve (UP, UBT, AAB...)
    $folders = array_diff(scandir($basePath), array('..', '.'));
    $idCounter = 1;

    foreach ($folders as $instFolder) {
        $instPath = $basePath . '/' . $instFolder;
        if (is_dir($instPath)) {
            $faculties = [];
            
            // 2. Kontrollojmë nëse ka nën-foldera (Fakultete) apo skedarë direkt
            $subItems = array_diff(scandir($instPath), array('..', '.'));
            
            $hasSubFolders = false;
            foreach ($subItems as $item) {
                if (is_dir($instPath . '/' . $item)) {
                    $hasSubFolders = true;
                    // Nëse është folder, e trajtojmë si Fakultet
                    $docs = [];
                    $files = array_diff(scandir($instPath . '/' . $item), array('..', '.'));
                    foreach ($files as $f) {
                        $type = (strpos(strtolower($f), 'raport') !== false) ? 'report' : 'decision';
                        $docs[] = [
                            "name" => $f,
                            "url" => $instPath . '/' . $item . '/' . $f,
                            "type" => $type,
                            "date" => date("d.m.Y", filemtime($instPath . '/' . $item . '/' . $f))
                        ];
                    }
                    $faculties[] = ["id" => uniqid(), "name" => $item, "documents" => $docs];
                }
            }

            // 3. Nëse institucioni NUK ka nën-foldera (skedarët janë direkt te emri i universitetit)
            if (!$hasSubFolders) {
                $docs = [];
                foreach ($subItems as $f) {
                    if (is_file($instPath . '/' . $f)) {
                        $type = (strpos(strtolower($f), 'raport') !== false) ? 'report' : 'decision';
                        $docs[] = [
                            "name" => $f,
                            "url" => $instPath . '/' . $f,
                            "type" => $type,
                            "date" => date("d.m.Y", filemtime($instPath . '/' . $f))
                        ];
                    }
                }
                if (!empty($docs)) {
                    $faculties[] = ["id" => uniqid(), "name" => "Dokumentet e Përgjithshme", "documents" => $docs];
                }
            }

            // Shtimi në listë
            $institucionet[] = [
                "id" => $idCounter++,
                "name" => $instFolder,
                "city" => "Kosovë", 
                "type" => (stristr($instFolder, 'Universiteti') && !stristr($instFolder, 'UBT') && !stristr($instFolder, 'AAB')) ? 'PUBLIC' : 'PRIVATE',
                "status" => "AKREDITUAR",
                "validityYear" => "2024-2029",
                "validityStatus" => "I Validuar",
                "sector" => (stristr($instFolder, 'Universiteti')) ? 'Sektori Publik' : 'Sektori Privat',
                "faculties" => $faculties
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKA - Regjistri i Akreditimit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal-open { overflow: hidden; }
        .accordion-content { display: none; }
        .accordion-content.active { display: block; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-gray-800">

    <div class="w-full text-center pt-12 pb-8 px-4">
        <h2 class="text-3xl md:text-5xl font-bold text-blue-900 mb-4">Akreditimet</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">Kërkoni dhe verifikoni statusin e akreditimit.</p>
    </div>

    <div class="w-full px-6 md:px-16 mb-8">
        <div class="bg-white p-4 rounded-2xl shadow-lg border border-gray-100 flex flex-col lg:flex-row gap-4">
            <input type="text" id="searchInput" placeholder="Kërko institucionin..." class="flex-1 p-3 bg-gray-50 rounded-xl outline-none border focus:border-blue-300">
            <div class="flex gap-2">
                <button onclick="filterType('ALL')" class="filter-btn px-6 py-2 rounded-lg font-bold text-sm bg-blue-900 text-white">Të Gjitha</button>
                <button onclick="filterType('PUBLIC')" class="filter-btn px-6 py-2 rounded-lg font-bold text-sm bg-gray-100 text-gray-600">Publike</button>
                <button onclick="filterType('PRIVATE')" class="filter-btn px-6 py-2 rounded-lg font-bold text-sm bg-gray-100 text-gray-600">Private</button>
            </div>
        </div>
    </div>

    <div class="w-full px-6 md:px-16 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="instGrid">
            <?php foreach ($institucionet as $inst): ?>
                <div class="inst-card bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all" 
                     data-type="<?php echo $inst['type']; ?>" 
                     data-name="<?php echo strtolower($inst['name']); ?>">
                    <div class="flex justify-between mb-4">
                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-blue-50 text-blue-800"><?php echo $inst['type']; ?></span>
                        <span class="text-emerald-500">✔</span>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 mb-4 h-12 overflow-hidden"><?php echo $inst['name']; ?></h3>
                    <button onclick='openModal(<?php echo json_encode($inst); ?>)' 
                            class="w-full py-2 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:border-blue-500 hover:text-blue-700">
                        SHIKO FAKULTETET
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="instModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-5xl h-[90vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col relative">
            <div class="p-6 flex justify-between items-center border-b">
                <h2 id="modalTitle" class="text-2xl font-bold text-blue-900"></h2>
                <button onclick="closeModal()" class="p-2 bg-gray-50 hover:bg-red-50 rounded-full">✕</button>
            </div>
            <div id="modalContent" class="flex-1 overflow-y-auto p-8 bg-slate-50">
                </div>
        </div>
    </div>

    <script>
        // Logjika e filtrimit
        function filterType(type) {
            const cards = document.querySelectorAll('.inst-card');
            cards.forEach(card => {
                if(type === 'ALL' || card.dataset.type === type) card.style.display = 'block';
                else card.style.display = 'none';
            });
        }

        // Search logjika
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.inst-card').forEach(card => {
                card.style.display = card.dataset.name.includes(val) ? 'block' : 'none';
            });
        });

        // Modal Logjika
        function openModal(inst) {
            const modal = document.getElementById('instModal');
            document.getElementById('modalTitle').innerText = inst.name;
            const content = document.getElementById('modalContent');
            
            let facultiesHtml = inst.faculties.map(fac => `
                <div class="bg-white mb-3 rounded-lg border border-gray-100 overflow-hidden">
                    <button onclick="this.nextElementSibling.classList.toggle('active')" class="w-full flex justify-between p-4 bg-gray-50 hover:bg-white font-bold text-blue-900">
                        ${fac.name} <span>+</span>
                    </button>
                    <div class="accordion-content p-4 border-t">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                                <p class="font-bold text-sm text-blue-900 mb-2">Vendimet</p>
                                ${fac.documents.filter(d => d.type === 'decision').map(d => `<a href="${d.url}" target="_blank" class="block text-xs py-1 text-blue-700 hover:underline">📄 ${d.name}</a>`).join('')}
                            </div>
                            <div class="bg-amber-50 p-3 rounded-xl border border-amber-100">
                                <p class="font-bold text-sm text-amber-900 mb-2">Raportet</p>
                                ${fac.documents.filter(d => d.type === 'report').map(d => `<a href="${d.url}" target="_blank" class="block text-xs py-1 text-amber-700 hover:underline">👁 ${d.name}</a>`).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            content.innerHTML = `
                <div class="bg-white p-6 rounded-xl border mb-6 flex justify-between items-center">
                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase">Statusi</p>
                        <span class="text-3xl font-bold text-blue-900">${inst.validityYear}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-500 font-bold uppercase">Sektori</p>
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded font-bold">${inst.sector}</span>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-4">Fakultetet</h3>
                ${facultiesHtml}
            `;
            
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            document.getElementById('instModal').style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    </script>
</body>
</html>
<?php include_once '../includes/footer.php'; ?>
