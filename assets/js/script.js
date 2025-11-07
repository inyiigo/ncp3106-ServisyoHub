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

// My Gawain: animated tab indicator (positions exactly under active label)
(function(){
	function onReady(cb){ if(document.readyState!=="loading"){ cb(); } else { document.addEventListener('DOMContentLoaded', cb); } }
	onReady(function(){
		var tabs = document.querySelector('.mq-tabs');
		if(!tabs) return;

		// create indicator element
		var indicator = document.createElement('span');
		indicator.className = 'mq-indicator';
		tabs.appendChild(indicator);

		var tabEls = Array.prototype.slice.call(tabs.querySelectorAll('.mq-tab'));

		function updateIndicator(animate){
			var active = tabs.querySelector('.mq-tab.active') || tabEls[0];
			if(!active) return;
			var tabsRect = tabs.getBoundingClientRect();
			var aRect = active.getBoundingClientRect();
			var left = aRect.left - tabsRect.left;
			var width = aRect.width;
			// ensure pixel values
			indicator.style.left = Math.round(left) + 'px';
			indicator.style.width = Math.round(width) + 'px';
			if(!animate){
				indicator.style.transition = 'none';
				// force layout then restore
				void indicator.offsetWidth;
				indicator.style.transition = '';
			}
		}

		// Update on load (no animation)
		updateIndicator(false);

		// Update on window resize
		var resizeTimer = null;
		window.addEventListener('resize', function(){
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function(){ updateIndicator(false); }, 80);
		});

		// Click handlers: make clicked tab active and animate indicator
		tabEls.forEach(function(el){
			el.addEventListener('click', function(e){
				var href = el.getAttribute('href') || '';
				var isSamePage = href.indexOf('?tab=') !== -1 || href.indexOf('#') === 0 || href === '';
				// if this is a same-page tab link, prevent full navigation and handle via history API
				if(isSamePage){
					e.preventDefault();
					tabEls.forEach(function(t){ t.classList.remove('active'); });
					el.classList.add('active');
					updateIndicator(true);
					// update URL without reload
					try{ history.pushState({}, '', href || window.location.pathname); }catch(err){}
					// emit a custom event so pages can react to tab changes
					var params = (href.indexOf('?')!==-1) ? new URLSearchParams(href.split('?')[1]) : new URLSearchParams(window.location.search);
					var tabName = params.get('tab') || '';
					window.dispatchEvent(new CustomEvent('tabchange', { detail: { tab: tabName } }));
				} else {
					// normal navigation
					// allow link to proceed
				}
			});
		});

		// React to browser navigation (back/forward) and notify listeners
		window.addEventListener('popstate', function(){
			// determine active tab from URL
			var params = new URLSearchParams(window.location.search);
			var tabName = params.get('tab') || '';
			// update active classes
			tabEls.forEach(function(t){ t.classList.toggle('active', (t.getAttribute('href')||'').indexOf('tab='+tabName) !== -1); });
			updateIndicator(false);
			window.dispatchEvent(new CustomEvent('tabchange', { detail: { tab: tabName } }));
		});
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

		// Use tab-scoped storage key so Offered and Posted filters don't collide
		var tab = modal.getAttribute('data-tab') || 'default';
		var storageKey = 'mqFilters_' + tab;

		function loadSaved(){
			try{
				var data = JSON.parse(localStorage.getItem(storageKey) || '{}');
				if(data && Array.isArray(data.status)){
					form.querySelectorAll('input[type="checkbox"]').forEach(function(el){ el.checked = data.status.indexOf(el.value) !== -1; });
				}
				updateLabelFromForm();
			}catch(e){ /* noop */ }
		}

		function save(){
			var selected = [];
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function(el){ selected.push(el.value); });
			localStorage.setItem(storageKey, JSON.stringify({ status: selected }));
		}

		function open(){
			modal.classList.add('active');
			// reflect accessibility state used by page CSS
			modal.setAttribute('aria-hidden', 'false');
			openBtn.setAttribute('aria-expanded','true');
		}
		function close(){
			modal.classList.remove('active');
			// reflect accessibility state used by page CSS
			modal.setAttribute('aria-hidden', 'true');
			openBtn.setAttribute('aria-expanded','false');
		}

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
