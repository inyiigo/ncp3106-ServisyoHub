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

// My Gawain: Filter modal interactions
(function(){
	function onReady(cb){ if(document.readyState!=="loading"){ cb(); } else { document.addEventListener("DOMContentLoaded", cb); } }
	onReady(function(){
		var openBtn = document.getElementById('mqFilterBtn');
		var modal = document.getElementById('mqFilterModal');
		if(!openBtn || !modal){ return; }
		var label = document.getElementById('mqFilterLabel');
		var form = document.getElementById('mqFilterForm');
		var resetBtn = document.getElementById('mqFilterReset');
		var applyBtn = document.getElementById('mqFilterApply');
		var closeEls = modal.querySelectorAll('[data-filter-close]');

		function updateLabelFromForm(){
			var checks = form.querySelectorAll('input[type="checkbox"]:checked');
			var count = checks.length;
			if(label){ label.textContent = count === 0 ? 'All' : String(count); }
		}

		function loadSaved(){
			try{
				var data = JSON.parse(localStorage.getItem('mqFilters')||'{}');
				if(data && Array.isArray(data.status)){
					form.querySelectorAll('input[type="checkbox"]').forEach(function(el){ el.checked = data.status.indexOf(el.value) !== -1; });
				}
				updateLabelFromForm();
			}catch(e){ /* noop */ }
		}

		function save(){
			var selected = [];
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function(el){ selected.push(el.value); });
			localStorage.setItem('mqFilters', JSON.stringify({ status: selected }));
		}

		function open(){ modal.classList.add('active'); openBtn.setAttribute('aria-expanded','true'); }
		function close(){ modal.classList.remove('active'); openBtn.setAttribute('aria-expanded','false'); }

		openBtn.addEventListener('click', function(e){ e.preventDefault(); open(); });
		closeEls.forEach(function(el){ el.addEventListener('click', function(){ close(); }); });
		document.addEventListener('keydown', function(e){ if(e.key==='Escape' && modal.classList.contains('active')){ close(); } });

		resetBtn && resetBtn.addEventListener('click', function(){
			form.querySelectorAll('input[type="checkbox"]').forEach(function(el){ el.checked = false; });
			updateLabelFromForm();
			save();
		});

		applyBtn && applyBtn.addEventListener('click', function(){
			save();
			updateLabelFromForm();
			close();
			// If/when a backend filter is ready, append query params here.
		});

		// Initialize from localStorage on load
		loadSaved();
	});
})();
