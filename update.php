<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Assignment 3">
    <meta name="author" content="Michael D'mello">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment 3 - Edit</title>
    <link rel="stylesheet" href="css/style.css">
    <?php include "./functions.inc.php"; ?>
</head>
    <body>
        <form method="POST" action="index.php">
            <fieldset>
                <?php
                    $record = find_single_record($_GET["id"]);

                    if ($record)
                        { $record->dump_update_form(); }
                ?>
            </fieldset>
        </form>
        <?php
            $prevpage = $_SERVER["HTTP_REFERER"];
            echo "<a href=\"$prevpage\">Back</a>";
        ?>
        <hr>
        <script src=https://my.gblearn.com/js/loadscript.js></script>
        <hr>
        <?php show_source(__file__)?>
    </body>
</html>
