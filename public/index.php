<?php
include_once '../includes/header.php'; 

// Të 7 pikat e videos
$pikat_e_videos = [
    ["pyetja" => "Cilësia në arsim fillon me ty.", "koha" => 0.00],
    ["pyetja" => "Çfarë është AKA?", "koha" => 0.15],
    ["pyetja" => "Si mund të qasemi në AKA?", "koha" => 0.45],
    ["pyetja" => "Ruajtja e emailit si dëshmi.", "koha" => 1.05],
    ["pyetja" => "Si mund të kontribuoni në përmirësimin e cilësisë?", "koha" => 1.30],
    ["pyetja" => "Mos përfshirja në vendimmarrje.", "koha" => 2.00],
    ["pyetja" => "Siguria dhe anonimiteti juaj.", "koha" => 2.30],
];
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>AKA - Ballina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Containeri kryesor i videos */
        .video-hero { 
            position: relative; 
            width: 100vw; 
            height: 100vh; 
            background: #000; 
            overflow: hidden; 
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
        }

        #akaVideo {
            width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.85;
        }

        /* Menuja majtas */
        .sidebar-menu {
            position: absolute;
            top: 50%; left: 5%;
            transform: translateY(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 450px;
        }

        .pyetja-item {
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.5rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .pyetja-item:hover, .pyetja-item.active {
            opacity: 1;
            color: #ffffff;
            transform: translateX(8px);
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        /* Butonat lart djathtas */
        .video-controls {
            position: absolute;
            top: 30px;
            right: 5%;
            z-index: 20;
            display: flex;
            gap: 15px;
        }

        /* Ndalo scrollin horizontal te html/body per shkak te 100vw */
        body, html { overflow-x: hidden; margin: 0; padding: 0; }
    </style>
</head>
<body class="bg-white font-sans text-gray-800">

    <div class="video-hero">
        <video id="akaVideo" autoplay muted playsinline loop>
            <source src="../assets/AKA-video.mp4" type="video/mp4">
        </video>

        <div class="sidebar-menu">
            <?php foreach ($pikat_e_videos as $idx => $pika): ?>
                <div class="pyetja-item <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="navigoVideon(<?php echo $pika['koha']; ?>, this)">
                    <?php echo $pika['pyetja']; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="video-controls">
            <button onclick="toggleSound()" class="flex items-center gap-2 bg-white/20 backdrop-blur-md text-white px-6 py-2.5 rounded-full text-sm font-bold tracking-wider hover:bg-white/30 transition shadow-sm border border-white/10">
                <span id="vol-icon">🔇</span> <span id="vol-text">PA ZË</span>
            </button>
            <button onclick="mbyllVideon()" class="flex items-center gap-2 bg-[#2563eb] text-white px-6 py-2.5 rounded-full text-sm font-bold tracking-wider hover:bg-blue-700 transition shadow-lg">
                ✕ MBYLL
            </button>
        </div>
    </div>

    <div class="relative z-10 w-full bg-slate-50 border-t border-gray-200">
        
        <div class="bg-white py-10 text-center w-full shadow-sm">
            <h1 class="text-3xl md:text-4xl font-extrabold text-[#1e3a8a] uppercase tracking-wide">
                AGJENCIA E KOSOVËS PËR AKREDITIM
            </h1>
        </div>

        <div class="py-12 px-4 md:px-8">
            <main class="max-w-6xl mx-auto bg-white rounded-[2rem] shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-8 md:p-12">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                    
                    <aside class="md:col-span-4 flex flex-col gap-6">
                        <div class="bg-[#2563eb] text-white p-7 rounded-2xl shadow-lg">
                            <h3 class="text-xl font-bold italic mb-4">"Cilësia në arsim fillon me ty"</h3>
                            <p class="text-blue-100 text-sm leading-relaxed">
                                AKA garanton që institucionet e arsimit të lartë në Kosovë plotësojnë standardet ndërkombëtare për kërkim dhe mësimdhënie.
                            </p>
                        </div>

                        <div class="bg-[#f8fafc] p-7 rounded-2xl border border-gray-100">
                            <h3 class="text-xl font-semibold text-blue-600 mb-4">Baza Ligjore</h3>
                            <ul class="text-gray-500 space-y-3 font-medium text-sm">
                                <li class="hover:text-blue-600 transition-colors cursor-pointer">• Ligji për AKA-në</li>
                                <li class="hover:text-blue-600 transition-colors cursor-pointer">• Ligji për Arsimin e Lartë</li>
                                <li class="hover:text-blue-600 transition-colors cursor-pointer">• Udhëzimet Administrative</li>
                            </ul>
                        </div>
                    </aside>

                    <section class="md:col-span-8 flex flex-col gap-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-[#1e40af]">Agjencia e Kosovës për Akreditim – AKA</h2>
                        <p class="text-gray-600 leading-relaxed text-lg mb-2">
                            Agjencia e Kosovës për Akreditim, konform Ligjit për AKA-në dhe Ligjit për Arsimin e Lartë, është institucion i pavarur, përgjegjës për sigurimin e brendshëm dhe të jashtëm të cilësisë.
                        </p>

                        <div class="bg-[#f0fdf4] bg-opacity-0 bg-gradient-to-r from-blue-50 to-white rounded-2xl p-8 border-l-[3px] border-[#3b82f6]">
                            <h3 class="text-xl font-semibold text-[#2563eb] mb-4">Misioni dhe Përgjegjësitë</h3>
                            <p class="text-gray-600 leading-relaxed mb-6">
                                AKA është përgjegjëse për të gjitha proceset e sigurimit të cilësisë në institucionet e arsimit të lartë dhe programet e tyre të studimit në Republikën e Kosovës.
                            </p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-4">
                                <div class="flex items-center gap-3 text-gray-700 font-semibold">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></div> Akreditimin
                                </div>
                                <div class="flex items-center gap-3 text-gray-700 font-semibold">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></div> Riakreditimin
                                </div>
                                <div class="flex items-center gap-3 text-gray-700 font-semibold">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></div> Monitorimin
                                </div>
                                <div class="flex items-center gap-3 text-gray-700 font-semibold">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></div> Validimin
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </main>
        </div>

    </div>

    <script>
        const v = document.getElementById('akaVideo');

        // Funksioni për konvertimin e kohës 1.05 -> 65 sekonda
        function toSeconds(t) {
            const m = Math.floor(t);
            const s = Math.round((t - m) * 100);
            return (m * 60) + s;
        }

        function navigoVideon(koha, el) {
            v.currentTime = toSeconds(koha);
            v.play();
            document.querySelectorAll('.pyetja-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
        }

        function toggleSound() {
            v.muted = !v.muted;
            document.getElementById('vol-icon').innerText = v.muted ? "🔇" : "🔊";
            document.getElementById('vol-text').innerText = v.muted ? "ME ZË" : "PA ZË";
        }

        function mbyllVideon() {
            v.pause();
            // Bën scroll te fillimi i seksionit poshtë
            window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
        }

        window.addEventListener('load', () => {
            v.play().catch(e => console.log("Autoplay kërkon ndërveprim të parë."));
        });
    </script>
</body>
</html>
        <?php include '../includes/footer.php'; ?>
