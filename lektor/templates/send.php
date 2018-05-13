<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

$request_method = $_SERVER['REQUEST_METHOD'];
$from_process_location = '/beta/lidmaatschap/inschrijven/send.php';
$form_location = '/beta/lidmaatschap/inschrijven/';
$forward_good = '/beta/lidmaatschap/';

class Enroll {
    function Enroll($keymap) {
        $this->activity = $keymap['activity'] ?? null;
        $this->firstname = $keymap['firstname'] ?? null;
        $this->surname = $keymap['surname'] ?? null;
        $this->street = $keymap['street'] ?? null;
        $this->postcode = $keymap['postcode'] ?? null;
        $this->city = $keymap['city'] ?? null;
        $this->email = $keymap['email'] ?? null;
        $this->phone = $keymap['phone'] ?? null;
        $this->mobile = $keymap['mobile'] ?? null;
        $this->birthdate = $keymap['birthdate'] ?? null;
        $this->birthdate_stamp = strtotime($this->birthdate) ?? null;
    }

    function valid() {
        return isset($this->activity)
            && isset($this->firstname)
            && isset($this->surname)
            && isset($this->street)
            && isset($this->postcode)
            && isset($this->city)
            && isset($this->email)
            && (isset($this->phone)
                || isset($this->mobile))
            && isset($this->birthdate)
            && isset($this->birthdate_stamp);
    }

    function birthdate_string() {
        return strftime('%d/%m/%Y (%c)', $this->birthdate_stamp);
    }
}

if ($request_method != 'POST') {
    echo 'NEED POST!';
    return;
}

foreach($_POST as $k => $v) {
  echo $k . '->' . $v . '<br />';
}


$enroll = new Enroll($_POST);
$valid = $enroll->valid();
if (!$valid) {
    echo "values were not valid";
    echo "header(Location: '$from_process_location')";
    return;
}

$mail_address = "admin@rescueteam.be";
$mail_subject = "Nieuwe inschrijving voor $enroll->activity: '$enroll->firstname $enroll->surname'";
$mail_message = "Hallo,

Zopas werd een registratie ontvangen voor:

$enroll->activity

Details persoon:

Voornaam: $enroll->firstname
Familienaam: $enroll->surname
Straat: $enroll->street
Postcode: $enroll->postcode
Stad: $enroll->city
E-Mail: $enroll->email
Telefoon: $enroll->phone
GSM: $enroll->mobile
Geboortedatum: " . $enroll->birthdate_string() . "

Wij nemen zo spoedig mogelijk contact met u op.

Bedankt voor het vertrouwen!

Rescue Team Ninove
";

$mail_headers = 'From: admin@rescueteam.be' . "\r\n" .
    'Reply-To: leden@rescueteam.be' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$mail_result = mail($mail_address, wordwrap($mail_subject, 70), $mail_message, $mail_headers);


$good_qs = array(
    'result' => true,
    'text' => 'Bericht is verzonden. Wij nemen zo spoedig mogelijk contact met U op.',
    );

echo("forward to $forward_good?".http_build_query($good_qs));

?>
