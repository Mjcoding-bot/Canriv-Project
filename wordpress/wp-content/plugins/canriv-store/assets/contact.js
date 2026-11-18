document.addEventListener('DOMContentLoaded', () => {
    const clock = document.getElementById('currentPhtTime');
    function updateClock() {
        clock.textContent = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit',
        }).format(new Date()) + ' PHT';
    }
    updateClock();
    setInterval(updateClock, 1000);
    for (const button of document.querySelectorAll('[data-chat-state]')) {
        button.addEventListener('click', () => {
            const online = button.dataset.chatState === 'online';
            document.getElementById('panelChatOnline').classList.toggle('hidden', !online);
            document.getElementById('panelChatOffline').classList.toggle('hidden', online);
            for (const toggle of document.querySelectorAll('[data-chat-state]')) {
                toggle.setAttribute('aria-pressed', String(toggle === button));
            }
        });
    }
    document.querySelector('[data-open-chat]').addEventListener('click', () => {
        if (typeof window.Tawk_API?.maximize === 'function') {
            window.Tawk_API.maximize();
        } else {
            document.getElementById('canriv-chat-status').textContent = 'Live chat is awaiting account connection. Please use the inquiry form or call the team.';
        }
    });
    document.querySelector('[data-callback]').addEventListener('click', () => {
        const subject = document.getElementById('subjectField');
        subject.value = 'Request a priority callback';
        subject.scrollIntoView({ block: 'center', behavior: 'smooth' });
        subject.focus({ preventScroll: true });
    });
    document.querySelector('[data-vcard]').addEventListener('click', () => {
        const card = new Blob(['BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Canriv-J Corporation\r\nORG:Canriv-J Corporation\r\nTEL:+639454481254\r\nEMAIL:sales@canriv.com\r\nEND:VCARD\r\n'], { type: 'text/vcard' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(card);
        link.download = 'canriv-contact.vcf';
        link.click();
        setTimeout(() => URL.revokeObjectURL(link.href), 1000);
    });
});
