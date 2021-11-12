<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="description" content="Assignment 3">
    <meta name="author" content="Michael D'mello">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment 3 - Info</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include "functions.inc.php"; ?>
    <!-- Table head for all the properties of the TVRecord object -->
    <table>
        <?php
            $record = find_single_record($_GET["id"]);

            if ($record)
            {
                $out = "<tr><th>Id</th><th>Type</th><th>Brand</th><th>Model</th><th>Size</th><th>Price</th><th>Sale Price</th><th>Description</th></tr>";
                $out .= "<tr><td>{$record->id}</td><td>{$record->type}</td><td>{$record->brand}</td><td>{$record->model}</td><td>{$record->size}</td>";
                $out .= "<td>{$record->price}</td><td>{$record->sale_price}</td><td>{$record->description}</td></tr>";
                echo $out;
            }
            else
                { generate_error("Record not found"); }
        ?>
    </table>

    <?php
        $prevpage = $_SERVER["HTTP_REFERER"];
        echo "<a href=\"$prevpage\">Back</a>";
    ?>
</body>
</html>
