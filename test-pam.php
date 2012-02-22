<html>
<head>
</head>
<body>
<?php
if (isset($_POST['user']) && isset($_POST['pwd'])) {
	$username = $_POST['user'];
	$password = $_POST['pwd'];
	if (pam_auth($username, $password, &$error)) {
   	echo "Yeah baby, we're authenticated!";
	} else {
	   echo "<h3>Error: $error</h3>";
		renderForm();
	}
} else {
	renderForm();
}

function renderForm() {
	$form = <<<EOT
<h3>Login</h3>
<form action="test-pam.php" method="POST">
	<input name="user" type="text"/>
	<input name="pwd"  type="password" />
	<input type="submit"/>
</form>
EOT;
	echo $form;
}
?>
</body>
</html>
