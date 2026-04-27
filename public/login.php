<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kontrolli i logimit (Routing)
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

// MULTIDIMENSIONAL ARRAY 
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (array_key_exists($email, $perdoruesit_fiktiv) && $perdoruesit_fiktiv[$email]['fjalekalimi'] == $password) {
        
        $_SESSION['email'] = $email;
        $_SESSION['roli'] = $perdoruesit_fiktiv[$email]['roli'];
        $_SESSION['emri'] = $perdoruesit_fiktiv[$email]['emri'];

        // COOKIES
        if (isset($_POST['mbaj_mend'])) {
            setcookie("email_i_ruajtur", $email, time() + (86400 * 30), "/");
        } else {
            setcookie("email_i_ruajtur", "", time() - 3600, "/");
        }

        header("Location: " . ($_SESSION['roli'] == 'admin' ? "dashboard/admin/index.php" : "dashboard/user/index.php"));
        exit();
    } else {
        $gabim = "Emaili ose Fjalëkalimi është i pasaktë!";
    }
}

$email_nga_cookie = $_COOKIE['email_i_ruajtur'] ?? '';


// 1. Ngarkojmë strukturën bazë të Header-it (CSS, Meta tags etj)
require_once '../includes/header.php'; 

// 2. LOGJIKA E NAVIGIMIT (ROUTING) - Pika 1 e detyrës sate
if (isset($_SESSION['roli'])) {
    if ($_SESSION['roli'] === 'admin') {
        require_once '../includes/nav_admin.php';
    } else {
        require_once '../includes/nav_user.php';
    }
}

?>

<style>
    .login-container {
        min-height: 70vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f4f4f9;
        padding: 40px 0;
    }
    .login-box { 
        background: white; 
        padding: 30px; 
        border-radius: 8px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        width: 350px; 
    }
    .login-box h2 { color: #1e3a8a; margin-bottom: 20px; }
    .login-box input[type="email"], .login-box input[type="password"] { 
        width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
    }
    .login-box button { 
        width: 100%; padding: 12px; background: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px;
    }
    .login-box button:hover { background: #2c3e50; }
    .error { color: #d9534f; background: #f9dfdf; padding: 10px; border-radius: 4px; font-size: 14px; text-align: center; }
</style>

<div class="login-container">
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
            
            <div style="margin: 15px 0; font-size: 14px;">
                <input type="checkbox" name="mbaj_mend" id="mbaj_mend" <?php echo $email_nga_cookie ? 'checked' : ''; ?>>
                <label for="mbaj_mend">Më mbaj mend (Cookie)</label>
            </div>

            <button type="submit">Hyr</button>
        </form>

        <div style="font-size:11px; text-align:center; margin-top:25px; color:#777; border-top: 1px solid #eee; padding-top: 15px;">
            <strong>Llogaritë testuese (Dummy Data):</strong><br>
            <?php 
                foreach($perdoruesit_fiktiv as $email_key => $data) {
                    echo "Roli: " . strtoupper($data['roli']) . " | " . $email_key . " | Pw: " . $data['fjalekalimi'] . "<br>";
                }
            ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>