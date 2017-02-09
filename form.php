<?php

$mail = 'topfirmy@topfirmy.cz';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["odesilatel"])) {
        $odesilatel = $mail;
    } else {
        $odesilatel = $_POST["odesilatel"];
    }

    $headers = 'From: ' . $odesilatel . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $today = getdate();
    $datetime = $today['mday'] . '. ' . $today['mon'] . '. ' . $today['year'] . ' ' . $today['hours'] . ':' . $today['minutes'];

    // MESSAGE
    $message = '<p>Na webu ' . $_SERVER["HTTP_ORIGIN"] . ' byl vyplněn formulář.</p>';
    $message .= '<p><b style="text-decoration: underline; ">Informace o uživateli:</b></p>';

    $message .= '<ul>';
    $message .= '<li><b>IP adresa:</b> ' . $_SERVER["SERVER_ADDR"] . '</li>';    
    $message .= '<li><b>Odkud uživatel přišel:</b> ' . $_SERVER["HTTP_REFERER"] . '</li>';
    $message .= '<li><b>Uživatelův prohlížeč:</b> ' . $_SERVER["HTTP_USER_AGENT"] . '</li>';
    $message .= '<li><b>Datum a čas:</b> ' . $datetime . '</li>';
    $message .= '</ul>';

    $message .= '<p><b style="text-decoration: underline; ">Obsah formuláře:</b></p>';
    $message .= '<ul>';

    foreach ($_POST as $key => $val) {
        $emailErr = '';

        if (preg_match('~#(.*?)#~', $key, $out)) {

            //out[1];
            $val = test_input($val);
            $key = substr($key, 5);

            if ($key == 'e-mail') {
                if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $emailErr = "(E-mail je ve špatném formátu)";
                }
            }
            $message .= '<li><b>' . $key . '</b>: ' . nl2br($val) . ' ' . $emailErr . '</li>';
        }
    }

    $message .= '</ul>';

    if (!isset($_POST['prijemce']) || $_POST['prijemce'] == '') {
        $prijemce = $mail;
    } else {
        $prijemce = $_POST['prijemce'];
    }



    if (!isset($_POST['predmet']) || $_POST['predmet'] == '') {
        if (isset($_SERVER["HTTP_REFERER"])) {
            $predmet = 'Zpráva z webu ' . $_SERVER["HTTP_REFERER"];
        } else {
            $predmet = 'Zpráva z webu';
        }
    } else {
        $predmet = $_POST['predmet'];
    }

    

//SENDING
    if (mail($prijemce, $predmet, $message, $headers)) {
        echo $message;

        if (isset($_POST['ok_url'])) {
            header('Location: ' . $_POST["ok_url"]);
            die();
        } else {
            header('Location: ' . $_SERVER["HTTP_REFERER"]);
            die();
        }
    } else {
        $headers_error = 'From: ' . $mail;
        $errmsg = 'Na jednom z webů selhalo odeslání e-mailu s daty z formuláře. Níže jsou informace o serveru:\n';
        $errmsg .= implode('\n', $_SERVER);
        $errmsg .= '\n\nNíže jsou odeslané informacee:\n';
        $errmsg .= implode('\n', $_POST);
        mail($mail, 'Chyba při odeslání formuláře na webu', $errmsg, $headers_error);

        echo 'Formulář nebyl správně odeslán. Protože je chyba pravděpodobně na naší straně, napsali jsme o tom zprávu správci stránek. Děkujeme a pochopení.';
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
