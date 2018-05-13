<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

$request_method = $_SERVER['REQUEST_METHOD'];
$forward_url = "/lidmaatschap/inschrijven2/index.php";

$id_to_activity = array(

    "lid" => array(
        "description" => "Lidmaatschap RTN",
        "price" => 90,
        ),

    "bs0" => array(
        "description" => "Bijscholing Hoger Redder 30/6/2018",
        "price" => 50,
        ),

);

class Enroll {
    function Enroll($keymap) {
        $keys_req = array(
            "activity",
            "firstname",
            "surname",
            "street",
            "postalcode",
            "city",
            "email",
            "birthdate",
        );
        $keys_phone = array(
            "phone",
            "mobile",
        );

        $this->data = array();

        foreach($keys_req as $key) {
            if (array_key_exists($key, $keymap)) {
                $val = trim($keymap[$key]) ?? NULL;
            } else {
                $val = NULL;
            }
            $this->data[$key] = $val;
        }

        $this->phones = array();

        foreach($keys_phone as $key) {
            if (array_key_exists($key, $keymap)) {
                $val = trim($keymap[$key]) ?? NULL;
            } else {
                $val = NULL;
            }
            $this->phones[$key] = $val;
        }
    }

    function get_kv_data($id_to_activity) {
        $kv = array();

        $data = $this->data;
        $data["birthdate"] = strftime('%d/%m/%Y (%c)', strtotime($data["birthdate"]));

        foreach([$data, $this->phones] as $arr) {
            foreach($arr as $key => $value) {
                $kv[ "{" . $key . "}" ] = $value;
            }
        }

        $activity_id = $this->data["activity"];
        $activity = $id_to_activity[$activity_id];
        $kv["{activity}"] = $activity["description"];
        $kv["{price}"] = $activity["price"];

        return $kv;
    }

    function bad_keys() {
        $bad_keys = array();

        foreach($this->data as $key => $val) {
            if (empty($val)) {
                $bad_keys[] = $key;
            }
        }

        $bad_phones = array();
        foreach($this->phones as $key => $val) {
            if (!empty($val)) {
                $bad_phones[] = $key;
            }
        }
        if (count($bad_phones) != count($this->phones)) {
            $bad_keys = array_merge($bad_keys, array_keys($this->phones));
        }
        return $bad_keys;
    }

    function valid() {
        $bad_keys = $this->bad_keys();
        return empty($bad_keys);
    }
}




if ($request_method == 'POST') {
    $enroll = new Enroll($_POST);

    $valid = 1;
    $msg = "Uw verzoek werd succesvol geregistreerd. Wij nemen contact met u op.";

    $valid = $enroll->valid();
    if (!$valid) {
        $msg = "Sommige data was foutief. Corrigeer deze en probeer opnieuw.";
    } else {
        $person_kv = $enroll->get_kv_data($id_to_activity);

        $mail_address = "admin@rescueteam.be";
        $mail_subject = "Nieuwe registratie voor {activity} - {firstname} {surname}";
        $mail_message = <<<EOM
<p>Hallo,</p>
<p>Zopas werd een registratie ontvangen voor:</p>
<p>{activity} twv &euro;{price}</p>
<p>Door:</p>
<ul>
<li>Voornaam: {firstname}</li>
<li>Familienaam: {surname}</li>
<li>Straat: {street}</li>
<li>Postcode: {postalcode}</li>
<li>Stad: {city}</li>
<li>E-Mail: {email}</li>
<li>Telefoon: {phone}</li>
<li>GSM: {mobile}</li>
<li>Geboortedatum: {birthdate}</li>
</ul>
<p>Dit is g&eacute;&eacute;n bevestiging van inschrijving!</p>
<p>Wij nemen zo spoedig mogelijk contact met u op.</p>
<p>Bedankt voor het vertrouwen!</p>
<p>Rescue Team Ninove</p>

EOM;
        $mail_subject = str_replace(array_keys($person_kv), array_values($person_kv), $mail_subject);
        $mail_message = str_replace(array_keys($person_kv), array_values($person_kv), $mail_message);

        $mail_headers = "";
        $mail_headers .= "From: admin@rescueteam.be\r\n";
        $mail_headers .= "Reply-To: admin@rescueteam.be\r\n";
        $mail_headers .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $mail_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // DEBUG START
        $mail_address = "debug@rescueteam.be";

        $mail_headers = "";
        $mail_headers .= "From: debug@rescueteam.be\r\n";
        $mail_headers .= "Reply-To: admin@rescueteam.be\r\n";
        $mail_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $mail_headers .= "MIME-Version: 1.0\r\n";
        $mail_headers .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        // DEBUG END

        $valid = mail($mail_address, wordwrap($mail_subject, 70), $mail_message, $mail_headers);
        if (!$valid) {
            $msg = "Intern probleem. Probeer later opnieuw. Contacteer ons moest dit probleem aanhouden.";
        }

        echo("mail_address: \r\n$mail_address\r\n");
        echo("mail_subject: \r\n$mail_subject\r\n");
        echo("mail_headers: \r\n$mail_headers\r\n");
        echo("mail_message: \r\n$mail_message\r\n");
        echo("Result of mail was $valid \r\n");
    }

    $qs = [
        "valid" => $valid,
        "msg" => $msg,
    ];
    $target_url = $forward_url . "?" . http_build_query($qs);
    echo("forward to $target_url\r\n");
    echo("header(Location: $target_url)\r\n");
} else {
    if ($request_method != 'GET') {
        // Assume GET :)
    }
    require __DIR__ . "/../include/form.html";
}
?>
