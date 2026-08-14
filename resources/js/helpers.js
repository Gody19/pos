import Swal from 'sweetalert2';

export const formatMoney = (value) => {
    return new Intl.NumberFormat('en-TZ', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};

window.formatMoney = formatMoney;

export const showToast = (message, type = 'success') => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};

window.showToast = showToast;

export const confirmAction = (message = 'Are you sure?') => {
    return Swal.fire({
        title: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
    }).then((result) => result.isConfirmed);
};

window.confirmAction = confirmAction;

export const printReceipt = (html) => {
    const frame = window.open('', '_blank', 'width=400,height=600');
    if (!frame) {
        showToast('Please allow pop-ups to print receipts.', 'warning');
        return;
    }
    frame.document.write(html);
    frame.document.close();
    frame.focus();
    frame.print();
};

window.printReceipt = printReceipt;
