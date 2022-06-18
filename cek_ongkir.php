<?php 


if (!isset($_POST["origin"])) {
    return json_encode([
        "message" => "origin harus di isi",
    ]);
}
if (!isset($_POST["destination"])) {
    return json_encode([
        "message" => "destination harus di isi",
    ]);
}
if (!isset($_POST["weight"])) {
    return json_encode([
        "message" => "weight harus di isi"
    ]);
}

$urls = [];
$urls[] = "origin=".$_POST["origin"]."&destination=".$_POST["destination"]."&weight=".$_POST["weight"]."&courier=jne";
$urls[] = "origin=".$_POST["origin"]."&destination=".$_POST["destination"]."&weight=".$_POST["weight"]."&courier=pos";
$urls[] = "origin=".$_POST["origin"]."&destination=".$_POST["destination"]."&weight=".$_POST["weight"]."&courier=tiki";

$running = null;
$result = [];
$mh = curl_multi_init();

$multiCurl = [];
foreach ($urls as $key => $value) {
    $ch1 = curl_init();
    // curl_setopt($ch1, CURLOPT_URL, $value);
    curl_setopt_array($ch1, array(
        CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $value,
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            "key: f3f2e888e4e90dd01a0f7d429dc1ee7b",
        ),
    ));
    curl_multi_add_handle($mh, $ch1);

    $multiCurl[$key] = $ch1;
}

do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

foreach ($urls as $key => $value) {
    $r1 = curl_multi_getcontent($multiCurl[$key]);
    if ($r1) {
        $r1 = json_decode($r1);
        if ($r1->rajaongkir->status->code == 200) {
            $result[] = $r1->rajaongkir->results[0];
        }
    }
    curl_multi_remove_handle($mh, $multiCurl[$key]);
}

curl_multi_close($mh);

echo json_encode($result);
?>