<?php
require_once '../authentication.php';
kontrolloQasjen('user'); // Lejon vetëm institucionet
?>
<h1>Mirësevini Institucion: <?php echo $_SESSION['emri']; ?></h1>
<p>Këtu mund të ngarkoni dokumentet e vetëvlerësimit.</p>
<a href="../../logout.php">Dilni (Logout)</a>