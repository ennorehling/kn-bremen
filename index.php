<html>
<head>
<title>KN-Bremen DynIP</title>
</head>
<body bgcolor="white">
    <form action="dynip.php" method="POST">
        <input type="text" name="host" id="host"/>
        <label for="host">Host</label><br/>
        <input type="password" name="pass" id="pass"/>
        <label for="pass">Passwort</label><br/>
        <input type="text" name="ip" id="ip"/>
        <label for="ip">Adresse</label><br/>
        <input type="hidden" name="action" value="register"/>
        <input type="hidden" name="norebuild" value="1"/>
        <button type="submit" id="submitButton">Submit</button>
    </form>
</body>
</html>
