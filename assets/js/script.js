// Mobile nav toggle
(function() {
	function ready(fn){ if(document.readyState!=="loading"){ fn(); } else { document.addEventListener("DOMContentLoaded", fn); } }
	ready(function(){
		var btn = document.querySelector('[data-nav-toggle]');
		var overlay = document.querySelector('.dash-overlay');
		if(!btn){ return; }
		function toggle(){ document.documentElement.classList.toggle('dash-nav-open'); }
		btn.addEventListener('click', function(e){ e.preventDefault(); toggle(); });
		if(overlay){ overlay.addEventListener('click', toggle); }
	});
})();
