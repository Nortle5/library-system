function initRegisterValidation() {
    const form = document.querySelector('form[action$="register.php"]');
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
                const response = await fetch(input.dataset.endpoint + '?q=' + encodeURIComponent(input.value.trim()));

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

const TOAST_ICONS = {
    success: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
};
const CLOSE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';

function showToast(message, type = 'success') {
    const root = document.getElementById('toast-root');
    if (!root) return;

    const isError = type === 'error';
    const toast = document.createElement('div');
    toast.setAttribute('role', isError ? 'alert' : 'status');
    toast.className = 'flex translate-x-4 items-start gap-3 rounded-lg border-l-4 bg-white p-4 opacity-0 shadow-lg ring-1 ring-black/5 transition duration-300 '
        + (isError ? 'border-red-500' : 'border-green-500');

    const icon = document.createElement('span');
    icon.className = 'mt-0.5 shrink-0 ' + (isError ? 'text-red-500' : 'text-green-500');
    icon.innerHTML = TOAST_ICONS[isError ? 'error' : 'success'];

    const text = document.createElement('p');
    text.className = 'flex-1 text-sm text-gray-800';
    text.textContent = message; 

    const close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', 'Dismiss');
    close.className = 'shrink-0 text-gray-400 hover:text-gray-600';
    close.innerHTML = CLOSE_ICON;

    toast.append(icon, text, close);
    root.appendChild(toast);
    requestAnimationFrame(() => toast.classList.remove('translate-x-4', 'opacity-0'));

    const dismiss = () => {
        clearTimeout(timer);
        toast.classList.add('translate-x-4', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    };
    const timer = setTimeout(dismiss, 4500);
    close.addEventListener('click', dismiss);
}

function initToasts() {
    const data = document.getElementById('toast-data');
    if (!data) return;

    let items = [];
    try {
        items = JSON.parse(data.textContent);
    } catch (error) {
        return;
    }
    items.forEach((item, i) => setTimeout(() => showToast(item.message, item.type), i * 150));

    const url = new URL(window.location.href);
    ['result', 'added'].forEach((key) => url.searchParams.delete(key));
    window.history.replaceState(null, '', url);
}

function initBookDialog() {
    const dialog = document.getElementById('book-dialog');
    if (!dialog) return;

    const form = dialog.querySelector('form');
    const heading = document.getElementById('book-dialog-title');
    const errorBox = document.getElementById('book-errors');
    const fields = {
        id: document.getElementById('book-id'),
        title: document.getElementById('book-title'),
        author: document.getElementById('book-author'),
        genre: document.getElementById('book-genre'),
        copies: document.getElementById('book-copies'),
    };

    function showErrors(errors) {
        const items = errors.map((message) => {
            const li = document.createElement('li');
            li.textContent = message;
            return li;
        });
        errorBox.querySelector('ul').replaceChildren(...items);
        errorBox.classList.toggle('hidden', errors.length === 0);
    }

    function openDialog(book, errors = []) {
        const editing = book !== null && book.id !== '';
        heading.textContent = editing ? 'Edit book' : 'Add book';
        fields.id.value = book ? book.id : '';
        fields.title.value = book ? book.title : '';
        fields.author.value = book ? book.author : '';
        fields.genre.value = book ? book.genre : '';
        fields.copies.value = book ? book.copies : 1;
        showErrors(errors);
        dialog.showModal();
        fields.title.focus();
    }

    document.getElementById('add-book-btn').addEventListener('click', () => openDialog(null));
    document.getElementById('book-cancel').addEventListener('click', () => dialog.close());

    document.querySelectorAll('.edit-book-btn').forEach((btn) => {
        btn.addEventListener('click', () => openDialog({
            id: btn.dataset.id,
            title: btn.dataset.title,
            author: btn.dataset.author,
            genre: btn.dataset.genre,
            copies: btn.dataset.copies,
        }));
    });

    form.addEventListener('submit', (event) => {
        const errors = [];
        const title = fields.title.value.trim();
        const author = fields.author.value.trim();
        const genre = fields.genre.value.trim();
        const copies = fields.copies.value.trim();

        if (title === '' || title.length > 150) errors.push('Title is required (up to 150 characters).');
        if (author === '' || author.length > 100) errors.push('Author is required (up to 100 characters).');
        if (genre === '' || genre.length > 50) errors.push('Genre is required (up to 50 characters).');
        if (!/^\d+$/.test(copies) || Number(copies) < 1 || Number(copies) > 1000) {
            errors.push('Total copies must be a whole number from 1 to 1000.');
        }

        if (errors.length > 0) {
            event.preventDefault();
            showErrors(errors);
        }
    });

    const state = document.getElementById('dialog-state');
    if (state) {
        try {
            const saved = JSON.parse(state.textContent);
            openDialog(saved, saved.errors);
        } catch (error) {

        }
    }
}
initRegisterValidation();
initLiveSearch();
initMobileMenu();
initBackToTop();
initTableSearch();
initBorrowingTabs();
initToasts();
initBookDialog();