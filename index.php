<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Assignment 3">
    <meta name="author" content="Michael D'mello">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment 3</title>
    <style>
        /* Give tables, trs, and tds solid 1px borders */
        table, tr, td {
            border: 1px solid black;
        }
    </style>
    <?php include "./functions.inc.php"; ?>
</head>
    <body>
    <?php
        //Log errors to file
        ini_set('log_errors', 1);
        ini_set('error_log', "./error.log");
    ?>
        <form method="POST">
            <fieldset>
                <legend>What do?</legend>
                <label for="tv_type">TV Type:</label>
                <?php select("tv_type", ["--", "LCD", "LED", "OLED", "QLED"]); ?>
                <br><br>
                <h3>Brand:</h3>
                <input type="radio" name="brand" id="brandLG" value="LG"><label for="brandLG">LG</label>
                <input type="radio" name="brand" id="brandSamsung" value="Samsung"><label for="brandSamsung">Samsung</label>
                <input type="radio" name="brand" id="brandSony" value="Sony"><label for="brandSony">Sony</label>
                <input type="radio" name="brand" id="brandToshiba" value="Toshiba"><label for="brandToshiba">Toshiba</label>
                <br><br>
                <label for="model">Model:</label>
                <input type="text" name="model" id="model"/>
                <br><br>
                <label for="size">Size:</label>
                <input type="number" name="size" id="size"/>
                <br><br>
                <label for="price">Price:</label>
                <input type="number" name="price" id="price"/>
                <br><br>
                <label for="saleprice">Sale price:</label>
                <input type="number" name="saleprice" id="saleprice"/>
                <br><br>
                <label for="description">Description:</label>
                <textarea name="description" id="description" placeholder="Enter a description about this product"></textarea>

                <input type="submit" value="Search"/>
            </fieldset>
        </form>
        <script src=https://my.gblearn.com/js/loadscript.js></script>
        <hr>
        <?php show_source(__file__)?>
    </body>
</html>
