<html>
<body>
	<form action="" method="post">
	<?php

	require_once('recaptchalib.php');

	// Get a key from https://www.google.com/recaptcha/admin/create
	$publickey = "";
	$privatekey = "";

	# the response from reCAPTCHA
	$resp = null;
	# the error code from reCAPTCHA, if any
	$error = null;

	# was there a reCAPTCHA response?
	if ($_POST["recaptcha_response_field"]) {
		$resp = recaptcha_check_answer ($privatekey,
			$_SERVER["REMOTE_ADDR"],
			sanitize_text_field($_POST["recaptcha_challenge_field"]),
			sanitize_text_field($_POST["recaptcha_response_field"]));

		if ($resp->is_valid) {
			esc_html_e( 'You got it!', 'shmapper-by-teplitsa' );
		} else {
			# set the error code so that we can display it
			$error = $resp->error;
		}
	}
	echo recaptcha_get_html($publickey, $error);
	?>
		<br/>
		<input type="submit" value="submit" />
	</form>
</body>
</html>
