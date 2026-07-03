<?php $db = new PDO('mysql:host=localhost;dbname=magaees', 'root', ''); $res = $db->query('DESCRIBE settings')->fetchAll(PDO::FETCH_ASSOC); print_r($res);
