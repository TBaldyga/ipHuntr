<?php 
/*
UserCake Version: 2.0.1
http://usercake.com
*/
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once("models/header.php");

echo "
<body>
<section>
<br /><h1> Title </h1>
</section>
</body>
</html>";

require_once("models/footer.php");
?>