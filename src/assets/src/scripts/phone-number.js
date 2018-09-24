import './polyfills/Element.closest';
import './polyfills/Element.classList';

export default function PhoneNumber(namespace) {
	let isOpen = false;
	let selectedIndex = 0;
	let highlightedIndex = 0;

	const options = [];

	const elems = {
		activeFlag: getElement('af'),
		button: getElement('button'),
		dropdown: getElement('dropdown'),
		noResults: getElement('no-results'),
		number: getElement('number'),
		region: getElement('region'),
		regions: getElement('regions'),
		search: getElement('search'),
		field: getElement(),
	};

	init();

	function init() {
		selectedIndex = elems.region.selectedIndex;
		highlightedIndex = elems.region.selectedIndex;

		elems.button.addEventListener('click', e => {
			toggle();
		});

		elems.button.addEventListener('keydown', event => {
			if (event.key == 'ArrowDown') open();
		});

		document.addEventListener('click', e => {
			if (isOpen && !e.target.closest(`#${elems.button.id}, #${elems.dropdown.id}`)) close();
		}, true);

		document.addEventListener('focus', e => {
			if (isOpen && !e.target.closest(`#${elems.button.id}, #${elems.dropdown.id}`)) close();
		}, true);

		elems.dropdown.setAttribute('tabindex', -1);
		elems.dropdown.addEventListener('click', handleClick, true);

		elems.search.addEventListener('input', handleSearch);

		Array.from(elems.dropdown.querySelectorAll('li')).forEach(elem => {
			options.push({ elem: elem, enabled: true });
			elem.addEventListener('mousemove', handleMouseMove);
		});

		elems.dropdown.setAttribute('role', 'combobox');
		elems.dropdown.setAttribute('aria-expanded', true);
		elems.dropdown.setAttribute('aria-haspopup', 'listbox');
		elems.dropdown.setAttribute('aria-owns', elems.regions.id);

		elems.search.setAttribute('aria-autocomplete', 'both');
		elems.search.setAttribute('aria-controls', elems.regions.id);

		elems.regions.setAttribute('role', 'listbox');

		select(selectedIndex);
	}

	function getElement(elem) {
		return document.getElementById(`${namespace}${ elem ? `-${elem}` : ''}`);
	}

	function handleSearch(event) {
		const q = event.target.value.toLowerCase();
		const results = [];

		options.forEach((option, i) => {
			const string = option.elem.textContent.toLowerCase();

			if (string.indexOf(q) == -1) {
				options[i].elem.style.display = 'none';
				options[i].enabled = false;
			} else {
				options[i].elem.removeAttribute('style');
				options[i].enabled = true;
				results.push(option);
			}
		});

		const i = options.findIndex(element => element.enabled === true);

		elems.noResults.classList.toggle('is-visible', !results.length);

		highlight(i);
	}

	function enableAllOptions() {
		options.forEach(option => {
			option.enabled = true;
			option.elem.removeAttribute('style');
		});
	}

	function handleKeydown(event) {
		switch (event.key) {
			case 'ArrowDown':
				highlightNextOption();
				break;
			case 'ArrowUp':
				highlightPreviousOption();
				break;
			case 'Home':
				select(0);
				break;
			case 'End':
				select(options.length - 1);
				break;
			case 'Enter':
				select(highlightedIndex);
				close();
				break;
			case 'Escape':
				close();
				break;
			default:
				return;
		}

		event.preventDefault();
	}

	function handleClick(event) {
		const elem = event.target.closest('li');
		const index = options.findIndex(item => item.elem == elem);

		if (index == -1) return;

		select(index);
		close();
	}

	function handleMouseMove() {
		const elem = event.target.closest('li');
		const index = options.findIndex(item => item.elem == elem);

		if (index == -1) return;

		highlight(index);
	}

	function clamp(num, min, max) {
		return num <= min ? min : num >= max ? max : num;
	}

	function optionClamp(index) {
		return clamp(index, 0, options.length - 1);
	}

	function highlight(index) {
		let i = optionClamp(index);

		elems.search.setAttribute('aria-activedescendant', options[i].id);

		options[highlightedIndex].elem.classList.remove('is-selected');
		options[highlightedIndex].elem.removeAttribute('aria-selected');

		options[i].elem.classList.add('is-selected');
		options[i].elem.setAttribute('aria-selected', true);

		scrollToOption(i);

		highlightedIndex = i;
	}

	function highlightNextOption() {
		function findNext(element, index) {
			return index > highlightedIndex && element.enabled == true;
		}

		const i = options.findIndex(findNext);

		if (i > -1) {
			highlight(i);
		}
	}

	function highlightPreviousOption() {
		function findPrevious() {
			let i;

			options.forEach((option, index) => {
				if (index < highlightedIndex && option.enabled == true) i = index;
			});

			return i;
		}

		const i = findPrevious();

		if (i != undefined) {
			highlight(i);
		}
	}

	function select(index) {
		options[selectedIndex].elem.classList.remove('is-selected');

		selectedIndex = index;

		options[selectedIndex].elem.classList.add('is-selected');
		elems.region.selectedIndex = selectedIndex;
		elems.activeFlag.className = ''
		elems.activeFlag.classList.add('icon', `icon-${options[selectedIndex].elem.dataset.region}`);

		scrollToOption(selectedIndex);
		highlight(selectedIndex);
	}

	function scrollToOption(index, pin = false) {
		const element = options[index].elem;

		if (elems.regions.scrollHeight > elems.regions.clientHeight) {
			var scrollBottom = elems.regions.clientHeight + elems.regions.scrollTop;
			var elementBottom = element.offsetTop + element.offsetHeight;

			if (elementBottom > scrollBottom) {
			  elems.regions.scrollTop = elementBottom - elems.regions.clientHeight;
			} else if (element.offsetTop < elems.regions.scrollTop) {
			  elems.regions.scrollTop = element.offsetTop;
			}
		}
	}

	function open() {
		elems.dropdown.classList.add('is-open');

		const rect = elems.dropdown.getBoundingClientRect();
		const displayAbove = rect.bottom + 20 > (window.innerHeight);

		elems.dropdown.classList.toggle('above', displayAbove);
		elems.button.setAttribute('aria-expanded', true);
		elems.button.classList.add('active');
		elems.search.focus();

		highlight(selectedIndex);
		elems.noResults.classList.remove('is-visible');

		document.addEventListener('keydown', handleKeydown, true);

		isOpen = true;
	}

	function close() {
		elems.dropdown.classList.remove('is-open');
		elems.dropdown.classList.remove('above');
		elems.button.removeAttribute('aria-expanded');
		elems.button.classList.remove('active');
		elems.search.value = '';

		document.removeEventListener('keydown', handleKeydown, true);

		enableAllOptions();
		isOpen = false;
	}

	function toggle() {
		return !isOpen ? open() : close();
	}
}
