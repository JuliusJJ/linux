<?php
ini_set("allow_url_fopen", 1);
ini_set('max_execution_time', 0);
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
    require "connect.php";

    $sql = 'SELECT susitikimas, laikas, connect_name, connect_password from nustatymai where id_klubas=' . $id;
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $settings["susitikimas"] = $row["susitikimas"];
        $settings["laikas"] = $row["laikas"];
        $settings["conName"] = $row["connect_name"];
        $settings["conPsw"] = $row["connect_password"];
    }

    switch ($settings["susitikimas"]) {
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

    $nextThursday = strtotime($diena);

    if (date("Y-m-d", $nextThursday) == date('Y-m-d', strtotime('+7 day', strtotime(date("Y-m-d"))))) {
        $curr = date("Y-m-d");
    } else {
        $curr = date("Y-m-d", $nextThursday);
    }

    $sql = 'SELECT id_name, vardas, pavarde, active from names where id_klubas=' . $id;
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $tempo = array();
        $tempo["id_n"] = $row["id_name"];
        $tempo["vardas"] = $row["vardas"];
        $tempo["pavarde"] = $row["pavarde"];
        $tempo["active"] = $row["active"];
        $vardai_n[] = $tempo;
    }
    $sql = 'SELECT vardas, pavarde from guests where id_klubas=' . $id . ' and data="' . $curr . '"'; // . $_SESSION["klubas"];
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $tempo = array();
        $tempo["vardas"] = $row["vardas"];
        $tempo["pavarde"] = $row["pavarde"];
        $vardai_s[] = $tempo;
    }
    mysqli_close($conn);

    putenv("PHANTOMJS_EXECUTABLE=node_modules/phantomjs/lib/phantom/bin/phantomjs.exe");
    exec('casperjs scrape_members.js "' . $settings["conName"] . '" "' . $settings["conPsw"] . '" "' . $id . '"');
    exec('casperjs scrape_guests.js "' . $settings["conName"] . '" "' . $settings["conPsw"] . '" "' . $id . '"');
    if (!$json1 = file_get_contents('./' . $id . 'members.json')) {
        echo "break";
        continue;
    }
    $json2 = file_get_contents('./' . $id . 'guests.json');
    $nariai = json_decode($json1);
    $sveciai = json_decode($json2);
    require "connect.php";

    if (!empty($sveciai)) {
        foreach ($sveciai as $k => $s) {
            $check = false;
            $name = explode(" ", $s->name);
            if (isset($vardai_s)) {
                str_replace('/', '-', $s->visit_date);
                foreach ($vardai_s as $v) {
                    if ($v['vardas'] == $name[0] && $v['pavarde'] == $name[1]) {
                        $check = true;
                        break;
                    }
                }
            }
            if (!$check) {
                $t = true;
                $stmt = $conn->prepare('INSERT INTO guests (id_klubas, vardas, pavarde, imone, telefonas, mailas, veikla, data, pakviete, atvyko, mok, uzpildyta, tipas, lastmod, tikslinis) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 2, "false", 0, ' . time() . ', 0)');
                $stmt->bind_param("issssssss", $id, $name[0], $name[1], $s->company, $s->phone, $s->email, $s->specialty, $s->visit_date, $s->invited_by);
                $response[$k . "_s_status"] = true;
            }
        }
    }
    if (!empty($nariai)) {
        foreach ($nariai as $k => $n) {
            $check = false;
            $name = explode(" ", $n->name);
            if (isset($vardai_n)) {
                foreach ($vardai_n as $v) {
                    if ($v['vardas'] == $name[0] && $v['pavarde'] == $name[1]) {
                        $check = true;
                        if ($v["active"] != $n->active) {
                            $t = true;
                            $stmt = $conn->prepare('UPDATE names set active=? where id_name=?');
                            $stmt->bind_param("ii", $n->active, $v["id_n"]);
                            $response[$k . "_n_aupdt_status"] = true;
                        }
                        break;
                    }
                }
            }
            if (!$check) {
                $t = true;
                $stmt = $conn->prepare('INSERT INTO names (id_klubas, vardas, pavarde, active) VALUES (?, ?, ?, ?)');
                $stmt->bind_param("issi", $id, $name[0], $name[1], $n->active);
                $response[$k . "_n_status"] = true;
            }
        }
    }
    mysqli_close($conn);
}