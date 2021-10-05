<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

$request_method = $_SERVER['REQUEST_METHOD'];

$id_to_activity = array(

    "lid" => array(
        "description" => "Lidmaatschap RTN",
        "price" => 90,
        ),

    "ro0" => array(
        "description" => "Opleiding Hoger Redder 2022",
        "price" => 300,
        ),

);

class Enroll {
    static $keys_req = array(
        "activity",
        "firstname",
        "surname",
        "street",
        "postalcode",
        "city",
        "email",
        "birthdate",
        "birthplace",
    );

    static $keys_nonreq = array(
        "comments",
    );

    static $keys_phone = array(
        "phone",
        "mobile",
    );

    function __construct($keymap) {
        $this->data = array();

        foreach(array_merge(self::$keys_req, self::$keys_nonreq) as $key) {
            if (array_key_exists($key, $keymap)) {
                $val = trim($keymap[$key]) ?? NULL;
            } else {
                $val = NULL;
            }
            $this->data[$key] = $val;
        }

        $this->phones = array();

        foreach(self::$keys_phone as $key) {
            if (array_key_exists($key, $keymap)) {
                $val = trim($keymap[$key]) ?? NULL;
            } else {
                $val = NULL;
            }
            $this->phones[$key] = $val;
        }
    }

    function email() {
        return $this->data["email"];
    }

    function get_kv_data($id_to_activity) {
        $kv = array();

        $data = $this->data;
        $data["birthdate"] = strftime('%d/%m/%Y (%c)', strtotime($data["birthdate"]));

        foreach([$data, $this->phones] as $arr) {
            foreach($arr as $key => $value) {
                $kv[ "{" . $key . "}" ] = htmlspecialchars($value);
            }
        }

        $activity_id = $this->data["activity"];
        $activity = $id_to_activity[$activity_id];

        $kv["{activity}"] = htmlspecialchars($activity["description"]);
        $kv["{price}"] = htmlspecialchars($activity["price"]);

        return $kv;
    }

    function bad_keys() {
        $bad_keys = array();

        foreach(self::$keys_req as $key_req) {
            $val = $this->data[$key_req];
            if (empty($val)) {
                $bad_keys[] = $key;
            }
        }

        $bad_phones = array();
        foreach($this->phones as $key => $val) {
            if (empty($val)) {
                $bad_phones[] = $key;
            }
        }
        if (count($bad_phones) == count($this->phones)) {
            $bad_keys = array_merge($bad_keys, array_keys($this->phones));
        }
        return $bad_keys;
    }

    function valid() {
        $bad_keys = $this->bad_keys();
        $valid = empty($bad_keys);
        if (!$valid) {
            trigger_error("Some bad keys: " . join(", ", $bad_keys));
        }
        return $valid;
    }
}



function send_mail($to, $enroll) {
    global $id_to_activity;

    $person_kv = $enroll->get_kv_data($id_to_activity);

    $mail_subject = "Nieuwe registratie voor {activity} - {firstname} {surname}";
    $mail_message = <<<EOM
<p>Beste clubmedewerker,</p>
<p>Zopas werd op de website een registratie ontvangen voor</p>
<p>{activity} twv &euro;{price},-</p>
<p>door</p>
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
<li>Geboorteplaats: {birthplace}</li>
<li>Opmerkingen: {comments}</li>
</ul>
<p>Gelieve zo spoedig mogelijk bovenstaande persoon te contacteren voor bevestiging!</p>
<p>Rescue Web Developer Team Ninove</p>
EOM;
    $mail_subject = str_replace(array_keys($person_kv), array_values($person_kv), $mail_subject);
    $mail_message = str_replace(array_keys($person_kv), array_values($person_kv), $mail_message);

    $mail_headers = "";
    $mail_headers .= "From: no-reply@rescueteam.be\r\n";
    $mail_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $mail_headers .= "MIME-Version: 1.0\r\n";
    $mail_headers .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";

    $mail_success = mail($to, wordwrap($mail_subject, 70), $mail_message, $mail_headers);

    return $mail_success;
}

function send_emails() {
    $enroll = new Enroll($_POST);
    if (!$enroll->valid()) {
        return false;
    }

    $receivers = [
    
        "opleidingen@rescueteam.be",
    
        "debug@rescueteam.be",
    
        //$enroll->email(), // receiver must be an existing address on one.com domain
    ];

    foreach($receivers as $receiver) {
        $mail_success = send_mail($receiver, $enroll);
        if (!$mail_success) {
            return false;
        }
    }
    return true;
}

function handle_POST() {
    $success = send_emails();

    if ($success) {
        http_response_code(200);
        $message = "ok";
    } else {
        http_response_code(400);
        $message = error_get_last()["message"];
    }

    $data = [
        "ok" => $success,
        "msg" => $message,
    ];

    header('Content-type:application/json;charset=utf-8');
    echo json_encode($data);
}

if ($request_method == 'POST') {
    handle_POST();
}
?>
