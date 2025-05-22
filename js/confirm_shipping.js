document.addEventListener('DOMContentLoaded', function() {
    console.log('Confirm shipping JS loaded');
    
    // Auto-refresh functionality for waiting page
    if (document.querySelector('.shipping-status .badge-warning')) {
        setTimeout(function() {
            window.location.reload();
        }, 60000); // Refresh every 60 seconds
    }
    
    // Confirm order button functionality
    const confirmOrderBtn = document.querySelector('button[name="confirm_order"]');
    if (confirmOrderBtn) {
        console.log('Confirm order button found');
        confirmOrderBtn.addEventListener('click', function() {
            try {
                console.log('Confirm order button clicked');
                // Show loading state
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';
                this.disabled = true;
                
                // Submit the form explicitly to avoid any issues
                this.form.submit();
            } catch (e) {
                console.error('Error in confirm order handler:', e);
                // Re-enable the button if there's an error
                this.innerHTML = '<i class="fas fa-check"></i> Lanjutkan Pesanan';
                this.disabled = false;
                alert('Terjadi kesalahan: ' + e.message);
            }
        });
    } else {
        console.log('Confirm order button not found');
    }
    
    // Cancel order button functionality
    const cancelOrderBtn = document.querySelector('button[name="cancel_order"]');
    if (cancelOrderBtn) {
        console.log('Cancel order button found');
        cancelOrderBtn.addEventListener('click', function(e) {
            try {
                console.log('Cancel order button clicked');
                if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                    console.log('Cancel confirmation rejected by user');
                    e.preventDefault();
                    return false;
                }
                
                console.log('Cancel confirmation accepted, processing...');
                // Show loading state
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Membatalkan...';
                this.disabled = true;
                
                // Add a timeout to allow UI to update before form submission
                setTimeout(() => {
                    try {
                        console.log('Submitting cancel form...');
                        this.form.submit();
                    } catch (submitError) {
                        console.error('Error submitting form:', submitError);
                    }
                }, 100);
            } catch (e) {
                console.error('Error in cancel order handler:', e);
                // Re-enable the button if there's an error
                this.innerHTML = '<i class="fas fa-times"></i> Batalkan Pesanan';
                this.disabled = false;
                alert('Terjadi kesalahan: ' + e.message);
            }
        });
    } else {
        console.log('Cancel order button not found');
    }
}); 