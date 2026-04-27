<?php
// 1. Thërrasim header-in dhe navigimin publik (Pika e strukturës dhe Include)
require_once '../includes/header.php';

// 2. MULTIDIMENSIONAL ARRAY - Të dhënat fiktive të kërkuara nga profesori (Puna jote)
$lista_publike_akreditimeve = [
    [
        "universiteti" => "Universiteti i Prishtinës",
        "fakulteti" => "Fakulteti Ekonomik",
        "viti_themelimit" => 1960,
        "statusi" => "I Akredituar",
        "vlefshme_deri" => "2028-10-01"
    ],
    [
        "universiteti" => "Kolegji AAB",
        "fakulteti" => "Shkenca Kompjuterike",
        "viti_themelimit" => 2002,
        "statusi" => "I Akredituar",
        "vlefshme_deri" => "2026-09-01"
    ],
    [
        "universiteti" => "UBT",
        "fakulteti" => "Arkitekturë",
        "viti_themelimit" => 2001,
        "statusi" => "Në Shqyrtim",
        "vlefshme_deri" => "N/A"
    ],
    [
        "universiteti" => "Universiteti i Prizrenit",
        "fakulteti" => "Juridik",
        "viti_themelimit" => 2010,
        "statusi" => "Refuzuar",
        "vlefshme_deri" => "2023-01-01"
    ]
];

// 3. Përdorimi i Funksioneve (Numëruesi dhe formatimi i tekstit)
$total_regjistrime = count($lista_publike_akreditimeve);
$viti_aktual = date("Y");
?>

<div class="container" style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <h1 style="color: #2c3e50; margin-bottom: 10px;">Regjistri Publik i Institucioneve</h1>
    <p style="color: #7f8c8d; margin-bottom: 30px;">
        Gjetur <strong><?php echo $total_regjistrime; ?></strong> rezultate (Përditësuar: <?php echo $viti_aktual; ?>)
    </p>

    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
        <thead style="background-color: #34495e; color: white; text-align: left;">
            <tr>
                <th style="padding: 15px;">Institucioni</th>
                <th style="padding: 15px;">Fakulteti</th>
                <th style="padding: 15px;">Vjetërsia</th>
                <th style="padding: 15px;">Statusi i Akreditimit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_publike_akreditimeve as $inst): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px; font-weight: bold; color: #2c3e50;">
                        <?php echo strtoupper($inst['universiteti']); // Funksion stringu ?>
                    </td>
                    <td style="padding: 15px;">
                        <?php echo $inst['fakulteti']; ?>
                    </td>
                    <td style="padding: 15px;">
                        <?php 
                            // Funksion matematikor i thjeshtë: Viti aktual - Viti i themelimit
                            $vjetersia = $viti_aktual - $inst['viti_themelimit'];
                            echo $vjetersia . " vite";
                        ?>
                    </td>
                    <td style="padding: 15px;">
                        <?php 
                            // Logjikë me IF/ELSE për ngjyrat bazuar te statusi
                            if ($inst['statusi'] == 'I Akredituar') {
                                echo '<span style="background: #2ecc71; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;">✅ I Akredituar</span>';
                            } elseif ($inst['statusi'] == 'Në Shqyrtim') {
                                echo '<span style="background: #f1c40f; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;">⏳ Në Shqyrtim</span>';
                            } else {
                                echo '<span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;">❌ Refuzuar</span>';
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
require_once '../includes/footer.php'; 
?>