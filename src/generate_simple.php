<?php
/**
 * РЈРїСЂРѕС‰РµРЅРЅС‹Р№ РіРµРЅРµСЂР°С‚РѕСЂ РєРѕРјРјРµСЂС‡РµСЃРєРёС… РїСЂРµРґР»РѕР¶РµРЅРёР№
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

// РўРµСЃС‚РѕРІС‹Рµ РґР°РЅРЅС‹Рµ СЃРµСЃСЃРёРё
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// РџРѕР»СѓС‡Р°РµРј РїР°СЂР°РјРµС‚СЂС‹
$deal_id = $_GET['deal_id'] ?? 1;

echo "<h2>Р“РµРЅРµСЂР°С†РёСЏ РєРѕРјРјРµСЂС‡РµСЃРєРѕРіРѕ РїСЂРµРґР»РѕР¶РµРЅРёСЏ</h2>";

try {
    // 1. РџРѕРґРєР»СЋС‡РµРЅРёРµ Рє Р‘Р”
    $db = Database::getConnection();
    echo "вњ… РџРѕРґРєР»СЋС‡РµРЅРёРµ Рє Р‘Р” СѓСЃРїРµС€РЅРѕ<br>";
    
    // 2. РџРѕР»СѓС‡Р°РµРј РґР°РЅРЅС‹Рµ СЃРґРµР»РєРё
    $deal = $db->query("
        SELECT d.*, c.company_name 
        FROM deals d 
        LEFT JOIN clients c ON d.client_id = c.client_id 
        WHERE d.deal_id = ?", 
        [$deal_id]
    )->fetch();
    
    if (!$deal) {
        die("вќЊ РЎРґРµР»РєР° РЅРµ РЅР°Р№РґРµРЅР°");
    }
    
    echo "вњ… РЎРґРµР»РєР°: {$deal['deal_name']} (#{$deal['deal_number']})<br>";
    
    // 3. РЎРѕР·РґР°РµРј РїСЂРѕСЃС‚РѕР№ РґРѕРєСѓРјРµРЅС‚
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    
    // Р—Р°РіРѕР»РѕРІРѕРє
    $section->addText('РљРћРњРњР•Р Р§Р•РЎРљРћР• РџР Р•Р”Р›РћР–Р•РќРР•', ['bold' => true, 'size' => 16]);
    $section->addTextBreak();
    
    // РРЅС„РѕСЂРјР°С†РёСЏ Рѕ СЃРґРµР»РєРµ
    $section->addText("РќРѕРјРµСЂ СЃРґРµР»РєРё: {$deal['deal_number']}");
    $section->addText("РќР°Р·РІР°РЅРёРµ: {$deal['deal_name']}");
    $section->addText("РљР»РёРµРЅС‚: {$deal['company_name']}");
    $section->addText("Р‘СЋРґР¶РµС‚: {$deal['budget']} СЂСѓР±.");
    $section->addText("Р”Р°С‚Р°: " . date('d.m.Y'));
    $section->addTextBreak();
    
    // РўРµРєСЃС‚ РїСЂРµРґР»РѕР¶РµРЅРёСЏ
    $section->addText('РЈРІР°Р¶Р°РµРјС‹Р№ РєР»РёРµРЅС‚!');
    $section->addText('РџСЂРµРґСЃС‚Р°РІР»СЏРµРј РІР°Рј РєРѕРјРјРµСЂС‡РµСЃРєРѕРµ РїСЂРµРґР»РѕР¶РµРЅРёРµ РїРѕ СѓРєР°Р·Р°РЅРЅРѕР№ СЃРґРµР»РєРµ.');
    $section->addTextBreak();
    
    $section->addText('РЎ СѓРІР°Р¶РµРЅРёРµРј,');
    $section->addText('РљРѕРјРїР°РЅРёСЏ ASTI РњРµР±РµР»СЊ');
    
    // 4. РЎРѕС…СЂР°РЅСЏРµРј РґРѕРєСѓРјРµРЅС‚
    $output_dir = __DIR__ . '/../storage/';
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0777, true);
    }
    
    $output_file = $output_dir . 'proposal_' . $deal['deal_number'] . '.docx';
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($output_file);
    
    echo "вњ… Р”РѕРєСѓРјРµРЅС‚ СЃРіРµРЅРµСЂРёСЂРѕРІР°РЅ!<br>";
    
    // 5. РџРѕРєР°Р·С‹РІР°РµРј СЃСЃС‹Р»РєСѓ
    $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $output_file);
    echo "<h3 style='color: green;'>рџЋ‰ Р“РѕС‚РѕРІРѕ!</h3>";
    echo "<p><a href='$web_path' download style='
        padding: 15px 30px;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 18px;
    '>рџ“Ґ РЎРєР°С‡Р°С‚СЊ РєРѕРјРјРµСЂС‡РµСЃРєРѕРµ РїСЂРµРґР»РѕР¶РµРЅРёРµ</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>вќЊ РћС€РёР±РєР°:</h3>";
    echo $e->getMessage();
}
?>
