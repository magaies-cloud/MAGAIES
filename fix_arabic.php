<?php
function fix_file($file) {
    $content = file_get_contents($file);
    $content = preg_replace('/\?\?\?\?\?\?\?/', '�������', $content);
    $content = preg_replace('/\?\?\?/', '??', $content);
    $content = preg_replace('/\?\?\?\? \?\? \?\?\? \?\?\?\? \(\?\?\?\?\? \?\?\)/', '���� ��� ���� ���� (����� ���)', $content);
    $content = preg_replace('/\?\?\?/', '??', $content);
    $content = preg_replace('/\?\?\?\? \?\?\?\?\?\?\?\? \?\?\?\?\?\? \? \?\?\? \?\?\?\?\?\?\?\?\? \?\?\?/', '���� �������� ������� �� ��� ��������� ??', $content);
    $content = preg_replace('/\?\?\?\? \(/', '������� (', $content);
    $content = preg_replace('/\- \? \[OK \/ \?\] \? \? \? \? ? \?\./', '- ���� [OK / �����] ��� ��� ���� ����� ��� �������.', $content);
    $content = preg_replace('/\- \? \[Cancel \/ \?\?\] \? \? \? \? \?\?\? \?\?\?\? \? ? \?\./', '- ���� [Cancel / �����] ��� ��� ���� ��� ��������� ������� �� ��� �������.', $content);
    $content = preg_replace('/\? \? \?/', '? ����� �����', $content);
    $content = preg_replace('/\? \? ?\? \? \?\?\?\? \? \? \?\?\? \?\?\?\? \?/', '�� ��� ����� �� ����� �� ��� ��������� ������� ��', $content);
    $content = preg_replace('/\? \?\?\?/', '���� �����', $content);
    file_put_contents($file, $content);
}
fix_file('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js');
