<?php
$emri_projektit = "Agjencia e Kosovës për Akreditim (AKA)";
$viti_aktual = date("Y");
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimi i Serverit</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
            background-color: #f4f4f9;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: inline-block;
        }
        h1 { color: #2c3e50; }
        .sukses { 
            color: #27ae60; 
            font-size: 20px;
            font-weight: bold; 
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Mirësevini në projektin: <br> <?php echo $emri_projektit; ?></h1>
        
        <p>Nëse po e lexoni këtë tekst me ngjyra dhe pa asnjë kod të çuditshëm në ekran, kjo do të thotë që:</p>
        <p class="sukses">🚀 XAMPP dhe PHP po funksionojnë në mënyrë perfekte!</p>
        
        <hr>
        <p><small>Të drejtat e rezervuara &copy; <?php echo $viti_aktual; ?> - Faza e Parë e Projektit</small></p>
    </div>

</body>
</html>