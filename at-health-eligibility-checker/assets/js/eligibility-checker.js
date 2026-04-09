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
	// ---------- Screen 3a (current medication) ----------

	function selectCurrentMedication(med) {
		state.userData.currentMedication = med;
		addToHistory();
		showScreen('3b');

		// Populate doses for the selected medication.
		const heading = document.getElementById('screen3bHeading');
		const radioGroup = document.getElementById('doseRadioGroup');
		radioGroup.innerHTML = '';

		let doses;
		if (med === 'wegovy') {
			heading.textContent = 'What dose of Wegovy are you currently taking?';
			doses = [
				{ value: '0.25mg', label: '0.25mg (starter dose)' },
				{ value: '0.5mg', label: '0.5mg' },
				{ value: '1mg', label: '1mg' },
				{ value: '1.7mg', label: '1.7mg' },
				{ value: '2.4mg', label: '2.4mg (maximum dose)' }
			];
		} else {
			heading.textContent = 'What dose of Mounjaro are you currently taking?';
			doses = [
				{ value: '2.5mg', label: '2.5mg (starter dose)' },
				{ value: '5mg', label: '5mg' },
				{ value: '7.5mg', label: '7.5mg' },
				{ value: '10mg', label: '10mg' },
				{ value: '12.5mg', label: '12.5mg' },
				{ value: '15mg', label: '15mg (maximum dose)' }
			];
		}

		doses.forEach(function (dose) {
			const item = document.createElement('div');
			item.className = 'radio-item';
			item.onclick = function () {
				selectDose(dose.value);
			};
			item.innerHTML =
				'<input type="radio" name="dose" class="radio-input" value="' + dose.value + '">' +
				'<label>' + dose.label + '</label>';
			radioGroup.appendChild(item);
		});
	}

	// ---------- Screen 3b (current dose) ----------

	function selectDose(dose) {
		state.userData.currentDose = dose;
		addToHistory();
		showScreen(4);
	}

	// ---------- Screen 4 (age) ----------

	function selectAge(age) {
		state.userData.age = age;
		if (age === 'under18') {
			showIneligible('Unfortunately, this treatment is only suitable for adults aged 18-74. Please speak to your GP about weight management options suitable for your age group.');
		} else if (age === '75+') {
			showIneligible('Unfortunately, this treatment is only suitable for adults aged 18-74. Please speak to your GP about weight management options.');
		} else {
			addToHistory();
			showScreen('1b');
		}
	}

	// ---------- Screen 5 (ethnicity) ----------

	function selectEthnicity(ethnicity) {
		state.userData.ethnicity = ethnicity;
		addToHistory();
		showScreen(6);
	}

	// ---------- Screen 6 (sex at birth) ----------

	function selectSex(sex) {
		state.userData.sex = sex;
		addToHistory();
		if (sex === 'female') {
			state.pregnantAnswer = null;
			state.breastfeedingAnswer = null;
			state.conceiveAnswer = null;
			document.querySelectorAll('.yes-no-button').forEach(function (btn) {
				btn.classList.remove('selected');
			});
			document.getElementById('screen6bContinue').disabled = true;
			showScreen('6b');
		} else {
			showScreen(7);
		}
	}

	// ---------- Screen 6b (pregnancy safety questions) ----------

	function setPregnant(answer) {
		state.pregnantAnswer = answer;
		document.getElementById('pregnant-yes').classList.remove('selected');
		document.getElementById('pregnant-no').classList.remove('selected');
		document.getElementById('pregnant-' + answer).classList.add('selected');
		checkScreen6bComplete();
	}

	function setBreastfeeding(answer) {
		state.breastfeedingAnswer = answer;
		document.getElementById('breastfeeding-yes').classList.remove('selected');
		document.getElementById('breastfeeding-no').classList.remove('selected');
		document.getElementById('breastfeeding-' + answer).classList.add('selected');
		checkScreen6bComplete();
	}

	function setConceive(answer) {
		state.conceiveAnswer = answer;
		document.getElementById('conceive-yes').classList.remove('selected');
		document.getElementById('conceive-no').classList.remove('selected');
		document.getElementById('conceive-' + answer).classList.add('selected');
		checkScreen6bComplete();
	}

	function checkScreen6bComplete() {
		if (state.pregnantAnswer && state.breastfeedingAnswer && state.conceiveAnswer) {
			document.getElementById('screen6bContinue').disabled = false;
		}
	}

	function continueFrom6b() {
		if (state.pregnantAnswer === 'yes' || state.breastfeedingAnswer === 'yes' || state.conceiveAnswer === 'yes') {
			showIneligible('Unfortunately, this treatment is not suitable during pregnancy, breastfeeding, or if you are trying to conceive. Please speak to your GP about alternative weight management options.');
		} else {
			addToHistory();
			showScreen(7);
		}
	}

	window.selectCurrentMedication = selectCurrentMedication;
	window.selectAge = selectAge;
	window.selectEthnicity = selectEthnicity;
	window.selectSex = selectSex;
	window.setPregnant = setPregnant;
	window.setBreastfeeding = setBreastfeeding;
	window.setConceive = setConceive;
	window.continueFrom6b = continueFrom6b;
	// ---------- Screen 7 (weight) ----------

	function setWeightUnit(unit) {
		state.weightUnit = unit;
		document.getElementById('weight-kg-toggle').classList.toggle('active', unit === 'kg');
		document.getElementById('weight-st-toggle').classList.toggle('active', unit === 'st');
		document.getElementById('weightInputKg').style.display = unit === 'kg' ? 'block' : 'none';
		document.getElementById('weightInputSt').style.display = unit === 'st' ? 'block' : 'none';
	}

	function saveWeight() {
		const errorDiv = document.getElementById('weightError');
		errorDiv.style.display = 'none';

		let weightKg;

		if (state.weightUnit === 'kg') {
			weightKg = parseFloat(document.getElementById('weightKg').value);
			if (!weightKg || weightKg < 40 || weightKg > 250) {
				errorDiv.textContent = 'Please enter a valid weight between 40 and 250 kg';
				errorDiv.style.display = 'block';
				return;
			}
		} else {
			const stone = parseFloat(document.getElementById('weightStone').value) || 0;
			const pounds = parseFloat(document.getElementById('weightPounds').value) || 0;

			if (stone < 6 || stone > 40) {
				errorDiv.textContent = 'Please enter a valid weight';
				errorDiv.style.display = 'block';
				return;
			}

			weightKg = (stone * 6.35029) + (pounds * 0.453592);
		}

		state.userData.weight = weightKg;
		state.userData.weightUnit = state.weightUnit;
		addToHistory();
		showScreen(8);
	}

	// ---------- Screen 8 (height + BMI calculation) ----------

	function setHeightUnit(unit) {
		state.heightUnit = unit;
		document.getElementById('height-cm-toggle').classList.toggle('active', unit === 'cm');
		document.getElementById('height-ft-toggle').classList.toggle('active', unit === 'ft');
		document.getElementById('heightInputCm').style.display = unit === 'cm' ? 'block' : 'none';
		document.getElementById('heightInputFt').style.display = unit === 'ft' ? 'block' : 'none';
	}

	function saveHeight() {
		const errorDiv = document.getElementById('heightError');
		errorDiv.style.display = 'none';

		let heightCm;

		if (state.heightUnit === 'cm') {
			heightCm = parseFloat(document.getElementById('heightCm').value);
			if (!heightCm || heightCm < 120 || heightCm > 230) {
				errorDiv.textContent = 'Please enter a valid height between 120 and 230 cm';
				errorDiv.style.display = 'block';
				return;
			}
		} else {
			const feet = parseFloat(document.getElementById('heightFeet').value) || 0;
			const inches = parseFloat(document.getElementById('heightInches').value) || 0;

			if (feet < 4 || feet > 7) {
				errorDiv.textContent = 'Please enter a valid height';
				errorDiv.style.display = 'block';
				return;
			}

			heightCm = (feet * 30.48) + (inches * 2.54);
		}

		state.userData.height = heightCm;
		state.userData.heightUnit = state.heightUnit;

		// Calculate BMI from cm + kg.
		const heightM = heightCm / 100;
		const bmi = state.userData.weight / (heightM * heightM);
		state.userData.bmi = bmi.toFixed(1);

		addToHistory();
		showScreen(9);
	}

	window.setWeightUnit = setWeightUnit;
	window.saveWeight = saveWeight;
	window.setHeightUnit = setHeightUnit;
	window.saveHeight = saveHeight;
	// ---------- Screen 9 (diabetes) ----------

	function selectDiabetes(type) {
		state.userData.diabetes = type;
		addToHistory();
		showScreen(10);
	}

	// ---------- Screen 10 (medical history conditions) ----------

	function toggleCondition(condition) {
		const checkbox = document.getElementById('condition-' + condition);
		const item = checkbox.parentElement;

		if (condition === 'none') {
			if (checkbox.checked) {
				document.querySelectorAll('#conditions-checkbox-group .checkbox-input').forEach(function (cb) {
					if (cb.id !== 'condition-none') {
						cb.checked = false;
						cb.parentElement.classList.remove('selected');
					}
				});
				item.classList.add('selected');
				state.userData.conditions = ['none'];
			} else {
				item.classList.remove('selected');
				state.userData.conditions = [];
			}
		} else {
			const noneCheckbox = document.getElementById('condition-none');
			if (noneCheckbox.checked) {
				noneCheckbox.checked = false;
				noneCheckbox.parentElement.classList.remove('selected');
			}

			if (checkbox.checked) {
				item.classList.add('selected');
				if (!state.userData.conditions.includes(condition)) {
					state.userData.conditions.push(condition);
				}
			} else {
				item.classList.remove('selected');
				state.userData.conditions = state.userData.conditions.filter(function (c) {
					return c !== condition;
				});
			}
		}
	}

	function continueFromScreen10() {
		const errorDiv = document.getElementById('screen10Error');

		if (state.userData.conditions.length === 0) {
			errorDiv.textContent = 'Please select at least one option';
			errorDiv.style.display = 'block';
			return;
		}

		errorDiv.style.display = 'none';

		const ineligibleConditions = ['cancer', 'pancreatitis', 'eating-disorder'];
		const hasIneligible = state.userData.conditions.some(function (c) {
			return ineligibleConditions.includes(c);
		});

		if (hasIneligible) {
			showIneligible('Unfortunately, based on your medical history, this treatment may not be suitable for you. Please speak to your GP about alternative weight management options.');
			return;
		}

		if (state.userData.conditions.includes('bariatric')) {
			state.userData.hasBariatric = true;
			addToHistory();
			showScreen('10a');
		} else {
			addToHistory();
			showScreen(11);
		}
	}

	// ---------- Screen 10a (bariatric recency) ----------

	function selectBariatricRecent(answer) {
		if (answer === 'yes') {
			showIneligible('Unfortunately, this treatment is not suitable within 6 months of bariatric surgery. Please speak to your GP or bariatric surgeon about appropriate weight management options.');
		} else {
			addToHistory();
			showScreen('10b');
		}
	}

	// ---------- Screen 10b (bariatric details) ----------

	function continueFromScreen10b() {
		const details = document.getElementById('bariatricDetails').value;
		state.userData.bariatricDetails = details;
		addToHistory();
		showScreen(11);
	}

	window.selectDiabetes = selectDiabetes;
	window.toggleCondition = toggleCondition;
	window.continueFromScreen10 = continueFromScreen10;
	window.selectBariatricRecent = selectBariatricRecent;
	window.continueFromScreen10b = continueFromScreen10b;
	// ---------- Screen 11 (weight-related conditions) ----------

	function toggleWeightCondition(condition) {
		const checkbox = document.getElementById('weight-condition-' + condition);
		const item = checkbox.parentElement;

		if (condition === 'none-weight') {
			if (checkbox.checked) {
				document.querySelectorAll('#weight-conditions-checkbox-group .checkbox-input').forEach(function (cb) {
					if (cb.id !== 'weight-condition-none') {
						cb.checked = false;
						cb.parentElement.classList.remove('selected');
					}
				});
				item.classList.add('selected');
			} else {
				item.classList.remove('selected');
			}
		} else {
			const noneCheckbox = document.getElementById('weight-condition-none');
			if (noneCheckbox.checked) {
				noneCheckbox.checked = false;
				noneCheckbox.parentElement.classList.remove('selected');
			}

			if (checkbox.checked) {
				item.classList.add('selected');
			} else {
				item.classList.remove('selected');
			}
		}
	}

	function continueFromScreen11() {
		const errorDiv = document.getElementById('screen11Error');
		const checkboxes = document.querySelectorAll('#weight-conditions-checkbox-group .checkbox-input:checked');

		if (checkboxes.length === 0) {
			errorDiv.textContent = 'Please select at least one option';
			errorDiv.style.display = 'block';
			return;
		}

		errorDiv.style.display = 'none';

		const mentalHealthCheckbox = document.getElementById('weight-condition-mental-health');
		if (mentalHealthCheckbox && mentalHealthCheckbox.checked) {
			state.userData.hasMentalHealth = true;
			addToHistory();
			showScreen('11a');
		} else {
			addToHistory();
			showScreen(12);
		}
	}

	// ---------- Screen 11a (mental health details) ----------

	function continueFromScreen11a() {
		const details = document.getElementById('mentalHealthDetails').value;
		state.userData.mentalHealthDetails = details;
		addToHistory();
		showScreen(12);
	}

	// ---------- Screen 12 (other medical conditions) ----------

	function selectOtherConditions(answer) {
		if (answer === 'yes') {
			addToHistory();
			showScreen('12a');
		} else {
			addToHistory();
			showScreen(13);
		}
	}

	// ---------- Screen 12a (other conditions details) ----------

	function continueFromScreen12a() {
		const details = document.getElementById('otherConditionsDetails').value;
		state.userData.otherConditionsDetails = details;
		addToHistory();
		showScreen(13);
	}

	window.toggleWeightCondition = toggleWeightCondition;
	window.continueFromScreen11 = continueFromScreen11;
	window.continueFromScreen11a = continueFromScreen11a;
	window.selectOtherConditions = selectOtherConditions;
	window.continueFromScreen12a = continueFromScreen12a;
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
