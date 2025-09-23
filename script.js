;(function(){
	'use strict'

	const loginForm = document.getElementById('loginForm')
	const signupForm = document.getElementById('signupForm')
	const toggleLoginPw = document.getElementById('toggleLoginPw')
	const toggleSignupPw = document.getElementById('toggleSignupPw')

	function togglePassword(inputId, toggleBtn){
		const input = document.getElementById(inputId)
		if(!input) return
		const isHidden = input.getAttribute('type') === 'password'
		input.setAttribute('type', isHidden ? 'text' : 'password')
		toggleBtn.textContent = isHidden ? 'Hide' : 'Show'
	}

	if(toggleLoginPw){
		toggleLoginPw.addEventListener('click', function(){
			togglePassword('loginPassword', toggleLoginPw)
		})
	}
	if(toggleSignupPw){
		toggleSignupPw.addEventListener('click', function(){
			togglePassword('signupPassword', toggleSignupPw)
		})
	}

	function setInvalid(el, isInvalid){
		if(!el) return
		if(isInvalid){
			el.classList.add('is-invalid')
		} else {
			el.classList.remove('is-invalid')
		}
	}

	if(loginForm){
		loginForm.addEventListener('submit', function(e){
			e.preventDefault()
			const username = document.getElementById('loginUsername')
			const password = document.getElementById('loginPassword')
			const uInvalid = !username.value.trim()
			const pInvalid = !password.value.trim()
			setInvalid(username, uInvalid)
			setInvalid(password, pInvalid)
			if(uInvalid || pInvalid) return
			alert('Logged in as ' + username.value.trim())
		})
	}

	if(signupForm){
		signupForm.addEventListener('submit', function(e){
			e.preventDefault()
			const username = document.getElementById('signupUsername')
			const email = document.getElementById('signupEmail')
			const password = document.getElementById('signupPassword')
			const roleClient = document.getElementById('roleClient')
			const rolePro = document.getElementById('rolePro')
			const roleFeedback = document.getElementById('roleFeedback')

			const uInvalid = !username.value.trim()
			const eInvalid = !email.validity.valid
			const pInvalid = !password.value || password.value.length < 6
			const rInvalid = !(roleClient.checked || rolePro.checked)

			setInvalid(username, uInvalid)
			setInvalid(email, eInvalid)
			setInvalid(password, pInvalid)
			roleFeedback.style.display = rInvalid ? 'block' : 'none'

			if(uInvalid || eInvalid || pInvalid || rInvalid) return
			alert('Account created for ' + username.value.trim() + ' as ' + (roleClient.checked ? 'Client' : 'Professional'))
		})
	}

	// Improve mobile focus styles on iOS Safari
	document.addEventListener('touchstart', function(){}, {passive:true})
})()
