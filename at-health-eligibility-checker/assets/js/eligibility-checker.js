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
	// ---------- Screen 13 (previous weight-loss medications) ----------

	function togglePrevMed(med) {
		const checkbox = document.getElementById('prev-med-' + med.toLowerCase().replace(/\s/g, '-'));
		const item = checkbox.parentElement;

		if (med === 'never') {
			if (checkbox.checked) {
				document.querySelectorAll('#prev-meds-checkbox-group .checkbox-input').forEach(function (cb) {
					if (cb.id !== 'prev-med-never') {
						cb.checked = false;
						cb.parentElement.classList.remove('selected');
					}
				});
				item.classList.add('selected');
				state.userData.prevMeds = ['never'];
			} else {
				item.classList.remove('selected');
				state.userData.prevMeds = [];
			}
		} else {
			const neverCheckbox = document.getElementById('prev-med-never');
			if (neverCheckbox.checked) {
				neverCheckbox.checked = false;
				neverCheckbox.parentElement.classList.remove('selected');
			}

			if (checkbox.checked) {
				item.classList.add('selected');
				if (!state.userData.prevMeds.includes(med)) {
					state.userData.prevMeds.push(med);
				}
			} else {
				item.classList.remove('selected');
				state.userData.prevMeds = state.userData.prevMeds.filter(function (m) {
					return m !== med;
				});
			}
		}
	}

	function continueFromScreen13() {
		const errorDiv = document.getElementById('screen13Error');

		if (state.userData.prevMeds.length === 0) {
			errorDiv.textContent = 'Please select at least one option';
			errorDiv.style.display = 'block';
			return;
		}

		errorDiv.style.display = 'none';

		const realMeds = state.userData.prevMeds.filter(function (m) {
			return m !== 'never';
		});
		if (realMeds.length > 0) {
			state.userData.prevMedsToAsk = realMeds;
			state.userData.currentMedIndex = 0;
			addToHistory();
			showScreen('13-weight');
			updatePrevMedWeightScreen();
		} else {
			addToHistory();
			showScreen(14);
		}
	}

	// ---------- Screen 13-weight (per-medication weight loop) ----------

	function updatePrevMedWeightScreen() {
		const med = state.userData.prevMedsToAsk[state.userData.currentMedIndex];
		document.getElementById('screen13WeightHeading').textContent =
			'What was your weight in kg before starting ' + med + '?';
	}

	function savePrevWeight() {
		const weight = document.getElementById('prevMedWeight').value;
		const med = state.userData.prevMedsToAsk[state.userData.currentMedIndex];

		if (weight) {
			state.userData.prevWeights[med] = weight;
		}

		document.getElementById('prevMedWeight').value = '';
		state.userData.currentMedIndex++;

		if (state.userData.currentMedIndex < state.userData.prevMedsToAsk.length) {
			updatePrevMedWeightScreen();
		} else {
			addToHistory();
			showScreen(14);
		}
	}

	function skipPrevWeight() {
		document.getElementById('prevMedWeight').value = '';
		state.userData.currentMedIndex++;

		if (state.userData.currentMedIndex < state.userData.prevMedsToAsk.length) {
			updatePrevMedWeightScreen();
		} else {
			addToHistory();
			showScreen(14);
		}
	}

	// ---------- Screen 14 (current prescription medications) ----------

	function selectCurrentMeds(type) {
		state.userData.currentMeds = type;
		if (type === 'other') {
			addToHistory();
			showScreen('14a');
		} else {
			addToHistory();
			showScreen(15);
		}
	}

	// ---------- Screen 14a (current meds details) ----------

	function continueFromScreen14a() {
		const details = document.getElementById('currentMedsDetails').value;
		state.userData.currentMedsDetails = details;
		addToHistory();
		showScreen(15);
	}

	// ---------- Screen 15 (allergies) ----------

	function selectAllergies(answer) {
		if (answer === 'yes') {
			addToHistory();
			showScreen('15a');
		} else {
			if (state.userData.sex === 'female') {
				addToHistory();
				showScreen('15b');
			} else {
				addToHistory();
				showScreen(16);
			}
		}
	}

	// ---------- Screen 15a (allergies details) ----------

	function continueFromScreen15a() {
		const details = document.getElementById('allergiesDetails').value;
		state.userData.allergiesDetails = details;

		if (state.userData.sex === 'female') {
			addToHistory();
			showScreen('15b');
		} else {
			addToHistory();
			showScreen(16);
		}
	}

	// ---------- Screen 15b (pregnancy / planning / breastfeeding) ----------

	function selectPregnantPlanning(answer) {
		if (answer === 'yes') {
			showIneligible('Unfortunately, this treatment is not suitable during pregnancy, if planning to become pregnant, or while breastfeeding. Please speak to your GP about alternative weight management options.');
		} else {
			addToHistory();
			showScreen(16);
		}
	}

	window.togglePrevMed = togglePrevMed;
	window.continueFromScreen13 = continueFromScreen13;
	window.savePrevWeight = savePrevWeight;
	window.skipPrevWeight = skipPrevWeight;
	window.selectCurrentMeds = selectCurrentMeds;
	window.continueFromScreen14a = continueFromScreen14a;
	window.selectAllergies = selectAllergies;
	window.continueFromScreen15a = continueFromScreen15a;
	window.selectPregnantPlanning = selectPregnantPlanning;
	// ---------- Screen 16 (goal weight yes/no) ----------

	function selectGoalWeight(answer) {
		if (answer === 'yes') {
			addToHistory();
			showScreen(17);
		} else {
			addToHistory();
			showScreen(18);
			updateCompletingAs();
		}
	}

	// ---------- Screen 17 (goal weight value) ----------

	function setGoalWeightUnit(unit) {
		state.goalWeightUnit = unit;
		document.getElementById('goal-kg-toggle').classList.toggle('active', unit === 'kg');
		document.getElementById('goal-st-toggle').classList.toggle('active', unit === 'st');
		document.getElementById('goalWeightInputKg').style.display = unit === 'kg' ? 'block' : 'none';
		document.getElementById('goalWeightInputSt').style.display = unit === 'st' ? 'block' : 'none';
	}

	function saveGoalWeight() {
		const goalKg = document.getElementById('goalWeightKg').value;
		const goalStone = document.getElementById('goalWeightStone').value;
		const goalPounds = document.getElementById('goalWeightPounds').value;

		if (state.goalWeightUnit === 'kg' && goalKg) {
			state.userData.goalWeight = goalKg;
		} else if (goalStone) {
			const weightKg = (parseFloat(goalStone) * 6.35029) + (parseFloat(goalPounds || 0) * 0.453592);
			state.userData.goalWeight = weightKg.toFixed(1);
		}

		addToHistory();
		showScreen(18);
		updateCompletingAs();
	}

	// ---------- Screen 18 (DOB + "completing as" banner) ----------

	function updateCompletingAs() {
		document.getElementById('completingAs').textContent =
			'Completing as ' + state.userData.fullName + ' · ' + state.userData.email;
	}

	function savePersonalDetails() {
		const dob = document.getElementById('dob').value;
		const errorDiv = document.getElementById('screen18Error');

		if (!dob) {
			errorDiv.textContent = 'Please enter your date of birth';
			errorDiv.style.display = 'block';
			return;
		}

		const dobDate = new Date(dob);
		const today = new Date();
		const age = today.getFullYear() - dobDate.getFullYear();
		const monthDiff = today.getMonth() - dobDate.getMonth();

		if (age < 18 || (age === 18 && monthDiff < 0)) {
			errorDiv.textContent = 'You must be at least 18 years old';
			errorDiv.style.display = 'block';
			return;
		}

		state.userData.dob = dob;
		errorDiv.style.display = 'none';
		addToHistory();
		showScreen(19);
	}

	// ---------- Screen 19 (delivery address) ----------

	function saveAddress() {
		const line1 = document.getElementById('addressLine1').value.trim();
		const city = document.getElementById('city').value.trim();
		const postcode = document.getElementById('postcode').value.trim();
		const errorDiv = document.getElementById('screen19Error');

		if (!line1 || !city || !postcode) {
			errorDiv.textContent = 'Please fill in all required fields';
			errorDiv.style.display = 'block';
			return;
		}

		const postcodeRegex = /^[A-Z]{1,2}[0-9][A-Z0-9]?\s?[0-9][A-Z]{2}$/i;
		if (!postcodeRegex.test(postcode)) {
			errorDiv.textContent = 'Please enter a valid UK postcode';
			errorDiv.style.display = 'block';
			return;
		}

		state.userData.addressLine1 = line1;
		state.userData.addressLine2 = document.getElementById('addressLine2').value.trim();
		state.userData.city = city;
		state.userData.postcode = postcode.toUpperCase();
		state.userData.country = document.getElementById('country').value;

		errorDiv.style.display = 'none';
		addToHistory();
		showScreen(20);
	}

	// ---------- Screen 20 (GP details + consent) ----------

	function saveGPDetails() {
		const gpName = document.getElementById('gpName').value.trim();
		const gpPostcode = document.getElementById('gpPostcode').value.trim();
		const consent1 = document.getElementById('gpConsent1').checked;
		const consent2 = document.getElementById('gpConsent2').checked;
		const errorDiv = document.getElementById('screen20Error');

		if (!gpName || !gpPostcode) {
			errorDiv.textContent = 'Please fill in all fields';
			errorDiv.style.display = 'block';
			return;
		}

		if (!consent1 || !consent2) {
			errorDiv.textContent = 'Please agree to both consent statements';
			errorDiv.style.display = 'block';
			return;
		}

		state.userData.gpName = gpName;
		state.userData.gpPostcode = gpPostcode;

		errorDiv.style.display = 'none';
		addToHistory();
		showScreen(21);
		setupScreen21();
	}

	window.selectGoalWeight = selectGoalWeight;
	window.setGoalWeightUnit = setGoalWeightUnit;
	window.saveGoalWeight = saveGoalWeight;
	window.savePersonalDetails = savePersonalDetails;
	window.saveAddress = saveAddress;
	window.saveGPDetails = saveGPDetails;
	// ---------- Screen 21 (success / treatment selection) ----------

	let selectedTreatment = 'wegovy';
	let selectedWegovyDose = '0.25mg';
	let selectedMounjaroDose = '2.5mg';

	function getRecommendedTreatment() {
		const bmi = parseFloat(state.userData.bmi || '0');
		const hasPrevMeds = state.userData.prevMeds &&
			state.userData.prevMeds.length > 0 &&
			!state.userData.prevMeds.includes('i have never taken medication to lose weight');
		if (bmi >= 35 || hasPrevMeds) {
			return 'mounjaro';
		}
		return 'wegovy';
	}

	function getCurrentPrice() {
		if (selectedTreatment === 'wegovy') {
			return wegovyPricing[selectedWegovyDose];
		}
		return mounjaroPricing[selectedMounjaroDose];
	}

	function updateStartTreatmentBtn() {
		const btn = document.getElementById('startTreatmentBtn');
		if (btn) {
			btn.textContent = 'Start Treatment - \u00A3' + getCurrentPrice().toFixed(2) + '/month';
		}
	}

	function updateBadges() {
		const isNewUser = state.userData.userType === 'new';
		const isSwitching = state.userData.userType === 'switching';
		const currentMed = state.userData.currentMedication;
		const recommendedTreatment = isSwitching && currentMed ? currentMed : getRecommendedTreatment();

		const wegovyBadge = document.getElementById('wegovyBadge');
		const mounjaroBadge = document.getElementById('mounjaroBadge');

		wegovyBadge.style.display = 'none';
		mounjaroBadge.style.display = 'none';

		if (selectedTreatment === 'wegovy' && isNewUser) {
			wegovyBadge.textContent = 'Selected';
			wegovyBadge.style.display = 'block';
		} else if (isSwitching && currentMed === 'wegovy' && selectedTreatment === 'wegovy') {
			wegovyBadge.textContent = 'Current Medication - Selected';
			wegovyBadge.style.display = 'block';
		} else if (isNewUser && recommendedTreatment === 'wegovy' && selectedTreatment !== 'wegovy') {
			wegovyBadge.textContent = 'Recommended';
			wegovyBadge.style.display = 'block';
		}

		if (selectedTreatment === 'mounjaro' && isNewUser) {
			mounjaroBadge.textContent = 'Selected';
			mounjaroBadge.style.display = 'block';
		} else if (isSwitching && currentMed === 'mounjaro' && selectedTreatment === 'mounjaro') {
			mounjaroBadge.textContent = 'Current Medication - Selected';
			mounjaroBadge.style.display = 'block';
		} else if (isNewUser && recommendedTreatment === 'mounjaro' && selectedTreatment !== 'mounjaro') {
			mounjaroBadge.textContent = 'Recommended';
			mounjaroBadge.style.display = 'block';
		}

		const wegovyCard = document.getElementById('wegovyCard');
		const mounjaroCard = document.getElementById('mounjaroCard');
		if (selectedTreatment === 'wegovy') {
			wegovyCard.classList.add('selected');
			wegovyCard.style.borderWidth = '3px';
			wegovyCard.style.borderColor = '#8882c8';
			mounjaroCard.classList.remove('selected');
			mounjaroCard.style.borderWidth = '2px';
			mounjaroCard.style.borderColor = '#e5e7eb';
		} else {
			mounjaroCard.classList.add('selected');
			mounjaroCard.style.borderWidth = '3px';
			mounjaroCard.style.borderColor = '#8882c8';
			wegovyCard.classList.remove('selected');
			wegovyCard.style.borderWidth = '2px';
			wegovyCard.style.borderColor = '#e5e7eb';
		}
	}

	function selectTreatmentCard(treatment) {
		selectedTreatment = treatment;
		if (state.userData.userType === 'switching') {
			if (treatment === 'wegovy' && state.userData.currentMedication !== 'wegovy') {
				selectedWegovyDose = '0.25mg';
			} else if (treatment === 'mounjaro' && state.userData.currentMedication !== 'mounjaro') {
				selectedMounjaroDose = '2.5mg';
			}
		}
		updateBadges();
		updateStartTreatmentBtn();
	}

	function setupScreen21() {
		const firstName = state.userData.firstName;
		const heading = document.getElementById('screen21Heading');
		if (firstName) {
			heading.textContent = 'Great news, ' + firstName + '!';
		} else {
			heading.textContent = "You're eligible for treatment!";
		}

		const isSwitching = state.userData.userType === 'switching';
		const currentMed = state.userData.currentMedication;
		const recommendedTreatment = isSwitching && currentMed ? currentMed : getRecommendedTreatment();

		// Set initial selected treatment.
		if (isSwitching && currentMed) {
			selectedTreatment = currentMed;
			if (currentMed === 'wegovy' && state.userData.currentDose) {
				selectedWegovyDose = state.userData.currentDose;
			} else if (currentMed === 'mounjaro' && state.userData.currentDose) {
				selectedMounjaroDose = state.userData.currentDose;
			}
		} else {
			selectedTreatment = recommendedTreatment;
		}

		// Current medication info for switching users.
		const currentMedInfo = document.getElementById('currentMedicationInfo');
		if (isSwitching && currentMed) {
			const medName = currentMed === 'wegovy' ? 'Wegovy' : 'Mounjaro';
			const doseText = state.userData.currentDose ? ' ' + state.userData.currentDose : '';
			document.getElementById('currentMedText').textContent = medName + doseText;
			currentMedInfo.style.display = 'block';
		} else {
			currentMedInfo.style.display = 'none';
		}

		// Treatment subtitle.
		const subtitleEl = document.getElementById('treatmentSubtitle');
		if (isSwitching && currentMed) {
			subtitleEl.textContent = 'Continue with your current treatment below or explore an alternative option.';
		} else if (recommendedTreatment === 'wegovy') {
			subtitleEl.textContent = 'Wegovy is recommended based on your profile';
		} else {
			subtitleEl.textContent = 'Mounjaro is recommended for enhanced results based on your profile';
		}

		// Pricing blocks — flat price for new users, dose-select dropdown for switching users.
		const isNewUser = state.userData.userType === 'new';

		if (isNewUser) {
			document.getElementById('wegovyPricing').innerHTML =
				'<div style="margin-bottom: 16px;">' +
				'<p style="color: #111827; font-size: 30px; font-weight: 700;">\u00A3199<span style="font-size: 18px; font-weight: 400; color: #6b7280;">/month</span></p>' +
				'<p style="color: #8882c8; font-size: 14px; font-weight: 600;">Save \u00A350 on your first month</p>' +
				'</div>';

			document.getElementById('mounjaroPricing').innerHTML =
				'<div style="margin-bottom: 16px;">' +
				'<p style="color: #111827; font-size: 30px; font-weight: 700;">\u00A3249<span style="font-size: 18px; font-weight: 400; color: #6b7280;">/month</span></p>' +
				'<p style="color: #8882c8; font-size: 14px; font-weight: 600;">Save \u00A350 on your first month</p>' +
				'</div>';
		} else {
			const wegovyOptions = Object.keys(wegovyPricing).map(function (dose) {
				const price = wegovyPricing[dose];
				const sel = dose === selectedWegovyDose ? ' selected' : '';
				return '<option value="' + dose + '"' + sel + '>' + dose + ' - \u00A3' + price.toFixed(2) + '/month</option>';
			}).join('');

			document.getElementById('wegovyPricing').innerHTML =
				'<div style="margin-bottom: 16px;">' +
				'<label style="display: block; color: #6b7280; font-size: 12px; font-weight: 500; margin-bottom: 8px;">Select your dose</label>' +
				'<select class="select" id="wegovyDoseSelect" onchange="updateWegovyPrice()" onclick="event.stopPropagation()">' +
				wegovyOptions +
				'</select>' +
				'</div>';

			const mounjaroOptions = Object.keys(mounjaroPricing).map(function (dose) {
				const price = mounjaroPricing[dose];
				const sel = dose === selectedMounjaroDose ? ' selected' : '';
				return '<option value="' + dose + '"' + sel + '>' + dose + ' - \u00A3' + price.toFixed(2) + '/month</option>';
			}).join('');

			document.getElementById('mounjaroPricing').innerHTML =
				'<div style="margin-bottom: 16px;">' +
				'<label style="display: block; color: #6b7280; font-size: 12px; font-weight: 500; margin-bottom: 8px;">Select your dose</label>' +
				'<select class="select" id="mounjaroDoseSelect" onchange="updateMounjaroPrice()" onclick="event.stopPropagation()">' +
				mounjaroOptions +
				'</select>' +
				'</div>';
		}

		updateBadges();
		updateStartTreatmentBtn();
	}

	function updateWegovyPrice() {
		const dose = document.getElementById('wegovyDoseSelect').value;
		selectedWegovyDose = dose;
		updateStartTreatmentBtn();
	}

	function updateMounjaroPrice() {
		const dose = document.getElementById('mounjaroDoseSelect').value;
		selectedMounjaroDose = dose;
		updateStartTreatmentBtn();
	}

	// Stub checkout — merchant will wire the real checkout URL later.
	function proceedToCheckout() {
		const dose = selectedTreatment === 'wegovy' ? selectedWegovyDose : selectedMounjaroDose;
		state.userData.selectedTreatment = selectedTreatment;
		state.userData.selectedDose = dose;
		try {
			localStorage.setItem('eligibility-data', JSON.stringify(state.userData));
		} catch (err) {
			// localStorage may be blocked — ignore.
		}

		const medName = selectedTreatment.charAt(0).toUpperCase() + selectedTreatment.slice(1);
		const addr = state.userData.addressLine1 || '';
		const addr2 = state.userData.addressLine2 ? '\n' + state.userData.addressLine2 : '';
		const city = state.userData.city || '';
		const postcode = state.userData.postcode || '';
		const country = state.userData.country || '';

		alert(
			'Treatment selected: ' + medName + '\n' +
			'Dose: ' + dose + '\n' +
			'Price: \u00A3' + getCurrentPrice() + '/month\n\n' +
			'Shipping to:\n' +
			addr + addr2 + '\n' +
			city + ', ' + postcode + '\n' +
			country + '\n\n' +
			'Checkout URL will be configured by AT Health.'
		);
	}

	window.selectTreatmentCard = selectTreatmentCard;
	window.updateWegovyPrice = updateWegovyPrice;
	window.updateMounjaroPrice = updateMounjaroPrice;
	window.proceedToCheckout = proceedToCheckout;

	// ---------- Expose handlers on window for inline onclick attributes ----------
	// Additional window.* exports are added alongside each handler block above.

	window.goBack = goBack;
	window.reviewAnswers = reviewAnswers;

	// ---------- Initialize ----------

	showScreen(1);
})();
