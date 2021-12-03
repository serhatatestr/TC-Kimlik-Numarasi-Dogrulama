<?php
/**
 * @package	    TC Kimlik Numarası Doğrulama
 * @author	    Serhat ATEŞ
 * @copyright	Copyright (c) 2020 - 2021, Serhat ATEŞ, (https://www.serhatates.com.tr)
 * @license	    https://opensource.org/licenses/MIT	MIT License
 * @link	    https://www.serhatates.com.tr
 * @since	    Version 1.0.0
 */
function alert($alert, $color){
    return "<div class=\"alert alert-$color w-100 p-2 m-0\" role=\"alert\">
        <span class=\"d-block text-center\" style=\"font-family: 'Righteous'; font-size: 48px;\">Opps!</span>
        <span class=\"d-block text-center\">$alert</span>
    </div>";
}
function security($parameter){
    $parameter = htmlspecialchars($parameter);
    $parameter = strip_tags($parameter);
    $parameter = addslashes($parameter);
    return $parameter;
}
function sidesNumberControl($sidesNumber){
    if (is_numeric($sidesNumber[0]) == false || is_numeric($sidesNumber[1]) == false ||
        is_numeric($sidesNumber[2]) == false || is_numeric($sidesNumber[3]) == false || 
        is_numeric($sidesNumber[4]) == false || is_numeric($sidesNumber[5]) == false ||
        is_numeric($sidesNumber[6]) == false || is_numeric($sidesNumber[7]) == false ||
        is_numeric($sidesNumber[8]) == false || is_numeric($sidesNumber[9]) == false ||
        is_numeric($sidesNumber[10]) == false){
        return false;
    }

    $sidesNumber_X1 = $sidesNumber[0];
    $sidesNumber_X2 = $sidesNumber[1];
    $sidesNumber_X3 = $sidesNumber[2];
    $sidesNumber_X4 = $sidesNumber[3];
    $sidesNumber_X5 = $sidesNumber[4];
    $sidesNumber_X6 = $sidesNumber[5];
    $sidesNumber_X7 = $sidesNumber[6];
    $sidesNumber_X8 = $sidesNumber[7];
    $sidesNumber_X9 = $sidesNumber[8];

    $sidesNumber_Y1 = $sidesNumber[9];
    $sidesNumber_Y2 = $sidesNumber[10];

    $sidesNumber_Z1 = 
        (($sidesNumber_X1 + $sidesNumber_X3 + $sidesNumber_X5 + $sidesNumber_X7 + $sidesNumber_X9) * 7 - 
        ($sidesNumber_X2 + $sidesNumber_X4 + $sidesNumber_X6 + $sidesNumber_X8)) % 10;
    $sidesNumber_Z2 = 
        ($sidesNumber_X1 + $sidesNumber_X2 + $sidesNumber_X3 + $sidesNumber_X4 + $sidesNumber_X5 + 
        $sidesNumber_X6 + $sidesNumber_X7 + $sidesNumber_X8 + $sidesNumber_X9 + $sidesNumber_Y1) % 10;

    if ($sidesNumber_Y1 != $sidesNumber_Z1 || $sidesNumber_Y2 != $sidesNumber_Z2){
        return false;
    } else {
        return true;
    }
}
if (isset($_SERVER["REQUEST_METHOD"]) && !empty($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST["sidesFirstName"]) && empty($_POST["sidesFirstName"]) &&
        isset($_POST["sidesLastName"]) && empty($_POST["sidesLastName"]) &&
        isset($_POST["sidesDateOfBirth"]) && empty($_POST["sidesDateOfBirth"]) &&
        isset($_POST["sidesNumber"]) && empty($_POST["sidesNumber"])){
        exit(alert("Lütfen <b>Adınızı, Soyadınızı, Doğum Tarihinizi ve TC Kimlik Numaranızı</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "primary"));
    }

    if (!isset($_POST["sidesFirstName"]) || empty($_POST["sidesFirstName"])){
        exit(alert("Lütfen <b>Adınızı</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "warning"));
    }
    if (!isset($_POST["sidesLastName"]) || empty($_POST["sidesLastName"])){
        exit(alert("Lütfen <b>Soyadınızı</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "warning"));
    }
    if (!isset($_POST["sidesDateOfBirth"]) || empty($_POST["sidesDateOfBirth"])){
        exit(alert("Lütfen <b>Doğum Tarihinizi</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "warning"));
    }
    if (!isset($_POST["sidesNumber"]) || empty($_POST["sidesNumber"])){
        exit(alert("Lütfen <b>TC Kimlik Numaranızı</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "warning"));
    }
    $_POST["sidesFirstName"] = security($_POST["sidesFirstName"]);
    $_POST["sidesLastName"] = security($_POST["sidesLastName"]);
    $_POST["sidesDateOfBirth"] = security($_POST["sidesDateOfBirth"]);
    $_POST["sidesNumber"] = security($_POST["sidesNumber"]);

    if (strlen($_POST["sidesNumber"]) != 11 || !is_numeric($_POST["sidesNumber"]) || sidesNumberControl($_POST["sidesNumber"]) != true){
        exit(alert("Lütfen geçerli bir <b>TC Kimlik Numarası</b> yazıp <b>Bilgileri Doğrula</b> butonuna tıklayınız..", "warning"));
    }

    $NVIClient = new SoapClient("https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL");

    try {
        $NVIClientResult = $NVIClient->TCKimlikNoDogrula([
            'TCKimlikNo' => $_POST["sidesNumber"],
            'Ad' => $_POST["sidesFirstName"],
            'Soyad' => $_POST["sidesLastName"],
            'DogumYili' => date("Y", strtotime($_POST["sidesDateOfBirth"]))
        ]);
        if ($NVIClientResult->TCKimlikNoDogrulaResult) {
            exit(alert("<b>TC Kimlik Numarası Doğrulama</b> işlemi başarılı bir şekilde sonuçlandı..", "success"));
        } else {
            exit(alert("Giriler bilgilerin uyuşmazlığı sebebiyle <b>TC Kimlik Numarası Doğrulama</b> işlemi başarısızlıkla sonuçlandı..", "danger"));
        }
    } catch (Exception $e) {
        exit(alert("Sistemsel bir hata ile karşılaştık. Lütfen sonra tekrar deneyiniz..", "warning"));
    }
}
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="https://www.bilgetopluluk.com.tr/Assets/Images/Logo-sm.ico" type="image/x-icon"/>
        <link rel="shortcut icon" href="https://www.bilgetopluluk.com.tr/Assets/Images/Logo-sm.ico" type="image/x-icon"/>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
        
        <script>
            $(document).ready(function(){
                $("body").on("click", "button[type='button']", function() {
                    $("div#result").fadeOut("slow");
                    
                    setTimeout(function() { 
                        $.ajax({
                            type: "POST",
                            data: $("form#form").serialize(),
                            url: "/index.php",
                            success: function(RESULT){
                                $("div#result").fadeIn("slow");
                                $("div#result").html(RESULT);
                            }
                        });
                    }, 500);
                });

                $("button[type='button']").click();
            });
        </script>

        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Righteous&display=swap"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"/>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">

        <title>TC Kimlik Numarası Doğrulama!</title>
    </head>
    <body class="bg-light p-5 m-0">
        <div class="bg-white p-2 mx-auto my-0 shadow" style="width: 350px; border-radius: 4px;">
            <div class="w-100 p-1 m-0" id="result"></div>
            <form class="row w-100 p-0 m-0" id="form">
                <div class="col-12 p-1 m-0 form-floating">
                    <input type="text" class="form-control" name="sidesFirstName" placeholder="Adınız">
                    <label for="sidesFirstName">Adınız</label>
                </div>
                <div class="col-12 p-1 m-0 form-floating">
                    <input type="text" class="form-control" name="sidesLastName" placeholder="Soyadınız">
                    <label for="sidesLastName">Soyadınız</label>
                </div>
                <div class="col-12 p-1 m-0 form-floating">
                    <input type="date" class="form-control" name="sidesDateOfBirth" placeholder="Doğum Tarihiniz">
                    <label for="sidesDateOfBirth">Doğum Tarihiniz</label>
                </div>
                <div class="col-12 p-1 m-0 form-floating">
                    <input type="text" class="form-control" name="sidesNumber" placeholder="TC Kimlik Numaranız">
                    <label for="sidesNumber">TC Kimlik Numaranız</label>
                </div>
                <div class="col-12 p-1 m-0 btn-group" role="group">
                    <button type="reset" class="btn btn-warning">
                        <i class="bi bi-x-lg p-0 m-0 d-block text-center" style="font-size: 24px;"></i>
                        <span class="p-0 m-0 d-block text-center" style="font-size: 12px;"> Bilgileri Temizle</span>
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-pencil-square p-0 m-0 d-block text-center" style="font-size: 24px;"></i>
                        <span class="p-0 m-0 d-block text-center" style="font-size: 12px;"> Bilgileri Doğrula</span>
                    </button>
                </div>
            </form>
        </div>
    </body>
</html>
