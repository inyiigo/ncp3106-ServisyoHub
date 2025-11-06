<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Sign Up • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<style>
		/* Page background */
		/* Vertically center relative to screen height, keep horizontal left alignment and original left padding */
		body.signup-bg{ min-height:100dvh; margin:0; display:flex; align-items:flex-start; justify-content:flex-start; padding: clamp(20px, 6vw, 80px); padding-left: clamp(48px, 14vw, 260px); background:
			radial-gradient(1000px 580px at 15% -10%, rgba(255,255,255,.18), rgba(255,255,255,0) 60%),
			linear-gradient(180deg, rgba(0,0,0,.18), rgba(0,0,0,.18)),
			url('../assets/images/login background.png') center/cover no-repeat fixed; }

		/* Layout */
		.signup-wrap{ width:min(860px, 94vw); color:#e7f5ef; }
		/* Keep logo placement consistent with login and move it higher */
		.brand-top{ margin:0 0 12px; margin-left: clamp(-16px, -3vw, -40px); margin-top: clamp(-48px, -8vw, -128px); }
		.brand-top img{ height:clamp(48px, 8vw, 80px); display:block; }
		/* Push content block down to achieve vertical centering while keeping logo placement */
		.signup-content{ margin-top: clamp(12px, 9vh, 96px); }
		.greet-top{ margin:0 0 10px; font-weight:900; font-size: clamp(26px, 4.8vw, 44px); }
		.greet-sub{ margin:0 0 24px; font-weight:800; color: rgba(231,245,239,.9); font-size: clamp(18px, 3.4vw, 28px); }

		/* Social */
		.social-row{ display:flex; gap:12px; align-items:center; }
		.btn-social{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:999px; border:1.5px solid rgba(255,255,255,.24); background: rgba(0,0,0,.22); color:#d7f7f0; font-weight:800; text-decoration:none; }
		.btn-circle{ width:46px; height:46px; display:grid; place-items:center; border-radius:999px; border:1.5px solid rgba(255,255,255,.24); background: rgba(0,0,0,.22); color:#d7f7f0; text-decoration:none; }
		.btn-circle svg, .btn-social svg{ width:18px; height:18px; }

		/* Divider left-aligned to match input margins */
		.divider{ display:flex; align-items:center; justify-content:flex-start; color:rgba(231,245,239,.8); margin:14px 0; }
		.divider::before, .divider::after{ content:""; height:1px; background: rgba(231,245,239,.35); margin: 0 12px; flex: 0 0 auto; width: clamp(120px, 22vw, 240px); }
		.divider > span{ display:inline-block; padding: 0 10px; }

		/* Form grid */
		.sg-form{ display:grid; gap:16px; /* keep a shared gap var for password row */ --pw-gap: 8px; }
		.grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
		/* Ultra tight gap for password pair */
		.pw-grid{ gap: var(--pw-gap, 8px); }
		/* Make password columns hug their inputs so the middle gap is minimal */
		.grid-2.pw-grid{ grid-template-columns: repeat(2, 320px); justify-content: start; }
		@media (max-width: 720px){ .grid-2, .grid-2.pw-grid{ grid-template-columns:1fr; } }

		/* Password toggle styles (match login) */
		.input-with-toggle{ position:relative; }
		.input-with-toggle .control{ position:relative; }
		.input-with-toggle .pill{ padding-right:88px; }
		.toggle-visibility{ position:absolute; top:50%; right:12px; transform:translateY(-50%); height:34px; width:48px; border-radius:999px; border:0; background:transparent; color:#9de0d5; font-weight:800; cursor:pointer; display:grid; place-items:center; }
		.toggle-visibility svg{ width:18px; height:18px; color:#9de0d5; }
		.toggle-visibility .eye-closed{ display:none; }
		.toggle-visibility[aria-pressed="true"] .eye-open{ display:none; }
		.toggle-visibility[aria-pressed="true"] .eye-closed{ display:block; }
		.sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

		label{ font-weight:800; opacity:.95; display:block; margin:2px 4px 8px; }
	.pill{ width:100%; height:48px; border-radius:999px; border:1.5px solid rgba(255,255,255,.28); background:rgba(0,0,0,.28); color:#fff; font:inherit; padding:0 16px; }
	/* Shorter input variant */
	.pill.slim{ width:min(100%, 320px); }
		.pill::placeholder{ color: rgba(255,255,255,.7); }

		.agree{ display:flex; align-items:center; gap:10px; color:rgba(231,245,239,.9); margin-top:4px; }
		.agree input{ width:18px; height:18px; border-radius:4px; }
		.link{ color:#9de0d5; text-decoration:none; font-weight:800; }
		/* Small helper text under the agreement */
		/* Place below button with a small breathing space */
		.already{ color: rgba(231,245,239,.9); margin:8px 0 0; font-weight:800; }

		.cta{ margin-top:8px; height:52px; border-radius:999px; border:0; background:#7cd4c4; color:#0b2c24; font-weight:900; cursor:pointer; box-shadow:none; width: min(100%, calc(640px + var(--pw-gap, 2px))); justify-self: start; }
		@media (max-width: 720px){
			/* When password fields stack, match one input width */
			.cta{ width: min(100%, 320px); }
		}
	</style>
</head>
<body class="signup-bg page-fade">
	<main class="signup-wrap">
		<div class="brand-top"><img src="../assets/images/newlogo.png" alt="Servisyo Hub" /></div>
		<div class="signup-content">
			<h1 class="greet-top">Hello!</h1>
			<h2 class="greet-sub">We are glad to see you :)</h2>



			<form class="sg-form" action="../config/signup_act.php" method="POST" novalidate>
				<!-- Mobile number on top -->
				<div>
					<label for="mobile">Mobile number</label>
					<input type="tel" id="mobile" name="mobile" class="pill slim" placeholder="e.g. 0917 123 4567" inputmode="tel" pattern="[0-9\s+()-]{7,}" required />
				</div>

				<div class="grid-2 pw-grid">
					<div class="input-with-toggle">
						<label for="password">Password</label>
						<div class="control">
							<input type="password" id="password" name="password" class="pill slim" placeholder="Create a password" required />
							<button type="button" id="togglePassword" class="toggle-visibility" aria-label="Show password" aria-controls="password" aria-pressed="false">
								<svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" d="M12 5c-4.97 0-9 4.03-9 7 0 2.97 4.03 7 9 7s9-4.03 9-7c0-2.97-4.03-7-9-7zm0 12c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-3a2 2 0 110-4 2 2 0 010 4z"/>
								</svg>
								<svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" fill-opacity=".2" d="M12 5c-4.97 0-9 4.03-9 7 0 2.97 4.03 7 9 7s9-4.03 9-7c0-2.97-4.03-7-9-7z"/>
									<path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								</svg>
								<span class="sr-only">Show password</span>
							</button>
						</div>
					</div>
					<div class="input-with-toggle">
						<label for="confirm">Repeat Password</label>
						<div class="control">
							<input type="password" id="confirm" name="confirm" class="pill slim" placeholder="Repeat password" required />
							<button type="button" id="toggleConfirm" class="toggle-visibility" aria-label="Show password" aria-controls="confirm" aria-pressed="false">
								<svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" d="M12 5c-4.97 0-9 4.03-9 7 0 2.97 4.03 7 9 7s9-4.03 9-7c0-2.97-4.03-7-9-7zm0 12c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-3a2 2 0 110-4 2 2 0 010 4z"/>
								</svg>
								<svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" fill-opacity=".2" d="M12 5c-4.97 0-9 4.03-9 7 0 2.97 4.03 7 9 7s9-4.03 9-7c0-2.97-4.03-7-9-7z"/>
									<path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								</svg>
								<span class="sr-only">Show password</span>
							</button>
						</div>
					</div>
				</div>

				<label class="agree">
					<input type="checkbox" id="agree" required />
					<span>I agree to the <a class="link" href="./terms-and-conditions.php">Terms and Conditions</a></span>
				</label>

				<button type="submit" class="cta">Sign Up</button>

				<p class="already">Already have an account? <a class="link" href="./login.php">Login Here</a></p>
			</form>
		</div>

	</main>
	<script>
	// Page fade-in for smoother appearance after navigation
	(function(){
		if(document.readyState !== 'loading'){
			document.body.classList.add('is-ready');
		}else{
			document.addEventListener('DOMContentLoaded', function(){ document.body.classList.add('is-ready'); });
		}
	})();
	// Small client-side validation for password confirmation + show/hide toggles
	document.addEventListener('DOMContentLoaded', function(){
		var form = document.querySelector('.sg-form');
		if(form){
			form.addEventListener('submit', function(e){
				var pw = document.getElementById('password');
				var cf = document.getElementById('confirm');
				var agree = document.getElementById('agree');
				if(pw && cf && pw.value !== cf.value){
					e.preventDefault();
					alert('Passwords do not match.');
					cf.focus();
					return false;
				}
				if(!agree || !agree.checked){
					e.preventDefault();
					alert('Please agree to the Terms to continue.');
					return false;
				}
			});
		}

		// Toggle visibility for password inputs (mirrors login)
		document.querySelectorAll('.toggle-visibility').forEach(function(btn){
			btn.addEventListener('click', function(){
				var targetId = btn.getAttribute('aria-controls');
				var input = document.getElementById(targetId);
				if(!input) return;
				var isHidden = input.getAttribute('type') === 'password';
				input.setAttribute('type', isHidden ? 'text' : 'password');
				btn.setAttribute('aria-pressed', String(isHidden));
				btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
				var sr = btn.querySelector('.sr-only');
				if(sr) sr.textContent = isHidden ? 'Hide password' : 'Show password';
			});
		});
	});
	</script>
</body>
</html>
