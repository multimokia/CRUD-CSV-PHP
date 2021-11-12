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

        .red {
            color: red;
        }

        .error {
            color: red;
            font-weight: bold;
            border: 2px dotted red;
        }
    </style>
    <?php include "./functions.inc.php"; ?>
</head>
    <body>
        <form method="POST">
            <fieldset>
                <legend>What do?</legend>
                <label for="tv_type">TV Type:</label>
                <?php select("tv_type", ["--", "LCD", "LED", "OLED", "QLED"], null, true); ?>
                <br><br>
                <h3>Brand:</h3>
                <input type="radio" name="brand" id="brandLG" value="LG" required><label for="brandLG">LG</label>
                <input type="radio" name="brand" id="brandSamsung" value="Samsung"><label for="brandSamsung">Samsung</label>
                <input type="radio" name="brand" id="brandSony" value="Sony"><label for="brandSony">Sony</label>
                <input type="radio" name="brand" id="brandToshiba" value="Toshiba"><label for="brandToshiba">Toshiba</label>
                <br><br>
                <label for="model">Model:</label>
                <input type="text" name="model" id="model" required/>
                <br><br>
                <label for="size">Size:</label>
                <input type="number" name="size" id="size" required/>
                <br><br>
                <label for="price">Price:</label>
                <input type="text" name="price" id="price" pattern="^\d+(\.\d+)?$" required/>
                <br><br>
                <label for="saleprice">Sale price:</label>
                <input type="text" name="saleprice" id="saleprice" pattern="^\d+(\.\d+)?$"/>
                <br><br>
                <label for="description">Description:</label>
                <textarea name="description" id="description" placeholder="Enter a description about this product"></textarea>

                <input type="submit" value="Submit"/>
            </fieldset>
        </form>
        <hr>
        <table>
            <tr>
                <td>Id</td>
                <td>Type</td>
                <td>Brand</td>
                <td>Model</td>
                <td>Size</td>
                <td>Price</td>
                <td>Description</td>
            </tr>
            <?php
                foreach ($records as $tr)
                    { echo $tr -> __toString(); }
            ?>
        </table>
        <hr>
        <script src=https://my.gblearn.com/js/loadscript.js></script>
        <hr>
        <?php show_source(__file__)?>
    </body>
</html>
