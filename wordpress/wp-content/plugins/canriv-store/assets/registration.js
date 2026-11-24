document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('registration-result')?.focus();

    // Keep the address selectors in sync with the Philippine Standard
    // Geographic Code hierarchy: province/region → city/municipality → barangay.
    const province = document.querySelector('#canriv-registration-form select[name="province"]');
    const city = document.getElementById('registration-city');
    const barangay = document.getElementById('registration-barangay');
    if (province && city && barangay) {
        const apis = ['https://psgc.cloud/api/v2', 'https://psgc.gitlab.io/api'];
        const cache = new Map();
        const normalize = (value) => String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const load = async (path) => {
            if (!cache.has(path)) {
                cache.set(path, (async () => {
                    let lastError;
                    for (const api of apis) {
                        try {
                            const response = await fetch(`${api}${path}/`, { headers: { Accept: 'application/json' } });
                            if (!response.ok) throw new Error(`Location list unavailable (${response.status})`);
                            const items = await response.json();
                            if (!Array.isArray(items)) throw new Error('Invalid location list');
                            return items;
                        } catch (error) {
                            lastError = error;
                        }
                    }
                    cache.delete(path);
                    throw lastError || new Error('Location list unavailable');
                })());
            }
            return cache.get(path);
        };
        const setOptions = (select, prompt, items, disabled = false) => {
            select.replaceChildren(new Option(prompt, ''));
            items.forEach((item) => {
                const option = new Option(item.name, item.name);
                if (item.code) option.dataset.code = item.code;
                select.add(option);
            });
            select.disabled = disabled;
        };
        setOptions(city, 'Select a city or municipality', [], true);
        setOptions(barangay, 'Select a barangay or district', [], true);

        province.addEventListener('change', async () => {
            const selectedProvince = province.value;
            setOptions(city, 'Loading cities and municipalities…', [], true);
            setOptions(barangay, 'Select a barangay or district', [], true);
            if (!selectedProvince) {
                setOptions(city, 'Select a city or municipality', [], true);
                return;
            }
            try {
                const [provinces, regions] = await Promise.all([load('/provinces'), load('/regions')]);
                const selectedName = normalize(province.options[province.selectedIndex].text);
                let path = '';
                if (selectedName === 'metromanila') {
                    const region = regions.find((item) => normalize(item.name).includes('nationalcapitalregion'));
                    if (region) path = `/regions/${encodeURIComponent(region.code)}/cities-municipalities`;
                } else {
                    const match = provinces.find((item) => normalize(item.name) === selectedName);
                    if (match) path = `/provinces/${encodeURIComponent(match.code)}/cities-municipalities`;
                }
                if (!path) throw new Error('No matching province found');
                const localities = await load(path);
                if (province.value === selectedProvince) {
                    setOptions(city, 'Select a city or municipality', localities.sort((a, b) => a.name.localeCompare(b.name)));
                }
            } catch (error) {
                if (province.value === selectedProvince) {
                    setOptions(city, 'Location list could not load. Please try again.', [], true);
                    console.error('Canriv address locations:', error);
                }
            }
        });

        city.addEventListener('change', async () => {
            const selectedCity = city.value;
            setOptions(barangay, 'Loading barangays…', [], true);
            if (!selectedCity) {
                setOptions(barangay, 'Select a barangay or district', [], true);
                return;
            }
            try {
                const code = city.selectedOptions[0].dataset.code || selectedCity;
                const localities = await load(`/cities-municipalities/${encodeURIComponent(code)}/barangays`);
                if (city.value === selectedCity) {
                    setOptions(barangay, 'Select a barangay or district', localities.sort((a, b) => a.name.localeCompare(b.name)));
                }
            } catch (error) {
                if (city.value === selectedCity) {
                    setOptions(barangay, 'Barangay list could not load. Please try again.', [], true);
                    console.error('Canriv address barangays:', error);
                }
            }
        });
    }

    const phone = document.getElementById('mobile');
    if (phone) {
        phone.type = 'text';
        phone.required = true;
        phone.maxLength = 11;
        phone.minLength = 11;
        phone.inputMode = 'numeric';
        phone.pattern = '09[0-9]{9}';
        phone.autocomplete = 'tel-national';
        const validatePhone = () => {
            const number = phone.value.replace(/\D/g, '').slice(0, 11);
            if (phone.value !== number) phone.value = number;
            phone.setCustomValidity(number && !/^09\d{9}$/.test(number)
                ? 'Enter exactly 11 digits starting with 09, such as 09171234567.'
                : '');
        };
        phone.addEventListener('input', validatePhone);
        phone.addEventListener('blur', validatePhone);
        phone.form.addEventListener('submit', (event) => {
            validatePhone();
            if (!phone.reportValidity()) event.preventDefault();
        });
    }

    const password = document.getElementById('password');
    const confirm = document.getElementById('confirmPassword');
    const meter = document.getElementById('pwd-meter-bar');
    const status = document.getElementById('pwd-strength-text');
    const showPassword = document.getElementById('canriv-show-registration-password');
    if (!password || !confirm || !meter || !status || !showPassword) return;

    showPassword.addEventListener('change', () => {
        password.type = confirm.type = showPassword.checked ? 'text' : 'password';
    });

    function update() {
        confirm.setCustomValidity(confirm.value === password.value ? '' : 'Passwords must match.');
        const length = Array.from(password.value).length;
        meter.style.width = Math.min(length / password.minLength, 1) * 100 + '%';
        status.textContent = length >= password.minLength ? 'Minimum length met' : 'Use at least ' + password.minLength + ' characters';
    }
    password.addEventListener('input', update);
    confirm.addEventListener('input', update);
    update();
});
