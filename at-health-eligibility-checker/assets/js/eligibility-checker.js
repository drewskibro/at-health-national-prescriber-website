/**
 * AT Health Eligibility Checker — Script
 *
 * IIFE-wrapped state, navigation, and handler functions. User-facing
 * handlers (those called from inline onclick attributes in the
 * shortcode markup) are explicitly attached to window at the bottom
 * of the file so the HTML does not need to be rewritten.
 *
 * Built up incrementally via Phase 4-1 through Phase 4-9.
 */

(function () {
	'use strict';

	// ---------- State ----------

	let state = {
		currentScreen: 1,
		screenHistory: [],
		userData: {
			termsAgreed: false,
			userType: null,
			provider: null,
			currentMedication: null,
			currentDose: null,
			age: null,
			ethnicity: null,
			sex: null,
			weight: null,
			weightUnit: 'kg',
			height: null,
			heightUnit: 'cm',
			bmi: null,
			diabetes: null,
			conditions: [],
			hasBariatric: false,
			hasMentalHealth: false,
			prevMeds: [],
			prevMedsToAsk: [],
			currentMedIndex: 0,
			prevWeights: {},
			fullName: null,
			firstName: null,
			lastName: null,
			email: null,
			phone: null,
			dob: null,
			addressLine1: null,
			addressLine2: null,
			city: null,
			postcode: null,
			country: 'UK',
			selectedTreatment: 'wegovy'
		},
		ineligibleReason: '',
		selectedWegovyDose: '0.25mg',
		selectedMounjaroDose: '2.5mg',
		weightUnit: 'kg',
		heightUnit: 'cm',
		validationError: '',
		weightError: '',
		heightError: '',
		addressError: '',
		pregnantAnswer: null,
		breastfeedingAnswer: null,
		conceiveAnswer: null,
		earlyFirstName: '',
		earlyLastName: '',
		earlyEmail: '',
		earlyPhone: '',
		earlyFormError: '',
		weightKg: '',
		weightStone: '',
		weightPounds: '',
		heightCm: '',
		heightFeet: '',
		heightInches: '',
		goalWeightUnit: 'kg'
	};

	const wegovyPricing = {
		'0.25mg': 149.99,
		'0.5mg': 149.99,
		'1mg': 149.99,
		'1.7mg': 204.99,
		'2.4mg': 259.99
	};

	const mounjaroPricing = {
		'2.5mg': 149.99,
		'5mg': 179.99,
		'7.5mg': 229.99,
		'10mg': 249.99,
		'12.5mg': 274.99,
		'15mg': 299.99
	};

	// ---------- Progress bar ----------

	function getProgressPercentage(screen) {
		const baseScreens = 20;
		const screenMapping = {
			'1b': 4.5,
			'3a': 3.3,
			'3b': 3.6,
			'6b': 6.5,
			'17a': 17.5,
			'10a': 10.3,
			'10b': 10.6,
			'11a': 11.5,
			'12a': 12.5,
			'13-weight': 13.5,
			'14a': 14.5,
			'15a': 15.5,
			'15b': 15.7
		};

		let numericScreen;
		if (typeof screen === 'string') {
			numericScreen = screenMapping[screen] || 15;
		} else {
			numericScreen = screen;
		}

		return Math.round((numericScreen / baseScreens) * 100);
	}

	function updateProgressBar() {
		const percentage = getProgressPercentage(state.currentScreen);
		const fill = document.getElementById('progressBarFill');
		if (fill) {
			fill.style.width = percentage + '%';
		}

		const container = document.getElementById('progressBarContainer');
		if (!container) {
			return;
		}
		if (state.currentScreen === 1 || state.currentScreen === 'ineligible') {
			container.style.display = 'none';
		} else {
			container.style.display = 'block';
		}
	}

	// ---------- Screen navigation ----------

	function showScreen(screenId) {
		document.querySelectorAll('.screen').forEach(function (screen) {
			screen.classList.remove('active');
		});

		const screen = document.getElementById('screen-' + screenId);
		if (screen) {
			screen.classList.add('active');
			state.currentScreen = screenId;
			updateProgressBar();

			// Scroll to the top of the checker wrapper (not the page top),
			// so the widget feels self-contained when embedded in WordPress.
			const root = document.getElementById('at-health-checker-root');
			if (root && typeof root.scrollIntoView === 'function') {
				root.scrollIntoView({ block: 'start' });
			}
		}
	}

	function goBack() {
		if (state.screenHistory.length > 0) {
			const previousScreen = state.screenHistory.pop();
			showScreen(previousScreen);
		}
	}

	function addToHistory() {
		state.screenHistory.push(state.currentScreen);
	}

	function showIneligible(reason) {
		state.ineligibleReason = reason;
		const reasonEl = document.getElementById('ineligibleReason');
		if (reasonEl) {
			reasonEl.textContent = reason;
		}
		showScreen('ineligible');
	}

	function reviewAnswers() {
		state.screenHistory = [];
		showScreen(1);
	}

	// ---------- Handler blocks (populated in Phase 4-2 through 4-9) ----------
	// ---------- Screen 1 (agreement) ----------

	function agreeAndStart() {
		state.userData.termsAgreed = true;
		addToHistory();
		showScreen(2);
	}

	// ---------- Screen 1b (early contact details) ----------

	function saveEarlyDetails() {
		const firstName = document.getElementById('earlyFirstName').value.trim();
		const lastName = document.getElementById('earlyLastName').value.trim();
		const email = document.getElementById('earlyEmail').value.trim();
		const phone = document.getElementById('earlyPhone').value.trim();

		const errorDiv = document.getElementById('earlyFormError');

		if (!firstName || !lastName || !email || !phone) {
			errorDiv.textContent = 'Please fill in all fields';
			errorDiv.style.display = 'block';
			return;
		}

		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(email)) {
			errorDiv.textContent = 'Please enter a valid email address';
			errorDiv.style.display = 'block';
			return;
		}

		const phoneRegex = /^[0-9]{10,11}$/;
		const cleanPhone = phone.replace(/\s/g, '');
		if (!phoneRegex.test(cleanPhone)) {
			errorDiv.textContent = 'Please enter a valid UK phone number (10-11 digits)';
			errorDiv.style.display = 'block';
			return;
		}

		state.userData.firstName = firstName;
		state.userData.lastName = lastName;
		state.userData.fullName = firstName + ' ' + lastName;
		state.userData.email = email;
		state.userData.phone = phone;

		errorDiv.style.display = 'none';
		addToHistory();
		showScreen(5);
	}

	// ---------- Screen 2 (new vs switching) ----------

	function selectNewUser() {
		state.userData.userType = 'new';
		addToHistory();
		showScreen(4);
	}

	function selectSwitching() {
		state.userData.userType = 'switching';
		addToHistory();
		showScreen(3);
	}

	// ---------- Screen 3 (current provider) ----------

	function selectProvider(provider) {
		state.userData.provider = provider;
		addToHistory();
		showScreen('3a');
	}

	window.agreeAndStart = agreeAndStart;
	window.saveEarlyDetails = saveEarlyDetails;
	window.selectNewUser = selectNewUser;
	window.selectSwitching = selectSwitching;
	window.selectProvider = selectProvider;
	// HANDLERS_BLOCK_2: screens 3a, 3b, 4, 5, 6, 6b — added in phase 4-3
	// HANDLERS_BLOCK_3: screens 7, 8 — added in phase 4-4
	// HANDLERS_BLOCK_4: screens 9, 10, 10a, 10b — added in phase 4-5
	// HANDLERS_BLOCK_5: screens 11, 11a, 12, 12a — added in phase 4-6
	// HANDLERS_BLOCK_6: screens 13, 13-weight, 14, 14a, 15, 15a, 15b — added in phase 4-7
	// HANDLERS_BLOCK_7: screens 16, 17, 18, 19, 20 — added in phase 4-8
	// HANDLERS_BLOCK_8: screen 21 setup + proceedToCheckout — added in phase 4-9

	// ---------- Expose handlers on window for inline onclick attributes ----------
	// Additional window.* exports are added alongside each handler block above.

	window.goBack = goBack;
	window.reviewAnswers = reviewAnswers;

	// ---------- Initialize ----------

	showScreen(1);
})();
