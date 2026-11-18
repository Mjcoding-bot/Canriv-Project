document.addEventListener('DOMContentLoaded', () => {
    const jobs = Array.from(document.querySelectorAll('[data-job-role]'));
    const search = document.getElementById('career-search');
    const hub = document.getElementById('career-hub');
    const buttons = Array.from(document.querySelectorAll('[data-job-filter]'));
    let category = 'all';
    function filterJobs() {
        const term = search.value.trim().toLowerCase();
        for (const job of jobs) {
            job.hidden = !(job.textContent.toLowerCase().includes(term) &&
                (category === 'all' || job.dataset.jobCategory === category) &&
                (hub.value === 'all' || job.dataset.jobHub.split(' ').includes(hub.value)));
        }
        const count = jobs.filter((job) => !job.hidden).length;
        document.getElementById('career-count').textContent = `${count} OF ${jobs.length} ROLES SHOWN`;
        document.getElementById('career-empty').hidden = count > 0;
    }
    search.addEventListener('input', filterJobs);
    hub.addEventListener('change', filterJobs);
    for (const button of buttons) {
        const group = button.dataset.jobFilter;
        const count = jobs.filter((job) => group === 'all' || job.dataset.jobCategory === group).length;
        button.textContent = button.textContent.replace(/\(\d+\)/, `(${count})`);
        button.addEventListener('click', () => {
            category = group;
            for (const toggle of buttons) {
                toggle.setAttribute('aria-pressed', String(toggle === button));
                toggle.classList.toggle('bg-primary', toggle === button);
            }
            filterJobs();
        });
    }
    for (const link of document.querySelectorAll('[data-apply-role]')) {
        link.addEventListener('click', () => {
            document.getElementById('career-position').value = link.dataset.applyRole;
        });
    }
    const input = document.getElementById('career-resume');
    const drop = document.getElementById('career-upload');
    const label = document.getElementById('career-file-name');
    function showFile() {
        const file = input.files[0];
        const valid = !file || (/\.(pdf|docx|jpe?g)$/i.test(file.name) && file.size <= Number(input.dataset.limit));
        const error = `Choose PDF, DOCX or JPG, up to ${Math.floor(Number(input.dataset.limit) / 1024 / 1024)} MB.`;
        input.setCustomValidity(valid ? '' : error);
        label.textContent = valid ? (file?.name ?? '') : error;
    }
    input.addEventListener('change', showFile);
    drop.addEventListener('click', (event) => { if (event.target !== input) input.click(); });
    drop.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
    });
    drop.addEventListener('dragover', (event) => event.preventDefault());
    drop.addEventListener('drop', (event) => {
        event.preventDefault();
        input.files = event.dataTransfer.files;
        showFile();
    });
    filterJobs();
});
