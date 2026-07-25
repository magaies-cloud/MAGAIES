<?php
for($i=0; $i<5; $i++) {
    $c = file_get_contents("c:/xampp/htdocs/ELSAJANA2/magaies/scratch/script_$i.js");
    if(strpos($c, 'startPresetSave') !== false) {
        echo "Found in script_$i.js\n";
    }
}
?>
