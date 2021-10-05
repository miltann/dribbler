<?php
require 'inc.php';

?>
<head>
<title>Lernraum Bot</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container">
<div class="card">
  <div class="card-body">
    <form action="login.php" method="post">
        <div class="col justify-center">
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Matrikelnummer</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" name="matnr">
                    </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Password</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" name="password">
                    </div>
            </div>
            <input type="submit" value="Abschicken">
        </div>
    </form>
</div>
</div>
</div>
</body>
<html>