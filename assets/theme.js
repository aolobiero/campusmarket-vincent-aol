(function () {
	'use strict';

	const storageKey = 'campusmarket-theme';
	const root = document.documentElement;

	function setTheme(theme, persist) {
		const nextTheme = theme === 'light' ? 'light' : 'dark';
		root.classList.toggle('light-theme', nextTheme === 'light');
		root.classList.toggle('dark-theme', nextTheme === 'dark');

		if (document.body) {
			document.body.classList.toggle('light-theme', nextTheme === 'light');
			document.body.classList.toggle('dark-theme', nextTheme === 'dark');
		}

		if (persist) {
			try {
				localStorage.setItem(storageKey, nextTheme);
			} catch (error) {
			}
		}

		const button = document.querySelector('.theme-toggle');
		if (button) {
			button.setAttribute('aria-label', 'Switch to ' + (nextTheme === 'dark' ? 'light' : 'dark') + ' theme');
			button.setAttribute('title', 'Switch to ' + (nextTheme === 'dark' ? 'light' : 'dark') + ' theme');
			button.setAttribute('aria-pressed', String(nextTheme === 'dark'));
			button.innerHTML = nextTheme === 'dark'
				? '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path></svg>'
				: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"></path></svg>';
		}
	}

	let initialTheme = 'dark';
	try {
		initialTheme = localStorage.getItem(storageKey) === 'light' ? 'light' : 'dark';
	} catch (error) {
		initialTheme = root.classList.contains('light-theme') ? 'light' : 'dark';
	}
	setTheme(initialTheme, false);

	function addToggle() {
		if (document.querySelector('.theme-toggle')) {
			setTheme(initialTheme, false);
			return;
		}

		const button = document.createElement('button');
		button.className = 'theme-toggle';
		button.type = 'button';
		button.addEventListener('click', function () {
			initialTheme = root.classList.contains('dark-theme') ? 'light' : 'dark';
			setTheme(initialTheme, true);
		});

		const target = document.querySelector('.nav-actions') || document.querySelector('.nav') || document.querySelector('.shell') || document.body;
		if (target.classList.contains('shell')) {
			button.classList.add('theme-toggle-floating');
		}
		target.appendChild(button);
		setTheme(initialTheme, false);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', addToggle, { once: true });
	} else {
		addToggle();
	}
})();