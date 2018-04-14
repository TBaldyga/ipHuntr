<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once("models/header.php");

echo "
<body onload='myFunction()'>
<section>
<div id='wrapper'>
<div id='top'><div id='logo'></div></div>
<div id='content'>
<h1>ipHuntr Local</h1>
<h2>Alpha  1.1</h2>
<div id='left-nav'>";
include("left-nav.php");

echo "
</div>
<div id='main'>
Welcome to ipHuntr. Thank you for visiting us while we are still in development! <br />
Normally, here is where I would introduce you to the game and why you 'have' to play, but I wont be
doing that just yet. Feel free to look around, make an account, and try to figure out how the game works on your own.
There will be a guide one day but for now simply play with the features that currently exist. I would love your 
feedback as I update the game, you can contact me directly <a href='contact.php'>Here</a> <br />
Still need a hand? <br />
Try making an account first, then see if you can capture your IP address. If you can you will get points (which is good). Easy as that!
</div>
<div id='bottom'></div>
</div>
</section>
</body>
</html>";

require_once("models/footer.php");
?>
