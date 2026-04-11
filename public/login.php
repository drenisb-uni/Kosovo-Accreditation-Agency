<?php
session_start();

if (isset($_SESSION['roli'])) {
    if ($_SESSION['roli'] == 'admin') {
        header("Location: dashboard/admin/index.php");
        exit();
    } elseif ($_SESSION['roli'] == 'user') {
        header("Location: dashboard/user/index.php");
        exit();
    }
}

$gabim = "";

$perdoruesit_fiktiv = [
    'admin@aka.rks-gov.net' => [
        'fjalekalimi' => 'admin123', 
        'roli' => 'admin', 
        'emri' => 'KSHC Admin'
    ],
    'rektorati@uni-pr.edu' => [
        'fjalekalimi' => 'uni123', 
        'roli' => 'user', 
        'emri' => 'Universiteti i Prishtinës'
    ]
];

// Kontrollojmë nëse forma është dërguar (metoda POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verifikimi: A ekziston ky email në array dhe a përputhet fjalëkalimi?
    if (array_key_exists($email, $perdoruesit_fiktiv) && $perdoruesit_fiktiv[$email]['fjalekalimi'] == $password) {
        
        // 2. Ruajtja e vlerave në Session (Kërkesë e Fazës 1)
        $_SESSION['email'] = $email;
        $_SESSION['roli'] = $perdoruesit_fiktiv[$email]['roli'];
        $_SESSION['emri'] = $perdoruesit_fiktiv[$email]['emri'];

        // 3. Përdorimi i Cookies për personalizim (Kërkesë e Fazës 1)
        if (isset($_POST['mbaj_mend'])) {
            // Ruajmë emailin në Cookie për 30 ditë
            setcookie("email_i_ruajtur", $email, time() + (86400 * 30), "/");
        } else {
            // Fshijmë cookie-n nëse nuk u zgjodh kutia
            setcookie("email_i_ruajtur", "", time() - 3600, "/");
        }

        // Ridrejtimi sipas rolit
        if ($_SESSION['roli'] == 'admin') {
            header("Location: dashboard/admin/index.php");
        } else {
            header("Location: dashboard/user/index.php");
        }
        exit();
    } else {
        $gabim = "Emaili ose Fjalëkalimi është i pasaktë!";
    }
}

// Shikojmë nëse kemi një email të ruajtur në Cookie për ta parambushur formën
$email_nga_cookie = isset($_COOKIE['email_i_ruajtur']) ? $_COOKIE['email_i_ruajtur'] : '';
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Kyçja - Agjencia e Akreditimit</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; margin-top: 100px; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; }
        .login-box input[type="email"], .login-box input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        .login-box button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; font-size: 14px; text-align: center; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2 style="text-align:center;">Kyçja në AKA</h2>
        
        <?php if($gabim): ?>
            <p class="error"><?php echo $gabim; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Emaili:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email_nga_cookie); ?>" required>
            
            <label>Fjalëkalimi:</label>
            <input type="password" name="password" required>
            
            <div style="margin-bottom: 15px;">
                <input type="checkbox" name="mbaj_mend" id="mbaj_mend" <?php echo $email_nga_cookie ? 'checked' : ''; ?>>
                <label for="mbaj_mend">Më mbaj mend (Cookie)</label>
            </div>

            <button type="submit">Hyr</button>
        </form>
        <p style="font-size:12px; text-align:center; margin-top:20px; color:#666;">
            Provoni: <br> admin@aka.rks-gov.net / admin123 <br> rektorati@uni-pr.edu / uni123
        </p>
    </div>

</body>
</html>