<?php
require_once '../authentication.php';
kontrolloQasjen('user');
// Supozojmë se në SESSION kemi 'universiteti' dhe 'fakulteti' e userit
$user_uni = $_SESSION['universiteti'] ?? 'UP'; 
$user_fak = $_SESSION['fakulteti'] ?? 'FIEK';

$requests_dir = '../../../Akreditimet/Requests/';
$mesazhi = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['request_pdf'])) {
    $programi = str_replace(['.', '/', '\\'], '', $_POST['emri_programit']);
    $target_dir = $requests_dir . $user_uni . '/' . $user_fak . '/';
    
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_name = time() . "_" . basename($_FILES["request_pdf"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["request_pdf"]["tmp_name"], $target_file)) {
        // Krijojmë një file json/txt për të ruajtur detajet e kërkesës
        $request_info = [
            'programi' => $programi,
            'data_aplikimit' => date('Y-m-d'),
            'statusi' => 'Në Shqyrtim',
            'file' => $file_name
        ];
        file_put_contents($target_dir . 'status_request.json', json_encode($request_info));
        $mesazhi = "<div class='alert success'>Kërkesa për <strong>$programi</strong> u dërgua me sukses!</div>";
    }
}

// Leximi i statusit aktual
$statusi_aktual = "S'ka kërkesë aktive";
$json_path = $requests_dir . $user_uni . '/' . $user_fak . '/status_request.json';
if(file_exists($json_path)) {
    $info = json_decode(file_get_contents($json_path), true);
    $statusi_aktual = $info['statusi'];
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Aplikimi për Akreditim</title>
    <link rel="stylesheet" href="stili_juaj.css">
    <link rel="stylesheet" href="../../../assets/css/style.css">

    <style>
        
        
        .request-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 20px auto; }
        .status-box { text-align: center; padding: 20px; margin-bottom: 20px; border-radius: 8px; background: #ebf5fb; border: 1px solid #3498db; }
        .status-text { font-size: 20px; font-weight: bold; color: #2980b9; }
        #logout {margin:15px 0px;text-align:center;height: 25px;width: 100px;border: 1px solid #2980b9;border-radius: 5px;background-color:#3498db ;float: right;padding-top: 7px;font-weight: bold;}
        #logout:hover{background-color: #003366;}
        #link{text-decoration-line: none;color: #2980b9;}
        #dergoap{font-weight: bold;border-radius:5px;width:175px;height:30px;background-color:#ebf5fb;}
        #mirseviniText{border-bottom:5px solid black;border-radius:4px;width:70%;border-color:#2980b9}
        #emriProgramitInput{border-radius:5px;width:95%;height:30px;background-color:#ebf5fb;margin-bottom:20px}
        #dergoKerkesenText{border-bottom:3.5px solid black;width:35%;border-color:#2980b9}
    </style>
</head>
<body>
    <div class="main-content" style="flex: 1; padding: 40px;">
    <div id="logout" >
        <a  id="link" href="../../logout.php"style="color:white">LOGOUT</a>        
            </div>    
    
        <h2 id="mirseviniText">Mirësevini në Sistemin e Akreditimit</h2>
        <p>Institucioni: <strong><?php echo $user_uni; ?></strong> | Fakulteti: <strong><?php echo $user_fak; ?></strong></p>
        
        <?php echo $mesazhi; ?>

        <div class="status-box">
            <p>Statusi i Progresit të Akreditimit:</p>
            <div class="status-text">⏳ <?php echo $statusi_aktual; ?></div>
        </div>

        <div class="request-card">
            <h3 id="dergoKerkesenText">Dërgo Kërkesë të Re</h3><br>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Emri i Programit:</label><br>
                <input id="emriProgramitInput" type="text" name="emri_programit" required style="padding:10px; margin: 10px 0;"><br>
                <label >Dokumentacioni (PDF):</label><br>
                <input  type="file" name="request_pdf" accept=".pdf" required style="display:inline"><br><br>
                <button id="dergoap" type="submit" class="btn">Dërgo Aplikimin</button>
            </form>
            
        </div>
    </div>
</body>
</html>
<?php require_once '../../../includes/footer.php'; ?></php>

