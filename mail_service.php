<?php
date_default_timezone_set("Europe/Vilnius");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require "connect.php";

$sql = 'SELECT * FROM clubs'; // . $_SESSION["klubas"];
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $tempo = array();
    $tempo["id_k"] = $row["id_klubas"];
    $tempo["pavadinimas"] = $row["pavadinimas"];
    $klubai[] = $tempo;
}

foreach ($klubai as $k) {
    $id = $k["id_k"];

    $sql = 'SELECT susitikimas, laikas from nustatymai where id_klubas=' . $id; // . $_SESSION["klubas"];
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $settings["susitikimas"] = $row["susitikimas"];
        $settings["laikas"] = $row["laikas"];
    }

    switch ($settings['susitikimas']) {
        case 1:
            $diena = "next Monday";
            break;
        case 2:
            $diena = "next Tuesday";
            break;
        case 3:
            $diena = "next Wednesday";
            break;
        case 4:
            $diena = "next Thursday";
            break;
        case 5:
            $diena = "next Friday";
            break;
    }

    $next = strtotime($diena);

    if (date("Y-m-d", $next) == date('Y-m-d', strtotime('+7 day', strtotime(date("Y-m-d"))))) {
        $hd = date("H") * 60 + date("i");
        $laikas = explode(":", $settings["laikas"]);
        $l = $laikas[0] * 60 + $laikas[1];
        $curr = date("Y-m-d");
        if ($hd - $l >= 60) {
            $yra = false;
            $sql2 = 'SELECT * FROM guests WHERE data="' . $curr . '" and id_klubas=' . $id;
            $result2 = $conn->query($sql2);
            if ($result2->num_rows > 0) {
                while ($row2 = $result2->fetch_assoc()) {
                    $tempo = array();
                    $tempo["id_g"] = $row2["id_guest"];
                    $tempo["vardas"] = $row2["vardas"];
                    $tempo["pavarde"] = $row2["pavarde"];
                    $tempo["atvyko"] = $row2["atvyko"];
                    $tempo["gavo"] = $row2["gavo"];
                    $tempo["imone"] = $row2["imone"];
                    $tempo["komentaras"] = $row2["komentaras"];
                    $tempo["lastmod"] = $row2["lastmod"];
                    $tempo["mailas"] = $row2["mailas"];
                    $tempo["mok"] = $row2["mok"];
                    $tempo["pakviete"] = $row2["pakviete"];
                    $tempo["telefonas"] = $row2["telefonas"];
                    $tempo["tipas"] = $row2["tipas"];
                    $tempo["uzpildyta"] = $row2["uzpildyta"];
                    $tempo["veikla"] = $row2["veikla"];
                    $tempo["agrdb"] = $row2["agrdb"];
                    $tempo["agrnews"] = $row2["agrnews"];
                    $tempo["ispudisA"] = $row2["ispudisA"];
                    $tempo["ispudisB"] = $row2["ispudisB"];
                    $tempo["ispudisC"] = $row2["ispudisC"];
                    $tempo["ispudisD"] = $row2["ispudisD"];
                    $tempo["kartas"] = $row2["kartas"];
                    $tempo["rekomendacija"] = $row2["rekomendacija"];
                    $tempo["supazinimas"] = $row2["supazinimas"];
                    $tempo["sutikimas"] = $row2["sutikimas"];
                    $tempo["uzsiminimas"] = $row2["uzsiminimas"];
                    $tempo["zingsnis"] = $row2["zingsnis"];
                    $duom[] = $tempo;
                }
            }
            $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
            try {
                //Server settings
                $mail->SMTPDebug = 0;                                 // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->CharSet = 'UTF-8';
                $mail->Host = 'smtp.hostinger.com';                    // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'administracija@bnisantaka.com';                 // SMTP username
                $mail->Password = 'bni1731733';                           // SMTP password
                $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465;                                    // TCP port to connect to //587;465
                $mail->setFrom('administracija@bnisantaka.com', 'BNI ' . $k["pavadinimas"]);
                $mail->addReplyTo('administracija@bnisantaka.com', 'Information');
                $mail->isHTML(true);
                $mail->Subject = 'Prašome užpildyti šią anketą';
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $negavo = array();

                if (is_array($duom)) {

                    foreach ($duom as $k => $val) {
                        // print "<pre>";
                        // print_r($val);
                        // print "</pre>";
                        if ($val['atvyko'] == 1 && $val['tipas'] == 0 && !isset($val['gavo'])) {
                            if (filter_var($val['mailas'], FILTER_VALIDATE_EMAIL)) {
                                $yra = true;
                                $mail->ClearAllRecipients();
                                $mail->addAddress($val['mailas']);

                                $mail->Body = 'Sveiki,<br>
                                <br>
                                Dėkojame už jūsų dalyvavimą BNI ' . $k["pavadinimas"] . ' verslo pusryčiuose ir prašome užpildyti šią anketą.<br>
                                https://www.bnilietuva.com/anketa.php?data=' . $curr . '&git=' . $id . '_' . $val["id_g"] . '<br>
                                <br>
                                Pagarbiai,<br>
                                BNI ' . $k["pavadinimas"];
                                $mail->send();
                                $sql = 'UPDATE guests SET gavo=' . time() . ' where id_klubas=' . $id . ' and id_guest=' . $val["id_g"] . ' and data="' . $curr . '"';
                                $conn->query($sql);
                            }
                        }
                    }
                }
            } catch (Exception $e) { }
        }
    }
}
