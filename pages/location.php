<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Location • ServisyoHub</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.container {
	max-width: 920px;
	margin: 0 auto;
	padding: 18px;
	display: grid;
	place-items: center;
	min-height: calc(100vh - 64px);
	box-sizing: border-box;
	position: relative;
	z-index: 1;
	opacity: 0;
	animation: fadeIn 0.8s ease-in-out forwards;
}
.header { margin-bottom: 18px; display:flex; align-items:center; gap:12px; justify-content:center; }
.header h1 { margin:0; text-align:center; width:100%; }
.location-card { 
	padding: 22px; 
	border-radius:12px; 
	width:100%; 
	max-width:900px;
	background: #0078a6 !important;
	color: #fff;
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
}
.section { margin-bottom: 18px; }
.section h3 { margin:0 0 8px; font-size:1.05rem; color: #fff; }
.section p { margin:0 0 8px; color: rgba(255,255,255,.85); line-height:1.45; }
.section p strong { color: #fff; }
.small { font-size:0.92rem; color: rgba(255,255,255,.75); }

#map {
	width: 100%;
	height: 340px;
	border-radius: 12px;
	box-shadow: 0 4px 14px rgba(0,0,0,0.08);
	margin-bottom: 10px;
}

.bottom-box {
	position: fixed;
	right: 20px;
	bottom: 20px;
	z-index: 999;
	background: transparent;
	border: none;
	padding: 0;
	border-radius: 0;
	box-shadow: none;
	opacity: 0;
	animation: fadeIn 1s ease-in-out 0.3s forwards;
}
.back-box {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	border-radius: 10px;
	background: #0078a6;
	color: #fff;
	text-decoration: none;
	font-weight: 700;
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
	box-shadow: 0 6px 18px rgba(0,120,166,.24);
}
.back-box:hover {
	transform: translateY(-4px) scale(1.02);
	box-shadow: 0 12px 28px rgba(0,120,166,.32);
	background: #006a94;
	border-color: color-mix(in srgb, #0078a6 60%, #0000);
}
@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

/* page override: white background */
body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

/* Blue bottom border on topbar */
.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

/* Background logo - transparent and behind UI */
.bg-logo {
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 25%;
	max-width: 350px;
	opacity: 0.15;
	z-index: 0;
	pointer-events: none;
}
.bg-logo img {
	width: 100%;
	height: auto;
	display: block;
}

/* centered floating bottom navigation */
/* REMOVED bottom nav styles */
/*
.dash-bottom-nav {
	position: fixed;
	left: 50%;
	right: auto;
	bottom: 16px;
	z-index: 1000;
	width: max-content;
	transform: translateX(-50%) scale(0.92);
	transform-origin: bottom center;
	transition: transform 180ms ease, box-shadow 180ms ease;
	border: 3px solid #0078a6;
	background: transparent;
}
.dash-bottom-nav:hover {
	transform: translateX(-50%) scale(1);
	box-shadow: 0 12px 28px rgba(2,6,23,.12);
}
*/
@media (max-width:520px) {
	.bottom-box { left: 12px; right: 12px; bottom: 14px; display:flex; justify-content:center; }
	.back-box { width:100%; justify-content:center; }
}
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<main class="container">
		<article class="form-card glass-card location-card" role="main" aria-labelledby="location-title">
			<header class="header">
				<h1 id="location-title">Our Location</h1>
			</header>

			<section class="section">
				<h3>Head Office</h3>
				<p>ServisyoHub Headquarters is located inside the University of the East, Recto Avenue, Manila.</p>
				<p><strong>Address:</strong><br>University of the East, 2219 Recto Ave, Sampaloc, Manila, 1008 Metro Manila</p>
				<p><strong>Operating Hours:</strong><br>Monday – Friday, 9:00 AM – 6:00 PM</p>
			</section>

			<section class="section">
				<h3>Find Us on the Map</h3>
				<p>Your live location and our office will appear below (please allow location access).</p>
				<div id="map"></div>
				<div id="userLocation" class="small" style="text-align:center;"></div>
			</section>

			<footer class="small" style="margin-top:12px;">
				<p>Last updated: <?php echo date('Y-m-d'); ?></p>
			</footer>
		</article>
	</main>

	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./settings.php" class="back-box" title="Back to settings">← Back to settings</a>
	</div>

	<!-- REMOVED floating bottom navigation -->
	<!--
	<nav class="dash-bottom-nav">
		<a href="./home-gawain.php" aria-label="Home">...</a>
		<a href="./my-gawain.php" aria-label="My Gawain">...</a>
		<a href="./profile.php" aria-label="Profile">...</a>
	</nav>
	-->

	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script>
	// ServisyoHub HQ (University of the East - Recto)
	const hubLocation = [14.601307, 120.989349];
	const map = L.map('map').setView(hubLocation, 15);

	// OpenStreetMap layer
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap contributors'
	}).addTo(map);

	// ServisyoHub marker
	const hubMarker = L.marker(hubLocation)
		.addTo(map)
		.bindPopup("<b>ServisyoHub HQ</b><br>University of the East<br>Recto Ave, Manila")
		.openPopup();

	// Track user location live
	if (navigator.geolocation) {
		navigator.geolocation.watchPosition(
			position => {
				const userLat = position.coords.latitude;
				const userLng = position.coords.longitude;
				const userPos = [userLat, userLng];

				// If user marker exists, update position; else create it
				if (window.userMarker) {
					window.userMarker.setLatLng(userPos);
				} else {
					window.userMarker = L.marker(userPos, { icon: L.icon({
						iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
						iconSize: [32, 32],
						iconAnchor: [16, 32]
					})})
					.addTo(map)
					.bindPopup("📍 You are here")
					.openPopup();
				}

				// Show coordinates
				document.getElementById("userLocation").innerHTML =
					`📍 Your location:<br>Latitude: ${userLat.toFixed(5)}, Longitude: ${userLng.toFixed(5)}`;

				// Fit both markers into view
				const group = L.featureGroup([hubMarker, window.userMarker]);
				map.fitBounds(group.getBounds(), { padding: [40, 40] });
			},
			() => {
				document.getElementById("userLocation").textContent =
					"⚠️ Please allow location access to see your position.";
			}
		);
	} else {
		document.getElementById("userLocation").textContent =
			"❌ Your browser does not support geolocation.";
	}	
	</script>
</body>
</html>
