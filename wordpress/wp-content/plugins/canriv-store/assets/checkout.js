document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.canriv-purchase-checkout form.checkout');
    if (!form) return;

    const focusFirstMissingField = () => {
        if (!document.querySelector('.woocommerce-error, .woocommerce-NoticeGroup-checkout')) return;

        const invalidRows = form.querySelectorAll('.woocommerce-invalid-required-field, .woocommerce-invalid');
        const field = Array.from(invalidRows)
            .flatMap((row) => Array.from(row.querySelectorAll('input, select, textarea')))
            .find((control) => control.getClientRects().length && !control.disabled && control.type !== 'hidden');
        if (!field) return;

        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(() => field.focus({ preventScroll: true }), 250);
    };

    // WooCommerce can show validation errors after a full page response or an
    // AJAX checkout attempt. In both cases, take the customer to the field to fix.
    window.setTimeout(focusFirstMissingField, 100);
    if (window.jQuery) {
        window.jQuery(document.body).on('checkout_error updated_checkout', () => {
            window.setTimeout(focusFirstMissingField, 100);
        });
    }
});
