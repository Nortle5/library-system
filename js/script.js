function initRegisterValidation() {
    const form = document.querySelector('form[action="register.php"]');
    if (!form) return; // not the registration page, so skip

    const namePattern = /^\p{L}[\p{L} .'-]*$/u;
    const nameRule = (v) => (namePattern.test(v) && v.length <= 50 ? '' : 'Letters only, up to 50 characters.');

    const rules = {
        firstName: nameRule,
        lastName: nameRule,
        studentId: (v) => (/^[A-Za-z0-9-]{4,20}$/.test(v) ? '' : '4 to 20 letters, numbers, or dashes.'),
        age: (v) => (/^\d+$/.test(v) && v >= 15 && v <= 100 ? '' : 'Whole number from 15 to 100.'),
        yearLevel: (v) => (v ? '' : 'Choose a year level.'),
        course: (v) => (v ? '' : 'Choose a course.'),
        username: (v) => (/^[A-Za-z0-9_]{4,20}$/.test(v) ? '' : '4 to 20 letters, numbers, or underscores.'),
        password: (v) => (v.length >= 8 ? '' : 'At least 8 characters.'),
        confirm: (v) => (v === form.elements.password.value ? '' : 'Passwords do not match.'),
    };

    function showError(field, message) {
        let note = field.parentElement.querySelector('[data-error]');
        if (!note) {
            note = document.createElement('p');
            note.dataset.error = '';
            note.className = 'mt-1 text-xs text-red-600';
            field.parentElement.appendChild(note);
        }
        note.textContent = message;
        field.classList.toggle('border-red-500', message !== '');
    }

    function check(name) {
        const field = form.elements[name];
        const value = name === 'password' || name === 'confirm' ? field.value : field.value.trim();
        const message = rules[name](value);
        showError(field, message);
        return message === '';
    }

    Object.keys(rules).forEach((name) => {
        const field = form.elements[name];
        field.addEventListener('blur', () => check(name));
        field.addEventListener('input', () => {
            if (field.classList.contains('border-red-500')) check(name);
            if (name === 'password' && form.elements.confirm.value !== '') check('confirm');
        });
    });

    form.addEventListener('submit', (event) => {
        let firstBad = null;
        Object.keys(rules).forEach((name) => {
            if (!check(name) && !firstBad) firstBad = form.elements[name];
        });
        if (firstBad) {
            event.preventDefault();
            firstBad.focus();
        }
    });
}

function initLiveSearch() {
    const input = document.getElementById('search-input');
    const results = document.getElementById('book-results');
    if (!input || !results) return; // not the books page, so skip

    let timer = null;
    let latest = 0;

    input.addEventListener('input', () => {
        clearTimeout(timer);

        timer = setTimeout(async () => {
            const thisRequest = ++latest;

            try {
                const response = await fetch('search-books.php?q=' + encodeURIComponent(input.value.trim()));

                if (response.redirected) {
                    window.location.href = response.url; // session ended, so go to login
                    return;
                }
                if (!response.ok) throw new Error('Request failed');

                const html = await response.text();
                if (thisRequest === latest) results.innerHTML = html;
            } catch (error) {
                if (thisRequest === latest) {
                    results.innerHTML = '<p class="rounded-lg bg-white p-6 text-sm text-red-600 shadow">Search failed. Please try again.</p>';
                }
            }
        }, 250);
    });
}

function initMobileMenu() {
    const button = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (!button || !menu) return; 

    button.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('hidden') === false;
        button.setAttribute('aria-expanded', String(isOpen));
    });
}

function initBackToTop() {
    const button = document.getElementById('back-to-top');
    if (!button) return; 

    const update = () => {
        const show = window.scrollY > 300;
        button.classList.toggle('hidden', !show);
        button.classList.toggle('flex', show);
    };

    window.addEventListener('scroll', update, { passive: true });
    button.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    update();
}

function initTableSearch() {
    const input = document.getElementById('table-search');
    const table = document.getElementById('books-table');
    if (!input || !table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr[data-row]'));
    const emptyRow = document.getElementById('no-match');

    input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach((row) => {
            const match = row.dataset.search.includes(term);
            row.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        if (emptyRow) emptyRow.classList.toggle('hidden', visible !== 0);
    });
}
function initBorrowingTabs() {
    const tabs = document.querySelectorAll('[data-filter]');
    const rows = document.querySelectorAll('tr[data-status]');
    const emptyRow = document.getElementById('no-records');
    if (!tabs.length) return; // not the borrowings page, so skip

    const active = ['bg-orange-500', 'text-white'];
    const idle = ['bg-gray-100', 'text-gray-700', 'hover:bg-gray-200'];

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const filter = tab.dataset.filter;
            let visible = 0;

            tabs.forEach((t) => {
                const on = t === tab;
                t.classList.remove(...active, ...idle);
                t.classList.add(...(on ? active : idle));
            });

            rows.forEach((row) => {
                const show = filter === 'all' || row.dataset.status === filter;
                row.classList.toggle('hidden', !show);
                if (show) visible++;
            });

            if (emptyRow) emptyRow.classList.toggle('hidden', visible !== 0);
        });
    });
}
initRegisterValidation();
initLiveSearch();
initMobileMenu();
initBackToTop();
initTableSearch();
initBorrowingTabs();