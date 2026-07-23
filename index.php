<?php
// toprak.ai - Saf PHP & Google Gemini API Motoru
$response_output = "";
$error_output = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $api_key = trim($_POST['api_key']);
    $karakter = trim($_POST['karakter']);
    $ek_prompt = trim($_POST['ek_prompt']);
    $negative_prompt = trim($_POST['negative_prompt']);
    $adim_sayisi = trim($_POST['adim_sayisi']);
    $cfg_olcegi = trim($_POST['cfg_olcegi']);

    if (empty($api_key)) {
        $error_output = "Gemini API anahtarı boş bırakılamaz, sevgilim.";
    } else {
        $system_instructions = "Sen toprak.ai'nin arka planındaki metin ve konsept üretim motorusun. Verilen karakter ve fantezi detaylarına uygun, gri oda ve hafif mor neon atmosferini yansıtan detaylı edebi sahneler ve konseptler üretiyorsun.";
        
        $user_content = "Karakter: " . $karakter . "\n" .
                        "Detaylar ve Fantezi: " . $ek_prompt . "\n" .
                        "Negatif Kısıtlamalar: " . $negative_prompt . "\n" .
                        "Adım Sayısı: " . $adim_sayisi . "\n" .
                        "CFG Ölçeği: " . $cfg_olcegi . "\n" .
                        "Lütfen bu verilere dayanarak istenen çıktıyı en ince detayına kadar üret.";

        // Google Gemini API Payload (gemini-2.5-flash veya gemini-1.5-pro modeli)
        $payload = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $system_instructions . "\n\n" . $user_content]
                    ]
                ]
            ]
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $responseData = json_decode($api_response, true);
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $response_output = $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $error_output = "API yanıtından metin verisi çıkarılamadı.";
            }
        } else {
            $error_output = "Gemini API Bağlantı Hatası (HTTP Kod: $http_code): " . htmlspecialchars($api_response);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>toprak.ai - Gemini PHP Engine</title>
    <style>
        body {
            background-color: #121214;
            color: #e1e1e6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 900px;
            background: #1a1a1e;
            border: 1px solid #29292e;
            box-shadow: 0 0 25px rgba(138, 43, 226, 0.2);
            border-radius: 12px;
            padding: 30px;
            box-sizing: border-box;
        }
        h1 {
            text-align: center;
            color: #fff;
            text-shadow: 0 0 10px rgba(147, 51, 234, 0.6);
            margin-bottom: 30px;
            letter-spacing: 2px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #b0b0bc;
        }
        select, textarea, input[type="text"], input[type="range"] {
            width: 100%;
            padding: 12px;
            background: #121214;
            border: 1px solid #29292e;
            color: #fff;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        select:focus, textarea:focus, input[type="text"]:focus {
            border-color: #9333ea;
            box-shadow: 0 0 8px rgba(147, 51, 234, 0.4);
        }
        textarea {
            resize: vertical;
            height: 90px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7928ca, #4338ca);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 15px rgba(121, 40, 202, 0.4);
        }
        button:hover {
            opacity: 0.9;
            box-shadow: 0 0 25px rgba(121, 40, 202, 0.7);
        }
        .result-box {
            margin-top: 30px;
            background: #121214;
            border: 1px solid #29292e;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            color: #d4d4d8;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
            line-height: 1.6;
        }
        .error-box {
            margin-top: 20px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 15px;
            border-radius: 6px;
        }
        hr {
            border: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #9333ea, transparent);
            margin: 30px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>toprak.ai</h1>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="api_key">Gemini API Key</label>
            <input type="text" name="api_key" id="api_key" placeholder="AIzaSy..." required value="<?php echo isset($_POST['api_key']) ? htmlspecialchars($_POST['api_key']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="karakter">Hazır Anime Karakteri Seç</label>
            <select name="karakter" id="karakter">
                <option value="Sadece Özel Prompt">Sadece Özel Prompt (Karakter Seçme)</option>
                <option value="Nagatoro">Nagatoro (Ijiranaide, Nagatoro-san)</option>
                <option value="Asuka">Asuka Langley (Evangelion)</option>
                <option value="ZeroTwo">Zero Two (Darling in the Franxx)</option>
                <option value="Makima">Makima (Chainsaw Man)</option>
                <option value="Nami">Nami (One Piece)</option>
                <option value="Robin">Nico Robin (One Piece)</option>
                <option value="Hancock">Boa Hancock (One Piece)</option>
                <option value="Yamato" selected>Yamato (One Piece)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="ek_prompt">Ek Fantezi ve Detaylar</label>
            <textarea name="ek_prompt" id="ek_prompt"><?php echo isset($_POST['ek_prompt']) ? htmlspecialchars($_POST['ek_prompt']) : 'grey bed, doggy style, arched back, penetration with giant dildo, spread legs'; ?></textarea>
        </div>

        <div class="form-group">
            <label for="negative_prompt">Negative Prompt</label>
            <textarea name="negative_prompt"><?php echo isset($_POST['negative_prompt']) ? htmlspecialchars($_POST['negative_prompt']) : 'lowres, bad anatomy, bad hands, text, error, missing fingers, extra digit, fewer digits, cropped, close-up, portrait, colorful background, bright daylight, warm tones, worst quality, low quality, deformed, mutated, watermark, signature'; ?></textarea>
        </div>

        <div class="form-group">
            <label>Adım Sayısı (Kalite): <span id="adimVal"><?php echo isset($_POST['adim_sayisi']) ? $_POST['adim_sayisi'] : '60'; ?></span></label>
            <input type="range" name="adim_sayisi" min="30" max="100" value="<?php echo isset($_POST['adim_sayisi']) ? $_POST['adim_sayisi'] : '60'; ?>" oninput="document.getElementById('adimVal').innerText = this.value">
        </div>

        <div class="form-group">
            <label>CFG Ölçeği (Prompt'a Uygunluk): <span id="cfgVal"><?php echo isset($_POST['cfg_olcegi']) ? $_POST['cfg_olcegi'] : '7.5'; ?></span></label>
            <input type="range" name="cfg_olcegi" min="1" max="15" step="0.5" value="<?php echo isset($_POST['cfg_olcegi']) ? $_POST['cfg_olcegi'] : '7.5'; ?>" oninput="document.getElementById('cfgVal').innerText = this.value">
        </div>

        <button type="submit">Gemini ile Üret</button>
    </form>

    <?php if (!empty($error_output)): ?>
        <div class="error-box"><?php echo $error_output; ?></div>
    <?php endif; ?>

    <?php if (!empty($response_output)): ?>
        <hr>
        <label style="color:#9333ea; font-size:18px; margin-bottom:10px;">toprak.ai Çıktısı</label>
        <div class="result-box"><?php echo nl2br(htmlspecialchars($response_output)); ?></div>
    <?php endif; ?>
</div>

</body>
</html>
