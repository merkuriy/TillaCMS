<?php

$dict = array(
    "а" => "a",
    "б" => "b",
    "в" => "v",
    "г" => "g",
    "д" => "d",
    "е" => "e",
    "ё" => "yo",
    "ж" => "j",
    "з" => "z",
    "и" => "i",
    "й" => "y",
    "к" => "k",
    "л" => "l",
    "м" => "m",
    "н" => "n",
    "о" => "o",
    "п" => "p",
    "р" => "r",
    "с" => "s",
    "т‚" => "t",
    "у" => "u",
    "ф„" => "f",
    "х…" => "h",
    "ц" => "c",
    "ч" => "ch",
    "ш" => "sh",
    "щ" => "sch",
    "ъ" => "",
    "ы" => "yi",
    "ь" => "",
    "э" => "e",
    "ю" => "yu",
    "я" => "ya"
);


$jsonDict = json_encode($dict);
?>
<!doctype html>
<html>
<head>

</head>
<body>
transname script

<script>
    dict = <?php echo $jsonDict ?>;
</script>
<script src="/scripts/transname.js"></script>

</body>
</html>