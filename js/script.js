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
initRegisterValidation();
initLiveSearch();