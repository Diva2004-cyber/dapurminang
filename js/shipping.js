document.addEventListener('DOMContentLoaded', function() {
    const calculateShippingBtn = document.getElementById('calculate_shipping');
    const shippingResult = document.getElementById('shipping_result');
    const shippingDistance = document.getElementById('shipping_distance');
    const shippingBaseCost = document.getElementById('shipping_base_cost');
    const shippingCostPerKm = document.getElementById('shipping_cost_per_km');
    const shippingSurcharges = document.getElementById('shipping_surcharges');
    const shippingTotalCost = document.getElementById('shipping_total_cost');
    const shippingCostInput = document.querySelector('input[name="shipping_cost"]');
    const deliveryAddress = document.getElementById('delivery_address');

    // Initialize shipping cost from hidden input
    let currentShippingCost = parseFloat(shippingCostInput.value) || 0;
    updateOrderSummary();

    calculateShippingBtn.addEventListener('click', function() {
        const isDifficultArea = document.getElementById('is_difficult_area').checked;
        const isHeavyLoad = document.getElementById('is_heavy_load').checked;
        
        if (!deliveryAddress.value) {
            alert('Mohon masukkan alamat pengiriman');
            return;
        }
        
        // Show loading state
        calculateShippingBtn.disabled = true;
        calculateShippingBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghitung...';
        
        // In a real implementation, you would use a geocoding service here
        // For this example, we'll use dummy coordinates
        const dummyCoordinates = {
            latitude: -7.3275 + (Math.random() * 0.1 - 0.05),
            longitude: 108.2207 + (Math.random() * 0.1 - 0.05)
        };
        
        fetch('ajax_files/calculate_shipping.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `latitude=${dummyCoordinates.latitude}&longitude=${dummyCoordinates.longitude}&is_difficult_area=${isDifficultArea}&is_heavy_load=${isHeavyLoad}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            // Update UI with shipping details
            shippingResult.classList.remove('d-none');
            shippingDistance.textContent = data.distance;
            shippingBaseCost.textContent = data.base_cost.toLocaleString();
            shippingCostPerKm.textContent = data.cost_per_km.toLocaleString();
            shippingTotalCost.textContent = data.total_cost.toLocaleString();
            
            // Update surcharges
            shippingSurcharges.innerHTML = '';
            if (data.surcharges && data.surcharges.length > 0) {
                shippingSurcharges.innerHTML = '<p>Biaya Tambahan:</p>';
                data.surcharges.forEach(surcharge => {
                    shippingSurcharges.innerHTML += `<p>${surcharge.type}: Rp ${surcharge.amount.toLocaleString()}</p>`;
                });
            }
            
            // Update the hidden shipping cost input
            currentShippingCost = data.total_cost;
            shippingCostInput.value = currentShippingCost;
            
            // Update order summary
            updateOrderSummary();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghitung ongkos kirim');
        })
        .finally(() => {
            // Reset button state
            calculateShippingBtn.disabled = false;
            calculateShippingBtn.textContent = 'Hitung Ongkir';
        });
    });

    function updateOrderSummary() {
        const cartItems = document.querySelectorAll('.cart-item');
        let subtotal = 0;
        
        cartItems.forEach(item => {
            const price = parseFloat(item.querySelector('.item-price').textContent.replace('Rp ', '').replace(',', ''));
            const quantity = parseInt(item.querySelector('.item-quantity').value);
            subtotal += price * quantity;
        });
        
        const total = subtotal + currentShippingCost;
        
        document.querySelector('.subtotal-amount').textContent = `Rp ${subtotal.toLocaleString()}`;
        document.querySelector('.shipping-amount').textContent = `Rp ${currentShippingCost.toLocaleString()}`;
        document.querySelector('.total-amount').textContent = `Rp ${total.toLocaleString()}`;
    }

    // Update order summary when cart items change
    document.querySelectorAll('.item-quantity').forEach(input => {
        input.addEventListener('change', updateOrderSummary);
    });
}); 