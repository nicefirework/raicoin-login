<?php
session_start();
$session_expired = 20*60; //Set session in seconds 
//create random login session
if (!isset($_SESSION["random"])) {
	$random = rand(10,100)/100;
	$_SESSION["session_time"] = time();
	$_SESSION["random"] = $random;
}
//Redirect if session expired 
$session_left = $session_expired - (time() - $_SESSION["session_time"]);
if((time() - $_SESSION["session_time"]) > $session_expired)
{
	unset($_SESSION['random']);
	unset($_SESSION['session_time']);
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  
		<title>Raicoin Login</title>
		<link rel="stylesheet" href="css/style.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>	
		<style>
			#qrcode {
			  width:200px;
			  height:200px;
			  margin-top:30px;
			  margin-bottom:30px;
			}
		</style>
	</head>
	<body>
		<div class="wrapper active">
		<?php if (isset($_SESSION["login_account"]) && $_SESSION["login_check"] == TRUE){?>
			<header>
				<h1>Login Successful! 🚀</h1>
		  		<p>Your RAI address:</p>
			</header>
			
			<div class="form">
				<form action="logout.php" method="post">
					<input type="text" name="rai_address" spellcheck="false" minlength="64" maxlength="64" placeholder="RAI address" required="" value="<?php echo $_SESSION["login_account"];?>">
					<button>Logout</button>
				</form>
			</div>
		 <?php }
		 else {?>
			
		  <header>
			<h1>Login with RAI 🚀</h1>
		  </header>
		  
		  <?php 
		  	if ($_POST) {
		  ?>
		  <p>Send <?php echo $_SESSION["random"];?> 
			RAI to your own address:</p>
		  <div class="form">
		  	<form action="" method="post">
			<input type="text" name="rai_address" spellcheck="false" minlength="64" maxlength="64" placeholder="RAI address" required="" value="<?php if ($_POST) { echo trim($_POST['rai_address']);}?>">
		  	</form>
		  </div>
			<div>Waiting for your payment. <br/>Session restart in <strong><span id="time"><?php echo gmdate('i:s', $session_left);?></span></strong> minutes!</div>	  	
		  	<div class="qrcode" id="qrcode"></div>
			<script>
				/*QR Code generator*/
				function makeCode () {		
					var qrcode = new QRCode(document.getElementById("qrcode"), {
						width : 200,
						height : 200,
						colorDark : "#000000",
						colorLight : "#ffffff",
						correctLevel : QRCode.CorrectLevel.H
					});
					var text = "<?php echo $_POST['rai_address'];?>";
					qrcode.makeCode(text);
				}
				makeCode();
			</script>
			
		 	<script>
				/*Count down timer*/
				function startTimer(duration, display) {
					var timer = duration, minutes, seconds;
					setInterval(function () {
						minutes = parseInt(timer / 60, 10)
						seconds = parseInt(timer % 60, 10);
				
						minutes = minutes < 10 ? "0" + minutes : minutes;
						seconds = seconds < 10 ? "0" + seconds : seconds;
				
						display.textContent = minutes + ":" + seconds;
				
						if (--timer < 0) {
							timer = duration;
							window.location.replace('login.php');
						}
					}, 1000);
				}
				
				window.onload = function () {
					var session_duration = <?php echo $session_left;?>,
						display = document.querySelector('#time');
					startTimer(session_duration, display);
				};
			</script>
			

			<script>
			/*CHECK PAYMENT INTERMITTENTLY*/
			//read multiple fetch example: https://stackoverflow.com/questions/40981040/using-a-fetch-inside-another-fetch-in-javascript
			//read set timeout https://stackoverflow.com/questions/6685396/execute-the-setinterval-function-without-delay-the-first-time
			//https://www.freecodecamp.org/news/javascript-settimeout-how-to-set-a-timer-in-javascript-or-sleep-for-n-seconds/
			(function timeout() {
			fetch('https://rpc.raicoin.org', {
					  method: 'post',
					  body: JSON.stringify({'action': 'account_info', 'account': '<?php echo trim($_POST["rai_address"]);?>'}),
					  mode: 'cors',
					  headers: new Headers({
						  'Content-Type': 'application/json'
					  })
					})
				.then(function(response){ 
					response.json().then( function(result){
						//console.log(result);
						//get Previous Block 
						fetch('https://rpc.raicoin.org', {
							method: 'post',
							body: JSON.stringify({'action': 'block_query', 'hash': result.head_block.previous}),
							mode: 'cors',
							headers: new Headers({
								'Content-Type': 'application/json'
							})
						}).then(function(response){ 
							response.json().then( function(data){
								//console.log(result);
								//console.log(data);
								if (result.head_block.account == data.block.account && result.head_block.opcode == "receive" && result.head_block.account == "<?php echo trim($_POST["rai_address"]);?>" && result.head_block_amount/1000000000 === <?php echo $_SESSION["random"];?>) {
									//send ajax post to php script, set session login and come back to login.php
									//https://stackoverflow.com/questions/8567114/how-can-i-make-an-ajax-call-without-jquery
									//https://stackoverflow.com/questions/41707032/making-post-request-using-fetch
									//https://phpenthusiast.com/blog/javascript-fetch-api-tutorial
									if (data.block.opcode == "send" && data.block.account == data.block.link && data.block.account == "<?php echo trim($_POST["rai_address"]);?>" && data.amount/1000000000 === <?php echo $_SESSION["random"];?>) {
										var json_data = {
											"account": result.account,
											"amount": result.head_block_amount,
											"random": <?php echo $_SESSION["random"];?>
										}
										fetch('ajax.php', {
											method : 'post',
											mode: 'cors', //cors, no-cors, same-origin
											headers: {
											  'Content-Type': 'application/json', //sent request
											  'Accept': 'application/json', //expected data sent back
											  'X-Requested-With': 'XMLHttpRequest',
											  //'credentials': 'same-origin'
											},
											body: JSON.stringify(json_data)
										})
										.then((res) => res.json())
										.then(function(data) {
											console.log(data);
											//Refresh window if login success from ajax call
											if(result.account === data.account && data.success == 1){
												window.location.replace('login.php');
											}
										})
										.catch((error) => console.log(error))
										clearTimeout(timeoutId);
										//console.log('Timeout ID ' + timeoutId + ' has been cleared');
									}
								}
								else{
									//alert('session ID changed');
								}
							});
						}).catch(function (failed) {
							console.log('Request failed', failed);
						});
					});
				})				
				.catch(function (error) {
				  console.log('Request error', error);
				});

				const timeoutId = setTimeout(timeout, 8000);
			})();
			</script>
		 <?php } else {?>
		 
		  <p>Enter your RAI address:</p>
		  <div class="form">
		  	<form action="" method="post">
			<input type="text" name="rai_address" spellcheck="false" minlength="64" maxlength="64" placeholder="RAI address" required="" value="<?php if ($_POST) { echo trim($_POST['rai_address']);}?>">
			<button>Login</button>
		  	</form>
		  </div>
		 <?php }?>
		 <?php }?>
		</div>
	
	</body>
</html>