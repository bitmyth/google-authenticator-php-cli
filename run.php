#! /usr/local/opt/php@7.2/bin/php
<?php
require(__DIR__.'/vendor/autoload.php');

$ga = new PHPGangsta_GoogleAuthenticator();

$secrets = [
	'ta' => 'SECRETONE',
	'portfolio' => 'SECRETTWO',
];

while (1) {

	fwrite(STDOUT, "Choose MFA\n");
	$i = 0;
	foreach ($secrets as $key => $secret) {
		echo "[$i]: $key " . PHP_EOL;
		$i++;
	}

	$index = intval(fgets(STDIN));
	if (array_key_exists($index, array_keys($secrets))) {
		$secret = $secrets[array_keys($secrets)[$index]];
		break;
	}
}

while (1) {
	$oneCode = $ga->getCode($secret);
	echo "\r".array_keys($secrets)[$index]." checking code: $oneCode";
	//$shell="echo -n $oneCode | /usr/bin/pbcoby";
	//echo $shell;
	//`$shell`;
	copy2clipboard($oneCode);
	usleep(100);
}


echo "Secret is: " . $secret . "\n\n";

$qrCodeUrl = $ga->getQRCodeGoogleUrl('Blog', $secret);
echo "Google Charts URL for the QR-Code: " . $qrCodeUrl . "\n\n";

$oneCode = $ga->getCode($secret);
echo "Checking Code '$oneCode' and Secret '$secret':\n";

$checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
if ($checkResult) {
	echo 'OK';
} else {
	echo 'FAILED';
}
echo PHP_EOL;

function copy2clipboard($string){
	$descriptorspec = array(
		0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		2 => array("file", "a.txt", "a") // stderr is a file to write to
	);
	$process = proc_open('pbcopy', $descriptorspec, $pipes);
	if (is_resource($process)) {
		fwrite($pipes[0], $string);
		fclose($pipes[0]);
		fclose($pipes[1]);

		$return_value = proc_close($process);
		return $return_value;
	}
}

